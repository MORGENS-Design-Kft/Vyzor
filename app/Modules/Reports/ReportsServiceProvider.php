<?php

namespace App\Modules\Reports;

use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            \App\Modules\Reports\Commands\GenerateReports::class,
        ]);
    }

    public function boot(): void
    {
        //
    }
}
