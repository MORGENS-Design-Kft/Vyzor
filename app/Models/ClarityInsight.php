<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClarityInsight extends Model
{
    protected $fillable = [
        'project_id',
        'metric_name',
        'dimension1',
        'dimension2',
        'dimension3',
        'data',
        'num_of_days',
        'fetched_for',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'fetched_for' => 'date',
        ];
    }
}
