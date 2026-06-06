<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'                 => $this->uuid,
            'name'                 => $this->name,
            'hospital_name'        => $this->hospital_name,
            'address'              => $this->address,
            'description'          => $this->description,
            'country'              => $this->country?->name,
            'city'                 => $this->city?->name,
            'location'             => $this->location?->location,
            'personal_contact_no'  => $this->personal_contact_no,
            'appointment_no'       => $this->appointment_no,
            'qualifications'       => $this->qualification_names,
            'services'             => $this->service_names,
            'latitude'             => $this->latitude === null ? null : (float) $this->latitude,
            'longitude'            => $this->longitude === null ? null : (float) $this->longitude,
            'average_rating'       => (float) $this->average_rating,
            'status'               => $this->status,
            'qr_slug'              => $this->qr_slug,
            'public_profile_url'   => $this->public_profile_url,
        ];
    }
}
