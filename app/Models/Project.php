<?php

namespace App\Models;

use App\ProjectStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'customer_id',
        'status',
        'domain',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatusEnum::class,
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
