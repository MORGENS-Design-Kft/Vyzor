<?php

namespace App\Jobs;

use App\Models\Report;
use App\ReportStatusEnum;
use App\Services\ReportGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateAiReport implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public Report $report,
    ) {}

    public function handle(ReportGeneratorService $service): void
    {
        $service->generate($this->report);
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Report generation failed', [
            'report_id' => $this->report->id,
            'project_id' => $this->report->project_id,
            'preset' => $this->report->preset,
            'error' => $exception?->getMessage(),
        ]);

        $this->report->update([
            'content' => 'Error: ' . ($exception?->getMessage() ?? 'The report generation timed out.'),
            'status' => ReportStatusEnum::FAILED,
        ]);
    }
}
