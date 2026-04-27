<?php

namespace App\Modules\Analytics\Clarity\Models;

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
        'date_from',
        'date_to',
        'fetched_for',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'date_from' => 'date',
            'date_to' => 'date',
            'fetched_for' => 'datetime',
        ];
    }
}
