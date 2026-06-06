<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterQualification extends Model
{
    protected $fillable = ['qualification', 'status'];

    protected $casts = ['status' => 'boolean'];
}
