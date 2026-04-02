<?php

namespace Database\Seeders;

use App\Models\LLMContextPreset;
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
                'context' => <<<'MD'
# Traffic Overview Report

Analyze the website traffic data for the selected date range and provide a comprehensive overview.

## Focus Areas
- Total sessions and unique users trends
- Bot traffic ratio and any anomalies
- Pages per session and how it compares to typical benchmarks
- Traffic sources and referral patterns
- Any notable spikes or drops in traffic

## Expected Output
Provide a summary that highlights key traffic metrics, identifies trends, and flags any anomalies worth investigating. Include actionable recommendations where applicable.
MD,
            ],
            [
                'name' => 'UX Issues',
                'slug' => 'ux-issues',
                'description' => 'Analysis of UX signals like dead clicks, rage clicks, and quick backs.',
                'label_color' => '#f43f5e',
                'icon' => 'warning',
                'sort_order' => 2,
                'context' => <<<'MD'
# UX Issues Analysis Report

Analyze the UX signal data (dead clicks, rage clicks, quick backs, excessive scroll, script errors, error clicks) for the selected date range.

## Focus Areas
- Identify the most critical UX issues based on frequency and session impact
- Dead clicks: where users click expecting interaction but nothing happens
- Rage clicks: signs of user frustration from rapid repeated clicks
- Quick backs: pages where users immediately navigate away
- Excessive scrolling: content layout or findability issues
- Script errors and error clicks: technical issues affecting user experience

## Expected Output
Prioritize UX issues by severity and user impact. Provide specific, actionable recommendations for fixing each identified issue. Suggest which issues to address first for maximum user experience improvement.
MD,
            ],
            [
                'name' => 'Engagement Analysis',
                'slug' => 'engagement-analysis',
                'description' => 'Deep dive into user engagement metrics and content consumption patterns.',
                'label_color' => '#8b5cf6',
                'icon' => 'chart-bar',
                'sort_order' => 3,
                'context' => <<<'MD'
# Engagement Analysis Report

Analyze user engagement metrics including time on site, active time, scroll depth, and session behavior for the selected date range.

## Focus Areas
- Total time vs active time ratio (how engaged are users really?)
- Average scroll depth and what it reveals about content consumption
- Session duration patterns and engagement quality
- Pages per session and content discovery paths
- Comparison of engagement across different time periods if data allows

## Expected Output
Provide insights into how users are engaging with the website content. Identify areas where engagement is strong and where it drops off. Suggest improvements for increasing meaningful user interaction.
MD,
            ],
            [
                'name' => 'Device & Browser Analysis',
                'slug' => 'device-browser-analysis',
                'description' => 'Breakdown of device, browser, and OS usage with compatibility insights.',
                'label_color' => '#06b6d4',
                'icon' => 'device-mobile',
                'sort_order' => 4,
                'context' => <<<'MD'
# Device & Browser Analysis Report

Analyze the device, browser, and operating system breakdown data for the selected date range.

## Focus Areas
- Device distribution (desktop, mobile, tablet) and session behavior per device
- Browser usage patterns and any browser-specific issues
- Operating system breakdown
- Country/region distribution and geographic patterns
- Cross-device user behavior differences

## Expected Output
Identify the primary audience segments by device and browser. Flag any compatibility concerns or device-specific issues. Recommend optimization priorities based on the audience breakdown.
MD,
            ],
            [
                'name' => 'Content Performance',
                'slug' => 'content-performance',
                'description' => 'Page-level performance analysis including top pages and engagement.',
                'label_color' => '#f59e0b',
                'icon' => 'article',
                'sort_order' => 5,
                'context' => <<<'MD'
# Content Performance Report

Analyze the page-level performance data including popular pages, page titles, and content engagement for the selected date range.

## Focus Areas
- Top performing pages by visits and engagement
- Pages with high traffic but low engagement (potential improvement targets)
- Content that drives the most user interaction
- Entry and exit page patterns
- URL and page title analysis for SEO alignment

## Expected Output
Rank content by performance and identify opportunities. Highlight pages that need attention (high bounce, low engagement) and pages that are performing well. Provide recommendations for content strategy improvements.
MD,
            ],
            [
                'name' => 'Weekly Summary',
                'slug' => 'weekly-summary',
                'description' => 'Executive-style weekly summary covering all major performance aspects.',
                'label_color' => '#22c55e',
                'icon' => 'calendar-check',
                'sort_order' => 6,
                'context' => <<<'MD'
# Weekly Summary Report

Create a concise weekly summary covering all major aspects of website performance for the selected date range.

## Focus Areas
- Traffic highlights (sessions, users, notable changes)
- Engagement summary (time on site, scroll depth)
- Top UX issues that need attention
- Device/browser breakdown highlights
- Top performing and underperforming content

## Expected Output
A brief, executive-style summary suitable for stakeholder review. Use bullet points and keep the language clear and non-technical. Highlight the 3-5 most important takeaways and suggest immediate action items.
MD,
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
