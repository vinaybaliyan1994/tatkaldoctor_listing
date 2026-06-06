<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterLocation extends Model
{
    protected $fillable = ['master_city_id', 'location', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(MasterCity::class, 'master_city_id');
    }
}
