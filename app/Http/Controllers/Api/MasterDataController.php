<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterLocation;
use App\Models\MasterQualification;
use App\Models\MasterService;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class MasterDataController extends Controller
{
    // ─── Countries ───────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/countries',
        operationId: 'getCountries',
        summary: 'List all countries',
        description: 'Returns all countries available on the platform, sorted A→Z. Requires HMAC authentication.',
        tags: ['Master Data'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Countries fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Countries fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Country')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function countries(): JsonResponse
    {
        $countries = MasterCountry::orderBy('name')->get(['code', 'name']);

        return response()->json([
            'success' => true,
            'message' => 'Countries fetched successfully',
            'data'    => $countries,
        ]);
    }

    // ─── Cities ──────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/cities/{country_code}',
        operationId: 'getCitiesByCountry',
        summary: 'List active cities by country',
        description: 'Returns all active cities for the given ISO alpha-3 country code, sorted A→Z. Requires HMAC authentication.',
        tags: ['Master Data'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        parameters: [
            new OA\Parameter(
                name: 'country_code',
                in: 'path',
                required: true,
                description: 'ISO 3166-1 alpha-3 country code (case-insensitive, e.g. IND, USA, GBR)',
                schema: new OA\Schema(type: 'string', minLength: 3, maxLength: 3, example: 'IND')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cities fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Cities fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/City')
                        ),
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
                description: 'Country code not found',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Country code "XYZ" not found.'),
                    ]
                )
            ),
        ]
    )]
    public function cities(string $countryCode): JsonResponse
    {
        $countryCode = strtoupper($countryCode);

        if (! MasterCountry::find($countryCode)) {
            return response()->json([
                'success' => false,
                'message' => "Country code \"{$countryCode}\" not found.",
            ], 404);
        }

        $cities = MasterCity::where('country_code', $countryCode)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name', 'country_code']);

        return response()->json([
            'success' => true,
            'message' => 'Cities fetched successfully',
            'data'    => $cities,
        ]);
    }

    // ─── Locations ────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/locations/{city_id}',
        operationId: 'getLocationsByCity',
        summary: 'List active locations by city',
        description: 'Returns all active locations for the given city ID, sorted A→Z. Requires HMAC authentication.',
        tags: ['Master Data'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        parameters: [
            new OA\Parameter(
                name: 'city_id',
                in: 'path',
                required: true,
                description: 'ID of the city (from /cities/{country_code})',
                schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Locations fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Locations fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Location')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function locations(int $cityId): JsonResponse
    {
        $locations = MasterLocation::where('master_city_id', $cityId)
            ->where('status', true)
            ->orderBy('location')
            ->get(['id', 'location']);

        return response()->json([
            'success' => true,
            'message' => 'Locations fetched successfully',
            'data'    => $locations,
        ]);
    }

    // ─── Services ─────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/services',
        operationId: 'getServices',
        summary: 'List active services with sub-services',
        description: 'Returns all active parent services with their active child (sub) services, sorted A→Z. Use service IDs to filter doctor listings. Requires HMAC authentication.',
        tags: ['Master Data'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Services fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Services fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Service')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function services(): JsonResponse
    {
        $services = MasterService::with(['children' => fn ($q) => $q->active()->orderBy('service')])
            ->parents()
            ->active()
            ->orderBy('service')
            ->get(['id', 'service', 'parent_id'])
            ->map(fn ($s) => [
                'id'       => $s->id,
                'service'  => $s->service,
                'children' => $s->children->map(fn ($c) => ['id' => $c->id, 'service' => $c->service])->values(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Services fetched successfully',
            'data'    => $services,
        ]);
    }

    // ─── Qualifications ──────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/qualifications',
        operationId: 'getQualifications',
        summary: 'List active medical qualifications',
        description: 'Returns all active medical qualifications sorted A→Z (e.g. MBBS, MD, BDS). Use qualification IDs to filter doctor listings. Requires HMAC authentication.',
        tags: ['Master Data'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Qualifications fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Qualifications fetched successfully'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Qualification')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function qualifications(): JsonResponse
    {
        $qualifications = MasterQualification::where('status', true)
            ->orderBy('qualification')
            ->get(['id', 'qualification']);

        return response()->json([
            'success' => true,
            'message' => 'Qualifications fetched successfully',
            'data'    => $qualifications,
        ]);
    }

    // ─── Public Settings ──────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/v1/settings/public',
        operationId: 'getPublicSettings',
        summary: 'Get public platform settings',
        description: 'Returns all settings flagged as public as a flat key → value object. Useful for configuring client apps (e.g. site name, support contact, feature flags). Requires HMAC authentication.',
        tags: ['Settings'],
        security: [['HmacApiKey' => [], 'HmacTimestamp' => [], 'HmacNonce' => [], 'HmacSignature' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Settings fetched successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Settings fetched successfully'),
                        new OA\Property(property: 'data', ref: '#/components/schemas/PublicSettings'),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Missing or invalid HMAC headers',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function publicSettings(): JsonResponse
    {
        $settings = Setting::where('is_public', true)
            ->get(['key', 'value'])
            ->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'message' => 'Settings fetched successfully',
            'data'    => $settings,
        ]);
    }
}
