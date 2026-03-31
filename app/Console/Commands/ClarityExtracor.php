<?php

namespace App\Console\Commands;

use App\Models\ClarityInsight;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ClarityExtracor extends Command
{
    protected $signature = 'app:clarity-extractor
        {--days=1 : Number of days to pull (1, 2, or 3)}
        {--dimension1= : First dimension (Browser, Device, Country/Region, OS, Source, Medium, Campaign, Channel, URL)}
        {--dimension2= : Second dimension}
        {--dimension3= : Third dimension}';

    protected $description = 'Pulls clarity data and saves it to the database';

    private const VALID_DIMENSIONS = [
        'Browser',
        'Device',
        'Country/Region',
        'OS',
        'Source',
        'Medium',
        'Campaign',
        'Channel',
        'URL',
    ];

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dimension1 = $this->option('dimension1');
        $dimension2 = $this->option('dimension2');
        $dimension3 = $this->option('dimension3');

        if (!in_array($days, [1, 2, 3])) {
            $this->error('Days must be 1, 2, or 3.');
            return self::FAILURE;
        }

        $dimensions = array_filter([$dimension1, $dimension2, $dimension3]);
        foreach ($dimensions as $dim) {
            if (!in_array($dim, self::VALID_DIMENSIONS)) {
                $this->error("Invalid dimension: {$dim}");
                $this->info('Valid dimensions: ' . implode(', ', self::VALID_DIMENSIONS));
                return self::FAILURE;
            }
        }

        $this->info('Fetching Clarity data...');

        $data = $this->fetchClarityData($days, $dimension1, $dimension2, $dimension3);

        if ($data === null) {
            $this->error('Failed to fetch data from Clarity API.');
            return self::FAILURE;
        }

        $this->saveToDatabase($data, $days, $dimension1, $dimension2, $dimension3);

        $this->info('Clarity data extraction completed successfully.');
        return self::SUCCESS;
    }

    private function fetchClarityData(int $days, ?string $dimension1, ?string $dimension2, ?string $dimension3): ?array
    {
        $token = config('services.clarity.token');

        if (!$token) {
            $this->error('CLARITY_KEY is not set in your .env file.');
            return null;
        }

        $params = ['numOfDays' => $days];

        if ($dimension1) {
            $params['dimension1'] = $dimension1;
        }
        if ($dimension2) {
            $params['dimension2'] = $dimension2;
        }
        if ($dimension3) {
            $params['dimension3'] = $dimension3;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->get('https://www.clarity.ms/export-data/api/v1/project-live-insights', $params);

        if ($response->failed()) {
            $this->error("API returned {$response->status()}: {$response->body()}");
            return null;
        }

        return $response->json();
    }

    private function saveToDatabase(array $data, int $days, ?string $dimension1, ?string $dimension2, ?string $dimension3): void
    {
        $today = now()->toDateString();

        foreach ($data as $metric) {
            $metricName = $metric['metricName'] ?? 'Unknown';

            ClarityInsight::updateOrCreate(
                [
                    'metric_name' => $metricName,
                    'dimension1' => $dimension1,
                    'dimension2' => $dimension2,
                    'dimension3' => $dimension3,
                    'num_of_days' => $days,
                    'fetched_for' => $today,
                ],
                [
                    'data' => $metric['information'] ?? [],
                ]
            );

            $this->line("Saved metric: {$metricName}");
        }
    }
}
