<?php

namespace App\Modules\Ai\Contexts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LLMContextPreset extends Model
{
    protected $table = 'llm_context_presets';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'label_color',
        'icon',
        'context',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $preset) {
            if (empty($preset->slug)) {
                $preset->slug = Str::slug($preset->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
