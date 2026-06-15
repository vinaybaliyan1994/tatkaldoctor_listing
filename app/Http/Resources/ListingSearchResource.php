<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ListingSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $serviceNames = $this->service_names;
        $qualNames    = $this->qualification_names;
        $meta         = $this->meta_data ?? [];
        $isImported   = $this->isImported();
        $photoUrl = $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : (($this->meta_data['profile_photo_url'] ?? null) ?: null);

        return [
            'uuid'              => $this->uuid,
            'qr_slug'           => $this->qr_slug,
            'name'              => $this->name,
            'hospital_name'     => $this->hospital_name,
            'clinic_name'       => $this->hospital_name,
            'clinic_mobile'     => $isImported ? $this->personal_contact_no : null,
            'city'              => $this->city?->name ?? ($meta['city_name'] ?? null),
            'location'          => $this->location?->location ?? ($meta['location_name'] ?? null),
            'services'          => $serviceNames ?: array_values(array_filter([$meta['speciality'] ?? null])),
            'speciality'        => $serviceNames[0] ?? ($meta['speciality'] ?? null),
            'qualifications'    => $qualNames,
            'average_rating'    => (float) $this->average_rating,
            'profile_photo_url' => $photoUrl,
            'doctor_type'       => $this->doctorType(),
            'is_verified'       => $this->isTatkalVerified(),
            'badge'             => $this->isTatkalVerified() ? 'verified' : null,
            'booking_enabled'   => $this->bookingEnabled(),
        ];
    }
}
