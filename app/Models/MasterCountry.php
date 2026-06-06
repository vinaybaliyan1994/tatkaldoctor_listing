<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterCountry extends Model
{
    protected $primaryKey = 'code';
    protected $keyType    = 'string';
    public    $incrementing = false;

    protected $fillable = ['code', 'name'];

    public function cities(): HasMany
    {
        return $this->hasMany(MasterCity::class, 'country_code', 'code');
    }
}
