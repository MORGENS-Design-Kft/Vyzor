<?php

use App\Models\Project;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    $projects = Project::whereNotNull('clarity_api_key')->get();

    foreach ($projects as $project) {
        Artisan::call('app:fetch-clarity', ['project' => $project->id]);
    }
})->everyFourHours()->name('fetch-clarity-all-projects')->withoutOverlapping();
