<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $meta     = $this->meta_data ?? [];
        $photoUrl = $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : (($meta['profile_photo_url'] ?? null) ?: null);

        return [
            'uuid'                 => $this->uuid,
            'name'                 => $this->name,
            'hospital_name'        => $this->hospital_name,
            'address'              => $this->address,
            'description'          => $this->description,
            'country'              => $this->country?->name,
            'city'                 => $this->city?->name ?? ($meta['city_name'] ?? null),
            'location'             => $this->location?->location ?? ($meta['location_name'] ?? null),
            'personal_contact_no'  => $this->personal_contact_no,
            'appointment_no'       => $this->appointment_no,
            'clinic_name'          => $this->hospital_name,
            'clinic_mobile'        => $this->isImported() ? $this->personal_contact_no : null,
            'qualifications'       => $this->qualification_names,
            'services'             => $this->service_names ?: array_values(array_filter([$meta['speciality'] ?? null])),
            'speciality'           => $this->service_names[0] ?? ($meta['speciality'] ?? null),
            'latitude'             => $this->latitude === null ? null : (float) $this->latitude,
            'longitude'            => $this->longitude === null ? null : (float) $this->longitude,
            'average_rating'       => (float) $this->average_rating,
            'status'               => $this->status,
            'verification_status'  => $this->verification_status,
            'qr_slug'              => $this->qr_slug,
            'qr_generated_at'      => $this->qr_generated_at?->toIso8601String(),
            'qr_code_url'          => $this->qr_code_path
                ? Storage::disk('public')->url($this->qr_code_path)
                : null,
            'public_profile_url'   => $this->public_profile_url,
            'profile_photo_url'    => $photoUrl,
            'doctor_type'          => $this->doctorType(),
            'is_verified'          => $this->isTatkalVerified(),
            'badge'                => $this->isTatkalVerified() ? 'verified' : null,
            'booking_enabled'      => $this->bookingEnabled(),
            'external_source'      => $this->external_source,
            'external_url'         => $this->external_url,
            'document_summary'     => [
                'pending'  => $this->doctorDocuments()->where('status', 'pending')->count(),
                'approved' => $this->doctorDocuments()->where('status', 'approved')->count(),
                'rejected' => $this->doctorDocuments()->where('status', 'rejected')->count(),
            ],
        ];
    }
}
