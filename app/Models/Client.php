<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Client extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'api_key',
        'secret_key',
        'avail_from_date',
        'avail_to_date',
        'status',
    ];

    protected $hidden = ['secret_key'];

    protected $casts = [
        'avail_from_date' => 'date',
        'avail_to_date'   => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            $client->uuid       = (string) Str::uuid();
            $client->api_key    = self::generateApiKey();
            $client->secret_key = Crypt::encryptString(self::generateSecretKey());
        });
    }

    public static function generateApiKey(): string
    {
        return bin2hex(random_bytes(16)); // 32-char hex
    }

    public static function generateSecretKey(): string
    {
        return bin2hex(random_bytes(32)); // 64-char hex
    }

    public function getDecryptedSecretKey(): string
    {
        return Crypt::decryptString($this->secret_key);
    }

    public function clientSubscriptions(): HasMany
    {
        return $this->hasMany(ClientSubscription::class);
    }

    public function activeSubscription(): ?ClientSubscription
    {
        return $this->clientSubscriptions()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->latest()
            ->first();
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $today = now()->toDateString();

        if ($this->avail_from_date && $this->avail_from_date->toDateString() > $today) {
            return false;
        }

        if ($this->avail_to_date && $this->avail_to_date->toDateString() < $today) {
            return false;
        }

        return true;
    }
}
