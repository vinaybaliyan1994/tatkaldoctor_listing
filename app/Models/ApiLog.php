<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'api_key',
        'endpoint',
        'method',
        'request_ip',
        'request_headers',
        'response_status',
        'success',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'success'         => 'boolean',
        'created_at'      => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
