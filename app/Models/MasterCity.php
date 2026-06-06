<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterCity extends Model
{
    protected $fillable = ['country_code', 'name', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(MasterCountry::class, 'country_code', 'code');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(MasterLocation::class, 'master_city_id');
    }
}
