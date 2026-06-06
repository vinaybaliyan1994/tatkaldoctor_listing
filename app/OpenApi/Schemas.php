<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Reusable OpenAPI schema definitions.
 * Referenced via $ref: '#/components/schemas/{name}'.
 */

// ---------------------------------------------------------------------------
// Response envelopes
// ---------------------------------------------------------------------------

#[OA\Schema(
    schema: 'ErrorResponse',
    description: 'Standard error response body',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string',  example: 'Error message describing what went wrong'),
    ]
)]

// ---------------------------------------------------------------------------
// Master Data schemas
// ---------------------------------------------------------------------------

#[OA\Schema(
    schema: 'Country',
    description: 'ISO alpha-3 country',
    properties: [
        new OA\Property(property: 'code', type: 'string', maxLength: 3, example: 'IND',
            description: 'ISO 3166-1 alpha-3 country code'),
        new OA\Property(property: 'name', type: 'string', example: 'India'),
    ]
)]
#[OA\Schema(
    schema: 'City',
    description: 'City record',
    properties: [
        new OA\Property(property: 'id',           type: 'integer', example: 1),
        new OA\Property(property: 'name',         type: 'string',  example: 'Mumbai'),
        new OA\Property(property: 'country_code', type: 'string',  example: 'IND'),
    ]
)]
#[OA\Schema(
    schema: 'Location',
    description: 'Location / sub-city area',
    properties: [
        new OA\Property(property: 'id',       type: 'integer', example: 1),
        new OA\Property(property: 'location', type: 'string',  example: 'Andheri West'),
    ]
)]
#[OA\Schema(
    schema: 'ServiceChild',
    description: 'Sub-service / child service',
    properties: [
        new OA\Property(property: 'id',      type: 'integer', example: 5),
        new OA\Property(property: 'service', type: 'string',  example: 'Interventional Cardiology'),
    ]
)]
#[OA\Schema(
    schema: 'Service',
    description: 'Parent service with nested child services',
    properties: [
        new OA\Property(property: 'id',      type: 'integer', example: 1),
        new OA\Property(property: 'service', type: 'string',  example: 'Cardiology'),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ServiceChild')
        ),
    ]
)]
#[OA\Schema(
    schema: 'Qualification',
    description: 'Medical qualification',
    properties: [
        new OA\Property(property: 'id',            type: 'integer', example: 1),
        new OA\Property(property: 'qualification', type: 'string',  example: 'MBBS'),
    ]
)]
#[OA\Schema(
    schema: 'PublicSettings',
    description: 'Public platform settings — a flat key-value object',
    type: 'object',
    additionalProperties: new OA\AdditionalProperties(type: 'string'),
    example: ['site_name' => 'TatkalDoctor', 'support_email' => 'support@tatkaldoctor.com']
)]

// ---------------------------------------------------------------------------
// Listing schemas
// ---------------------------------------------------------------------------

#[OA\Schema(
    schema: 'ListingSearch',
    description: 'Doctor listing — compact search result item',
    properties: [
        new OA\Property(property: 'uuid',           type: 'string', format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'name',           type: 'string',  example: 'Dr. Ravi Kumar'),
        new OA\Property(property: 'hospital_name',  type: 'string',  nullable: true,
            example: 'City General Hospital'),
        new OA\Property(property: 'city',           type: 'string',  nullable: true,  example: 'Mumbai'),
        new OA\Property(property: 'location',       type: 'string',  nullable: true,  example: 'Andheri'),
        new OA\Property(property: 'average_rating', type: 'number',  format: 'float', example: 4.8),
    ]
)]
#[OA\Schema(
    schema: 'ListingDetail',
    description: 'Full doctor listing profile',
    properties: [
        new OA\Property(property: 'uuid',                type: 'string', format: 'uuid',
            example: '550e8400-e29b-41d4-a716-446655440000'),
        new OA\Property(property: 'name',                type: 'string',  example: 'Dr. Ravi Kumar'),
        new OA\Property(property: 'hospital_name',       type: 'string',  nullable: true,
            example: 'City General Hospital'),
        new OA\Property(property: 'address',             type: 'string',  nullable: true,
            example: '123 MG Road, Mumbai'),
        new OA\Property(property: 'description',         type: 'string',  nullable: true,
            example: 'Experienced cardiologist with 15+ years practice.'),
        new OA\Property(property: 'country',             type: 'string',  nullable: true,  example: 'India'),
        new OA\Property(property: 'city',                type: 'string',  nullable: true,  example: 'Mumbai'),
        new OA\Property(property: 'location',            type: 'string',  nullable: true,  example: 'Andheri'),
        new OA\Property(property: 'personal_contact_no', type: 'string',  nullable: true,
            example: '+91 9000000000'),
        new OA\Property(property: 'appointment_no',      type: 'string',  nullable: true,
            example: '+91 9000000001'),
        new OA\Property(property: 'qualifications',      type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['MBBS', 'MD — Cardiology']),
        new OA\Property(property: 'services',            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['Cardiology', 'Interventional Cardiology']),
        new OA\Property(property: 'latitude',            type: 'number',  format: 'float', nullable: true,
            example: 19.076),
        new OA\Property(property: 'longitude',           type: 'number',  format: 'float', nullable: true,
            example: 72.8777),
        new OA\Property(property: 'average_rating',      type: 'number',  format: 'float', example: 4.8),
        new OA\Property(property: 'status',              type: 'boolean', example: true),
        new OA\Property(property: 'qr_slug',             type: 'string',  nullable: true,
            example: 'dr-ravi-kumar-550e8400'),
        new OA\Property(property: 'public_profile_url',  type: 'string',  nullable: true, format: 'uri',
            example: 'https://tatkaldoctors.com/d/dr-ravi-kumar-550e8400'),
    ]
)]
#[OA\Schema(
    schema: 'Pagination',
    description: 'Pagination metadata',
    properties: [
        new OA\Property(property: 'current_page', type: 'integer', example: 1),
        new OA\Property(property: 'total',        type: 'integer', example: 100),
        new OA\Property(property: 'per_page',     type: 'integer', example: 20),
    ]
)]
class Schemas {}
