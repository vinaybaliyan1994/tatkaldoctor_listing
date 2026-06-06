<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterService extends Model
{
    protected $fillable = ['parent_id', 'service', 'status'];

    protected $casts = [
        'status'    => 'boolean',
        'parent_id' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MasterService::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MasterService::class, 'parent_id');
    }

    public function scopeParents(Builder $query): Builder
    {
        return $query->where('parent_id', 0);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
