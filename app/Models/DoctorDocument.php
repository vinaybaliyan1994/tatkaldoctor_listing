<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorDocument extends Model
{
    protected $fillable = [
        'listing_id',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'status',
        'remarks',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size'   => 'integer',
    ];

    public const DOCUMENT_TYPES = [
        'aadhaar'              => 'Aadhaar Card',
        'pan'                  => 'PAN Card',
        'medical_registration' => 'Medical Registration',
        'degree_certificate'   => 'Degree Certificate',
        'clinic_license'       => 'Clinic License',
        'other'                => 'Other',
    ];

    public const STATUSES = ['pending', 'approved', 'rejected'];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return self::DOCUMENT_TYPES[$this->document_type] ?? ucfirst($this->document_type);
    }
}
