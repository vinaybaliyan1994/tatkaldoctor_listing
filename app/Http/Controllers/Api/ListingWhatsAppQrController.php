<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListingWhatsAppQrController extends Controller
{
    /**
     * POST /api/v1/listings/{uuid}/whatsapp-qr
     *
     * Ensures the listing has a qr_slug (generates one if absent) and
     * refreshes qr_generated_at. Returns the slug so the caller can build
     * the wa.me deep-link. QR image is rendered client-side from that link.
     */
    public function generate(string $uuid, Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'QR generation is restricted to the doctor_listing admin Generate QR action.',
        ], 403);
    }
}
