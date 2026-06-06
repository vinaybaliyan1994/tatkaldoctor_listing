<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/v1/health',
        operationId: 'healthCheck',
        summary: 'Health check',
        description: 'Returns the current health status of the API. No authentication required.',
        tags: ['System'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success',   type: 'boolean', example: true),
                        new OA\Property(property: 'status',    type: 'string',  example: 'ok'),
                        new OA\Property(property: 'service',   type: 'string',  example: 'TatkalDoctor'),
                        new OA\Property(property: 'timestamp', type: 'string',  format: 'date-time', example: '2026-06-05T17:00:00+00:00'),
                    ]
                )
            ),
        ]
    )]
    public function check(): JsonResponse
    {
        return response()->json([
            'success'   => true,
            'status'    => 'ok',
            'service'   => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
