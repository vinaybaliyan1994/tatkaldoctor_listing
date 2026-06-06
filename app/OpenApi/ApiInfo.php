<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'TatkalDoctor Doctor Listing API',
    version: '1.0.0',
    description: "Core registry API for the TatkalDoctor ecosystem.\n\n"
        . "## Authentication\n\n"
        . "### Public Endpoints\n"
        . "`GET /api/v1/health` is public — no authentication required.\n\n"
        . "### Admin Login (Sanctum)\n"
        . "Call `POST /api/v1/login` with email + password to receive a Bearer token.\n\n"
        . "### HMAC-SHA256 (All Data Endpoints)\n"
        . "All master data and listing endpoints require four request headers:\n\n"
        . "| Header | Description |\n"
        . "|---|---|\n"
        . "| `X-Api-Key` | Client API key issued by super admin |\n"
        . "| `X-Timestamp` | Unix timestamp in seconds (UTC) |\n"
        . "| `X-Nonce` | Random string ≥ 8 chars, unique per request |\n"
        . "| `X-Signature` | HMAC-SHA256 hex-encoded signature |\n\n"
        . "**String to sign** (fields joined by `\\n`):\n\n"
        . "```\nHTTP_METHOD\nTIMESTAMP\nNONCE\nREQUEST_PATH\nSHA256(body)\n```\n\n"
        . "> Timestamp must be within **±5 minutes** of server time. "
        . "The `REQUEST_PATH` is the URL path only — no query string. "
        . "For GET requests, `SHA256(body)` is the SHA-256 of an empty string.",
    contact: new OA\Contact(name: 'TatkalDoctor Admin', email: 'superadmin@tatkaldoctor.com')
)]
#[OA\Server(url: 'http://127.0.0.1:8000', description: 'Local development server (WAMP)')]
#[OA\Tag(
    name: 'System',
    description: 'Health check and status endpoints — no authentication required'
)]
#[OA\Tag(
    name: 'Auth',
    description: 'Admin authentication — returns Sanctum Bearer token'
)]
#[OA\Tag(
    name: 'Master Data',
    description: 'Countries, cities, locations, services and qualifications — HMAC protected'
)]
#[OA\Tag(
    name: 'Settings',
    description: 'Public platform settings key-value pairs — HMAC protected'
)]
#[OA\Tag(
    name: 'Listings',
    description: 'Doctor listing search and detail profile endpoints — HMAC protected'
)]
#[OA\SecurityScheme(
    securityScheme: 'HmacApiKey',
    type: 'apiKey',
    in: 'header',
    name: 'X-Api-Key',
    description: 'Client API key issued by the super admin. Identifies the calling client application.'
)]
#[OA\SecurityScheme(
    securityScheme: 'HmacTimestamp',
    type: 'apiKey',
    in: 'header',
    name: 'X-Timestamp',
    description: 'Unix timestamp in seconds (UTC). Must be within ±5 minutes of server time.'
)]
#[OA\SecurityScheme(
    securityScheme: 'HmacNonce',
    type: 'apiKey',
    in: 'header',
    name: 'X-Nonce',
    description: 'Random string, minimum 8 characters. Must be unique per request to prevent replay attacks.'
)]
#[OA\SecurityScheme(
    securityScheme: 'HmacSignature',
    type: 'apiKey',
    in: 'header',
    name: 'X-Signature',
    description: "HMAC-SHA256 hex-encoded signature.\n\nString to sign (\\n separated):\nHTTP_METHOD\\nTIMESTAMP\\nNONCE\\nREQUEST_PATH\\nSHA256(body)"
)]
class ApiInfo {}
