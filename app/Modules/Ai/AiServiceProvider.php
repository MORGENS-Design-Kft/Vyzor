<?php

namespace App\Modules\Ai;

use App\Modules\Ai\Gateway\TimeoutAwareOpenAiGateway;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\AiManager;
use Laravel\Ai\Providers\OpenAiProvider;

class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Replace the upstream OpenAi driver with our timeout-aware variant.
        // See TimeoutAwareOpenAiGateway for the full bug-and-fix rationale —
        // short version: laravel/ai's tool-call follow-up requests forget to
        // forward the agent's #[Timeout(N)] attribute, falling back to a
        // hardcoded 60s default which times out GA reports mid-tool-loop.
        $this->app->afterResolving(AiManager::class, function (AiManager $manager): void {
            $manager->extend('openai', function ($app, $config) {
                $events = $app->make(Dispatcher::class);

                return new OpenAiProvider(
                    new TimeoutAwareOpenAiGateway($events),
                    $config,
                    $events,
                );
            });
        });
    }
}
