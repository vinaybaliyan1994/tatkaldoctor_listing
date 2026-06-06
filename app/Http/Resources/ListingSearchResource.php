<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid'           => $this->uuid,
            'name'           => $this->name,
            'hospital_name'  => $this->hospital_name,
            'city'           => $this->city?->name,
            'location'       => $this->location?->location,
            'average_rating' => (float) $this->average_rating,
        ];
    }
}
