<?php

namespace App\Jobs;

use App\Models\Report;
use App\Services\ReportGeneratorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateAiReport implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Report $report,
    ) {}

    public function handle(ReportGeneratorService $service): void
    {
        $service->generate($this->report);
    }
}
