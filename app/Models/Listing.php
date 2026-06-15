<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Listing extends Model
{
    protected $fillable = [
        'uuid', 'category_id', 'country_code', 'master_city_id', 'master_location_id',
        'name', 'hospital_name', 'address', 'description',
        'personal_contact_no', 'appointment_no', 'email',
        'qualifications', 'services', 'meta_data',
        'latitude', 'longitude', 'average_rating', 'status', 'source',
        'is_imported', 'is_verified_by_tatkaldoctor', 'external_source', 'external_url',
        'verification_status', 'verified_at', 'verified_by', 'rejection_reason',
        'qr_slug', 'public_profile_url', 'qr_code_path', 'qr_generated_at',
        'profile_photo_path',
    ];

    protected $casts = [
        'qualifications'      => 'array',
        'services'            => 'array',
        'meta_data'           => 'array',
        'status'              => 'boolean',
        'is_imported'         => 'boolean',
        'is_verified_by_tatkaldoctor' => 'boolean',
        'average_rating'      => 'decimal:2',
        'latitude'            => 'decimal:7',
        'longitude'           => 'decimal:7',
        'verified_at'     => 'datetime',
        'qr_generated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            $listing->uuid = (string) Str::uuid();
        });
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(MasterCountry::class, 'country_code', 'code');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(MasterCity::class, 'master_city_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(MasterLocation::class, 'master_location_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function doctorDocuments(): HasMany
    {
        return $this->hasMany(DoctorDocument::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(DoctorDocument::class, 'listing_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ListingAuditLog::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('status', true)
            ->where(function (Builder $query): void {
                $query->where('verification_status', 'approved')
                    ->orWhere('is_imported', true);
            });
    }

    public function scopeImported(Builder $query): Builder
    {
        return $query->where('is_imported', true);
    }

    public function isImported(): bool
    {
        return (bool) $this->is_imported;
    }

    public function isTatkalVerified(): bool
    {
        return ! $this->isImported()
            && $this->verification_status === 'approved'
            && (bool) $this->is_verified_by_tatkaldoctor;
    }

    public function bookingEnabled(): bool
    {
        return $this->isTatkalVerified();
    }

    public function doctorType(): string
    {
        return $this->isImported() ? 'imported' : 'verified';
    }

    public function getQualificationNamesAttribute(): array
    {
        if (empty($this->qualifications)) {
            return [];
        }

        return MasterQualification::whereIn('id', $this->qualifications)
            ->orderBy('qualification')
            ->pluck('qualification')
            ->toArray();
    }

    public function getServiceNamesAttribute(): array
    {
        if (empty($this->services)) {
            return [];
        }

        return MasterService::whereIn('id', $this->services)
            ->orderBy('service')
            ->pluck('service')
            ->toArray();
    }
}
