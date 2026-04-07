<?php

namespace App\Services;

use App\Ai\Agents\ReportAnalyst;
use App\Models\ClarityInsight;
use App\Models\LLMContextPreset;
use App\Models\Report;
use App\ReportStatusEnum;

class ReportGeneratorService
{
    public function generate(Report $report): Report
    {
        $report->update(['status' => ReportStatusEnum::GENERATING]);

        try {
            $prompt = $this->buildPrompt($report);

            $provider = config('ai.default', 'openai');
            $response = ReportAnalyst::make()->prompt($prompt, provider: $provider);

            $report->update([
                'content' => (string) $response,
                'ai_model_name' => $provider,
                'status' => ReportStatusEnum::COMPLETED,
            ]);
        } catch (\Throwable $e) {
            $report->update([
                'content' => 'Error: ' . $e->getMessage(),
                'status' => ReportStatusEnum::FAILED,
            ]);
        }

        return $report->refresh();
    }

    protected function buildPrompt(Report $report): string
    {
        $parts = [];

        // Add preset context
        if ($report->preset) {
            $preset = LLMContextPreset::where('slug', $report->preset)->first();
            if ($preset) {
                $parts[] = $preset->context;
            }
        }

        // Add custom prompt
        if ($report->custom_prompt) {
            $parts[] = "\n## Additional Instructions\n" . $report->custom_prompt;
        }

        // Add Clarity data
        $clarityData = ClarityInsight::where('project_id', $report->project_id)
            ->when($report->aspect_date_from, fn($q) => $q->where('date_from', '>=', $report->aspect_date_from))
            ->when($report->aspect_date_to, fn($q) => $q->where('date_to', '<=', $report->aspect_date_to))
            ->get();

        if ($clarityData->isNotEmpty()) {
            $parts[] = "\n## Clarity Data\n";
            foreach ($clarityData as $insight) {
                $parts[] = "### {$insight->metric_name}" .
                    ($insight->dimension1 ? " (Dimension: {$insight->dimension1})" : '') .
                    "\nDate range: {$insight->date_from->format('Y-m-d')} to {$insight->date_to->format('Y-m-d')}\n" .
                    "```json\n" . json_encode($insight->data, JSON_PRETTY_PRINT) . "\n```\n";
            }
        } else {
            $parts[] = "\n## Note\nNo Clarity data available for the selected date range ({$report->aspect_date_from?->format('Y-m-d')} to {$report->aspect_date_to?->format('Y-m-d')}). " .
                "Please provide a general analysis framework and recommendations based on the preset context.";
        }

        $parts[] = "\n## Output Format\nProvide the report in markdown format with clear headings, bullet points, and actionable recommendations.";

        return implode("\n", $parts);
    }
}
