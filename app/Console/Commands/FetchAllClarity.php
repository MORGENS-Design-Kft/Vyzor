<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpParser\Node\Stmt\TryCatch;

use function Laravel\Prompts\info;

class FetchAllClarity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-all-clarity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches clarity insights for all projects with an API key configured';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fetch Clarity insights for all projects...');

        $allProjectIds = \App\Models\Project::whereNotNull('clarity_api_key')->pluck('id');

        try {
            foreach ($allProjectIds as $projectId) {
                $this->info("Fetching Clarity insights for project ID: {$projectId}...");
                $this->call('app:fetch-clarity', [
                    'project' => $projectId,
                    '--days' => 1,
                ]);
            }
            $this->info('Finished fetching Clarity insights for all projects.');
        } catch (\Throwable $th) {
            $this->error('An error occurred while fetching Clarity insights: ' . $th->getMessage());
        }
    }
}
