<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at'   => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function scopeLive(Builder $q, string $placement): Builder
    {
        return $q->where('placement', $placement)
                 ->where('is_active', true)
                 ->where(fn ($s) => $s->whereNull('starts_at')->orWhere('starts_at', '<=', today()))
                 ->where(fn ($s) => $s->whereNull('ends_at')->orWhere('ends_at', '>=', today()))
                 ->orderBy('sort_order');
    }
}
