<?php

namespace App\Modules\Ai\Gateway;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Providers\TextProvider;
use Laravel\Ai\Gateway\OpenAi\OpenAiGateway;
use Laravel\Ai\Gateway\TextGenerationOptions;
use Laravel\Ai\Providers\Provider;
use Laravel\Ai\Responses\TextResponse;

/**
 * Subclass of laravel/ai's OpenAiGateway that propagates the agent's
 * #[Timeout(N)] attribute through every HTTP call in a generateText cycle —
 * including the tool-call follow-up requests that the upstream library forgets
 * to forward the timeout to.
 *
 * --------------------------------------------------------------------------
 * Upstream bug
 * --------------------------------------------------------------------------
 * The trait `Laravel\Ai\Gateway\OpenAi\Concerns\ParsesTextResponses::continueWithToolResults`
 * (line ~233 as of laravel/ai v0.4.x) submits tool results back to OpenAI via:
 *
 *     $this->client($provider)->post('responses', $body)
 *
 * Note the missing `$timeout` second argument. Because
 * `Laravel\Ai\Gateway\OpenAi\Concerns\CreatesOpenAiClient::client()` falls back
 * to a hardcoded 60-second default when timeout is null, every follow-up turn
 * in a tool-using flow gets a 60s deadline — regardless of the agent's
 * `#[Timeout(300)]` attribute. The same problem exists in the streaming path
 * (`HandlesTextStreaming::executeStreamingToolCalls`).
 *
 * For Vyzor this surfaced as `cURL error 28: Operation timed out after
 * 60002 milliseconds` on Google Analytics reports, where the AI almost always
 * drills in via `GoogleAnalyticsTool` after the initial prompt and OpenAI
 * frequently needs >60s to compose the final analysis around the tool result.
 * Clarity reports rarely trigger tool calls, so the bug never manifested there.
 *
 * --------------------------------------------------------------------------
 * Fix
 * --------------------------------------------------------------------------
 * Capture the requested timeout once at the entry points (`generateText`,
 * `streamText`) into `$this->currentTimeout`, and override the trait's
 * `client()` so it falls back to that value when no explicit timeout is
 * passed (which is exactly the case in `continueWithToolResults` and the
 * streaming tool-loop). The captured value is reset in a `finally` block
 * so it never leaks between calls.
 *
 * When the upstream library is fixed (PR welcome), this class can be removed
 * and the `extend('openai', …)` call in AiServiceProvider with it.
 */
class TimeoutAwareOpenAiGateway extends OpenAiGateway
{
    /**
     * Timeout (seconds) captured at the start of the current generateText /
     * streamText invocation. Used as the fallback by the overridden client()
     * for any HTTP call inside the same invocation cycle that doesn't pass
     * a timeout explicitly.
     */
    private ?int $currentTimeout = null;

    public function generateText(
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages = [],
        array $tools = [],
        ?array $schema = null,
        ?TextGenerationOptions $options = null,
        ?int $timeout = null,
    ): TextResponse {
        $previous = $this->currentTimeout;
        $this->currentTimeout = $timeout;

        try {
            return parent::generateText($provider, $model, $instructions, $messages, $tools, $schema, $options, $timeout);
        } finally {
            // Restore — supports the (rare) nested case without losing the outer timeout.
            $this->currentTimeout = $previous;
        }
    }

    public function streamText(
        string $invocationId,
        TextProvider $provider,
        string $model,
        ?string $instructions,
        array $messages = [],
        array $tools = [],
        ?array $schema = null,
        ?TextGenerationOptions $options = null,
        ?int $timeout = null,
    ): Generator {
        $previous = $this->currentTimeout;
        $this->currentTimeout = $timeout;

        try {
            yield from parent::streamText($invocationId, $provider, $model, $instructions, $messages, $tools, $schema, $options, $timeout);
        } finally {
            $this->currentTimeout = $previous;
        }
    }

    /**
     * Replace the trait's client() so that — when no explicit timeout is
     * passed (which is exactly the bug in continueWithToolResults) — we
     * fall through to the captured currentTimeout instead of the trait's
     * hardcoded 60s default.
     *
     * Behaviour matches the trait's client() in every other respect.
     */
    protected function client(Provider $provider, ?int $timeout = null): PendingRequest
    {
        return Http::baseUrl($this->baseUrl($provider))
            ->withToken($provider->providerCredentials()['key'])
            ->timeout($timeout ?? $this->currentTimeout ?? 60)
            ->throw();
    }
}
