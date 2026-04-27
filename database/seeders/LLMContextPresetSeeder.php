<?php

namespace Database\Seeders;

use App\Modules\Ai\Contexts\Models\LLMContextPreset;
use Illuminate\Database\Seeder;

class LLMContextPresetSeeder extends Seeder
{
    public function run(): void
    {
        $presets = [
            [
                'name' => 'Traffic Overview',
                'slug' => 'traffic-overview',
                'description' => 'Comprehensive overview of website traffic patterns, sessions, and user trends.',
                'label_color' => '#3b82f6',
                'icon' => 'chart-line-up',
                'sort_order' => 1,
                'context' => file_get_contents(resource_path('ai-prompts/presets/traffic-overview.md')),
            ],
            [
                'name' => 'UX Issues',
                'slug' => 'ux-issues',
                'description' => 'Analysis of UX signals like dead clicks, rage clicks, and quick backs.',
                'label_color' => '#f43f5e',
                'icon' => 'warning',
                'sort_order' => 2,
                'context' => file_get_contents(resource_path('ai-prompts/presets/ux-issues.md')),
            ],
            [
                'name' => 'Engagement Analysis',
                'slug' => 'engagement-analysis',
                'description' => 'Deep dive into user engagement metrics and content consumption patterns.',
                'label_color' => '#8b5cf6',
                'icon' => 'chart-bar',
                'sort_order' => 3,
                'context' => file_get_contents(resource_path('ai-prompts/presets/engagement-analysis.md')),
            ],
            [
                'name' => 'Device & Browser Analysis',
                'slug' => 'device-browser-analysis',
                'description' => 'Breakdown of device, browser, and OS usage with compatibility insights.',
                'label_color' => '#06b6d4',
                'icon' => 'device-mobile',
                'sort_order' => 4,
                'context' => file_get_contents(resource_path('ai-prompts/presets/device-browser-analysis.md')),
            ],
            [
                'name' => 'Content Performance',
                'slug' => 'content-performance',
                'description' => 'Page-level performance analysis including top pages and engagement.',
                'label_color' => '#f59e0b',
                'icon' => 'article',
                'sort_order' => 5,
                'context' => file_get_contents(resource_path('ai-prompts/presets/content-performance.md')),
            ],
            [
                'name' => 'Weekly Summary',
                'slug' => 'weekly-summary',
                'description' => 'Executive-style weekly summary covering all major performance aspects.',
                'label_color' => '#22c55e',
                'icon' => 'calendar-check',
                'sort_order' => 6,
                'context' => file_get_contents(resource_path('ai-prompts/presets/weekly-summary.md')),
            ],
        ];

        foreach ($presets as $preset) {
            LLMContextPreset::updateOrCreate(
                ['slug' => $preset['slug']],
                $preset,
            );
        }
    }
}
