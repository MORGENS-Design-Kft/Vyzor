<?php

namespace App\Ai\Agents;

use App\AiContextType;
use App\Models\AiContext;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Timeout(300)]
class ReportAnalyst implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        $context = AiContext::active()
            ->ofType(AiContextType::SYSTEM)
            ->forModel()
            ->where('slug', 'report-analyst-instructions')
            ->first();

        return $context?->context ?? '';
    }
}
