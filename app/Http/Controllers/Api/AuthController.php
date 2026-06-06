<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

// Global OpenAPI metadata (OA\Info, OA\Server, OA\Tag, OA\SecurityScheme) lives in app/OpenApi/ApiInfo.php
class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/v1/login',
        operationId: 'adminLogin',
        summary: 'Admin login',
        description: 'Authenticates a super_admin, admin, or user and returns a Sanctum Bearer token.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email',    type: 'string', format: 'email',    example: 'superadmin@tatkaldoctor.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'Admin@1234'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string',  example: 'Login successful.'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'user',
                                    type: 'object',
                                    properties: [
                                        new OA\Property(property: 'id',    type: 'integer', example: 1),
                                        new OA\Property(property: 'name',  type: 'string',  example: 'Super Admin'),
                                        new OA\Property(property: 'email', type: 'string',  example: 'superadmin@tatkaldoctor.com'),
                                        new OA\Property(property: 'role',  type: 'string',  example: 'super_admin',
                                            enum: ['super_admin', 'admin', 'user']),
                                    ]
                                ),
                                new OA\Property(property: 'token',      type: 'string', example: '1|abcxyz...'),
                                new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Account inactive',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Your account is inactive.'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials / validation error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string',  example: 'Validation failed.'),
                        new OA\Property(
                            property: 'errors',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'email', type: 'array',
                                    items: new OA\Items(type: 'string', example: 'The provided credentials are incorrect.')),
                            ]
                        ),
                    ]
                )
            ),
        ]
    )]
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json(['success' => false, 'message' => 'Your account is inactive.'], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'user'  => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $user->role,
                ],
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}
