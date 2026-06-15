<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MasterCity;
use App\Services\ListingRegistrationMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingCreateController extends Controller
{
    public function __construct(private readonly ListingRegistrationMatcher $matcher) {}

    // POST /api/v1/listings
    // Called by solution's DoctorListingProvisioner when a registration is approved.
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:191'],
            'hospital_name'      => ['nullable', 'string', 'max:191'],
            'qualification'      => ['nullable', 'string', 'max:191'],
            'registration_no'    => ['nullable', 'string', 'max:100'],
            'address'            => ['nullable', 'string', 'max:500'],
            'city'               => ['nullable', 'string', 'max:100'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'country_code'       => ['nullable', 'string', 'max:5'],
            'status'             => ['nullable', 'boolean'],
            // verification_status is intentionally excluded — all API-created listings
            // start as pending and must be reviewed by a super-admin in the web UI.
        ]);

        // Try to resolve master_city_id from city name
        $masterCityId = null;
        if (!empty($validated['city'])) {
            $city = MasterCity::where('name', 'like', '%' . $validated['city'] . '%')->first();
            $masterCityId = $city?->id;
        }

        $countryCode = strtoupper($validated['country_code'] ?? 'IND');

        $payload = [
            'name'                => $validated['name'],
            'hospital_name'       => $validated['hospital_name'] ?? null,
            'address'             => $validated['address'] ?? null,
            'personal_contact_no' => $validated['phone'] ?? null,
            'country_code'        => $countryCode,
            'master_city_id'      => $masterCityId,
            'status'              => $validated['status'] ?? false,
            'verification_status' => 'pending',
            'source'              => 'solution_registration',
            'meta_data'           => [
                'qualification'   => $validated['qualification'] ?? null,
                'registration_no' => $validated['registration_no'] ?? null,
            ],
        ];

        $listing = $this->matcher->findExisting([
            'name'            => $validated['name'],
            'phone'           => $validated['phone'] ?? null,
            'registration_no' => $validated['registration_no'] ?? null,
            'country_code'    => $countryCode,
        ]);

        if ($listing) {
            $payload['meta_data'] = array_filter(
                array_merge($listing->meta_data ?? [], $payload['meta_data']),
                fn ($value) => $value !== null
            );
            $listing->update(array_filter($payload, fn ($value) => $value !== null));
            $statusCode = 200;
        } else {
            $listing = Listing::create($payload);
            $statusCode = 201;
        }

        return response()->json([
            'success' => true,
            'message' => $statusCode === 201 ? 'Listing created successfully' : 'Existing listing updated successfully',
            'data'    => [
                'uuid'     => $listing->uuid,
                'qr_slug'  => $listing->qr_slug,
                'status'   => $listing->status,
                'verification_status' => $listing->verification_status,
            ],
        ], $statusCode);
    }
}
