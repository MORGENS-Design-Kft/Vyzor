<?php

namespace App\Modules\Ai\Tools;

use App\Modules\Projects\Models\Project;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Throwable;

/**
 * Base class for AI function-calling tools that operate within a single
 * project's scope.
 *
 * --------------------------------------------------------------------------
 * Why this exists
 * --------------------------------------------------------------------------
 * Every Vyzor AI tool follows the same shape:
 *
 *   1. It is bound to a Project at construction so it can never leak data
 *      across projects (the AI agent receives a tool whose scope is fixed).
 *   2. It returns a JSON-encoded payload to the LLM — successful results
 *      and errors are both shaped consistently so the model can parse them
 *      without surprises.
 *   3. Any thrown exception during execution becomes a {"error": "..."}
 *      response instead of crashing the whole agent run.
 *
 * Subclasses implement execute() instead of handle(); the base wires the
 * try/catch and JSON serialisation. This keeps the actual tool logic free
 * of boilerplate while guaranteeing a uniform response shape.
 *
 * --------------------------------------------------------------------------
 * How to subclass
 * --------------------------------------------------------------------------
 *   final class MyTool extends ProjectScopedTool {
 *       public function description(): string { ... }
 *       public function schema(JsonSchema $schema): array { ... }
 *
 *       protected function execute(Request $request): mixed
 *       {
 *           // Use $this->project for the active scope.
 *           // Return any value JSON-encodable — array, scalar, etc.
 *           // Throw to signal an error; base class catches and returns
 *           // a clean {"error":...} payload.
 *       }
 *   }
 *
 * If the subclass needs additional dependencies, override the constructor
 * and chain to parent::__construct($project) so the project binding stays
 * mandatory:
 *
 *   public function __construct(
 *       Project $project,
 *       public readonly SomeService $svc,
 *   ) {
 *       parent::__construct($project);
 *   }
 */
abstract class ProjectScopedTool implements Tool
{
    public function __construct(
        public readonly Project $project,
    ) {}

    public function handle(Request $request): Stringable|string
    {
        try {
            $result = $this->execute($request);
        } catch (Throwable $e) {
            return $this->errorResponse($e);
        }

        return $this->successResponse($result);
    }

    /**
     * The tool-specific work. Receive the LLM-supplied arguments via $request,
     * return any JSON-encodable value, or throw to signal an error.
     */
    abstract protected function execute(Request $request): mixed;

    /**
     * Wrap a thrown exception into the standard {"error": "..."} response.
     * Subclasses may override to add classification (e.g. distinguishing
     * upstream provider errors from local validation errors).
     */
    protected function errorResponse(Throwable $e): string
    {
        return (string) json_encode(
            ['error' => $e->getMessage()],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    /**
     * Wrap a successful result into a JSON payload for the LLM. Subclasses
     * may override to add envelope fields (timestamps, source markers, etc.).
     */
    protected function successResponse(mixed $result): string
    {
        return (string) json_encode(
            $result,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }
}
