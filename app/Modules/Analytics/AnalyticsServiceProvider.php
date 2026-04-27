<?php

namespace App\Modules\Analytics;

use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            \App\Modules\Analytics\Clarity\Commands\FetchClarity::class,
            \App\Modules\Analytics\Clarity\Commands\FetchAllClarity::class,
        ]);
    }

    public function boot(): void
    {
        //
    }
}
