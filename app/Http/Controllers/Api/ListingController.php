<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ListingDetailResource;
use App\Http\Resources\ListingSearchResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ListingController extends Controller
{
    // ─── Search ───────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/listings/search',
        operationId: 'searchListings',
        summary: 'Search active doctor listings',
        description: 'Returns paginated active, verified doctor listings. Supports filtering by location, service, qualification and free-text search. Requires HMAC authentication.',
        tags: ['Listings'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        parameters: [
            new OA\Parameter(
                name: 'country_code', in: 'query', required: false,
                description: 'Filter by ISO alpha-3 country code',
                schema: new OA\Schema(type: 'string', example: 'IND')
            ),
            new OA\Parameter(
                name: 'master_city_id', in: 'query', required: false,
                description: 'Filter by city ID (from /cities/{country_code})',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'master_location_id', in: 'query', required: false,
                description: 'Filter by location ID (from /locations/{city_id})',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'service_id', in: 'query', required: false,
                description: 'Filter by service ID (from /services). Matches both parent and child services.',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\Parameter(
                name: 'search', in: 'query', required: false,
                description: 'Free-text search on doctor name or hospital name',
                schema: new OA\Schema(type: 'string', maxLength: 191, example: 'Ravi')
            ),
            new OA\Parameter(
                name: 'per_page', in: 'query', required: false,
                description: 'Results per page (1–100, default 20)',
                schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100, default: 20, example: 20)
            ),
            new OA\Parameter(
                name: 'page', in: 'query', required: false,
                description: 'Page number (default 1)',
                schema: new OA\Schema(type: 'integer', minimum: 1, default: 1, example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listings fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Listings fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ListingSearch')
                        ),
                        new OA\Property(property: 'pagination', ref: '#/components/schemas/Pagination'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error (invalid parameter values)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function search(Request $request): JsonResponse
    {
        if ($request->filled('country_code')) {
            $request->merge(['country_code' => strtoupper($request->input('country_code'))]);
        }

        $validated = $request->validate([
            'country_code'        => 'nullable|string|size:3|exists:master_countries,code',
            'master_city_id'      => 'nullable|integer|exists:master_cities,id',
            'master_location_id'  => 'nullable|integer|exists:master_locations,id',
            'service_id'          => 'nullable|integer|exists:master_services,id',
            'search'              => 'nullable|string|max:191',
            'page'                => 'nullable|integer|min:1',
            'per_page'            => 'nullable|integer|min:1|max:100',
        ]);

        $query = Listing::with(['country', 'city', 'location'])
            ->where('status', true)
            ->where('verification_status', 'approved');

        if (! empty($validated['country_code'])) {
            $query->where('country_code', $validated['country_code']);
        }

        if (! empty($validated['master_city_id'])) {
            $query->where('master_city_id', $validated['master_city_id']);
        }

        if (! empty($validated['master_location_id'])) {
            $query->where('master_location_id', $validated['master_location_id']);
        }

        if (! empty($validated['service_id'])) {
            $query->whereJsonContains('services', (int) $validated['service_id']);
        }

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hospital_name', 'like', "%{$search}%");
            });
        }

        $listings = $query->orderByDesc('average_rating')
            ->orderBy('name')
            ->paginate((int) ($validated['per_page'] ?? 20));

        return response()->json([
            'success'    => true,
            'message'    => 'Listings fetched successfully',
            'data'       => ListingSearchResource::collection($listings->getCollection())->resolve($request),
            'pagination' => [
                'current_page' => $listings->currentPage(),
                'total'        => $listings->total(),
                'per_page'     => $listings->perPage(),
            ],
        ]);
    }

    // ─── Show by QR slug ──────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/listings/slug/{qrSlug}',
        operationId: 'showListingBySlug',
        summary: 'Get doctor profile by QR slug',
        description: 'Returns a full active, verified doctor profile by its QR slug. '
            . 'Used by public profile pages accessible via QR code scan. '
            . 'This route must be called **before** `/listings/{uuid}` to avoid parameter collision. '
            . 'Requires HMAC authentication.',
        tags: ['Listings'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        parameters: [
            new OA\Parameter(
                name: 'qrSlug',
                in: 'path',
                required: true,
                description: 'Unique QR slug generated when a listing is approved (e.g. dr-ravi-kumar-550e8400)',
                schema: new OA\Schema(type: 'string', example: 'dr-ravi-kumar-550e8400')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listing fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Listing fetched successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/ListingDetail'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Listing not found (no active approved listing with this slug)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Listing not found.'),
                    ]
                )
            ),
        ]
    )]
    public function showBySlug(string $qrSlug, Request $request): JsonResponse
    {
        $listing = Listing::with(['country', 'city', 'location'])
            ->where('qr_slug', $qrSlug)
            ->where('status', true)
            ->where('verification_status', 'approved')
            ->first();

        if (! $listing) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Listing fetched successfully',
            'data'    => (new ListingDetailResource($listing))->resolve($request),
        ]);
    }

    // ─── Show by UUID ─────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/listings/{uuid}',
        operationId: 'showListing',
        summary: 'Get doctor profile by UUID',
        description: 'Returns a full active, verified doctor profile by its UUID. '
            . 'The response includes all profile fields, contact details, qualifications, services and QR data. '
            . 'Requires HMAC authentication.',
        tags: ['Listings'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        parameters: [
            new OA\Parameter(
                name: 'uuid',
                in: 'path',
                required: true,
                description: 'UUID of the doctor listing',
                schema: new OA\Schema(type: 'string', format: 'uuid',
                    example: '550e8400-e29b-41d4-a716-446655440000')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listing fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Listing fetched successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/ListingDetail'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Listing not found (no active approved listing with this UUID)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Listing not found.'),
                    ]
                )
            ),
        ]
    )]
    public function show(string $uuid, Request $request): JsonResponse
    {
        $listing = Listing::with(['country', 'city', 'location'])
            ->where('uuid', $uuid)
            ->where('status', true)
            ->where('verification_status', 'approved')
            ->first();

        if (! $listing) {
            return response()->json([
                'success' => false,
                'message' => 'Listing not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Listing fetched successfully',
            'data'    => (new ListingDetailResource($listing))->resolve($request),
        ]);
    }
}
