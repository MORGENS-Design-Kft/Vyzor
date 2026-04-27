<?php

namespace App\Modules\Reports\Commands;

use App\Modules\Reports\Enums\ReportStatusEnum;
use App\Modules\Reports\Models\Report;
use App\Modules\Reports\Services\ReportGeneratorService;
use Illuminate\Console\Command;

class GenerateReports extends Command
{
    protected $signature = 'app:generate-reports
                            {--report= : Generate a specific report by ID}
                            {--all : Process all pending reports}';

    protected $description = 'Process pending AI reports using Claude';

    public function handle(ReportGeneratorService $service): int
    {
        if ($reportId = $this->option('report')) {
            $report = Report::find($reportId);
            if (!$report) {
                $this->error("Report #{$reportId} not found.");
                return self::FAILURE;
            }
            return $this->processReport($service, $report);
        }

        $reports = Report::where('status', ReportStatusEnum::PENDING)
            ->where('is_ai', true)
            ->get();

        if ($reports->isEmpty()) {
            $this->info('No pending reports to process.');
            return self::SUCCESS;
        }

        $this->info("Processing {$reports->count()} pending report(s)...");

        foreach ($reports as $report) {
            $this->processReport($service, $report);
        }

        return self::SUCCESS;
    }

    protected function processReport(ReportGeneratorService $service, Report $report): int
    {
        $this->info("Generating report #{$report->id}: {$report->title}");

        $report = $service->generate($report);

        if ($report->status === ReportStatusEnum::COMPLETED) {
            $this->info("Report #{$report->id} completed.");
            return self::SUCCESS;
        }

        $this->error("Report #{$report->id} failed: {$report->content}");
        return self::FAILURE;
    }
}
