<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use App\Models\Client;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class HmacAuthentication
{
    private const TIMESTAMP_TOLERANCE = 300;

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey    = $request->header('X-Api-Key');
        $timestamp = $request->header('X-Timestamp');
        $nonce     = $request->header('X-Nonce');
        $signature = $request->header('X-Signature');

        if (! $apiKey || ! $timestamp || ! $nonce || ! $signature) {
            return $this->unauthorized($request, null, $apiKey, 'Missing required HMAC headers: X-Api-Key, X-Timestamp, X-Nonce, X-Signature.');
        }

        if (abs(time() - (int) $timestamp) > self::TIMESTAMP_TOLERANCE) {
            return $this->unauthorized($request, null, $apiKey, 'Request timestamp is outside the acceptable window.');
        }

        if (strlen($nonce) < 8) {
            return $this->unauthorized($request, null, $apiKey, 'Nonce must be at least 8 characters.');
        }

        $nonceCacheKey = 'hmac_nonce:'.hash('sha256', $apiKey.'|'.$nonce);

        if (! Cache::add($nonceCacheKey, true, self::TIMESTAMP_TOLERANCE)) {
            return $this->unauthorized($request, null, $apiKey, 'Nonce has already been used.');
        }

        $client = Client::where('api_key', $apiKey)->first();

        if (! $client) {
            return $this->unauthorized($request, null, $apiKey, 'Invalid API key.');
        }

        if (! $client->isActive()) {
            return $this->unauthorized($request, $client, $apiKey, 'Client is inactive or outside availability period.');
        }

        $rateLimitResult = $this->checkRateLimit($client);
        if ($rateLimitResult !== null) {
            return $rateLimitResult;
        }

        $contentType  = $request->header('Content-Type', '');
        $rawBody      = str_starts_with($contentType, 'multipart/') ? '' : $request->getContent();
        $bodyHash     = hash('sha256', $rawBody);
        $stringToSign = implode("\n", [
            strtoupper($request->method()),
            $timestamp,
            $nonce,
            $request->getPathInfo(),
            $bodyHash,
        ]);

        $secretKey         = $client->getDecryptedSecretKey();
        $expectedSignature = hash_hmac('sha256', $stringToSign, $secretKey);

        if (! hash_equals($expectedSignature, strtolower($signature))) {
            return $this->unauthorized($request, $client, $apiKey, 'Invalid HMAC signature.');
        }

        $request->attributes->set('hmac_client', $client);

        $response = $next($request);

        $this->logRequest(
            $request,
            $client,
            $apiKey,
            $response->getStatusCode(),
            $response->getStatusCode() < 400,
            $response->getStatusCode() < 400 ? null : 'Request completed with error response.'
        );

        return $response;
    }

    private function checkRateLimit(Client $client): ?Response
    {
        $activeSub = $client->activeSubscription();
        if (! $activeSub) {
            return null;
        }

        $plan = $activeSub->plan;
        if (! $plan) {
            return null;
        }

        // max_appointments is the monthly API request quota for this client plan
        $maxRequests = $plan->max_appointments;
        if ($maxRequests === null) {
            return null; // no limit
        }

        $monthStart = Carbon::now()->startOfMonth()->toDateTimeString();
        $cacheKey   = 'hmac_rate:' . $client->id . ':' . Carbon::now()->format('Y-m');

        $count = Cache::remember($cacheKey, 60, fn () =>
            ApiLog::where('client_id', $client->id)
                ->where('created_at', '>=', $monthStart)
                ->where('success', true)
                ->count()
        );

        if ($count >= $maxRequests) {
            $this->logRequest(request(), $client, $client->api_key, 429, false, 'Monthly request quota exceeded.');
            return response()->json([
                'success' => false,
                'message' => 'Monthly API request quota exceeded. Please upgrade your plan.',
                'quota'   => ['used' => $count, 'max' => $maxRequests],
            ], 429);
        }

        return null;
    }

    private function unauthorized(Request $request, ?Client $client, ?string $apiKey, string $message): Response
    {
        $response = response()->json(['success' => false, 'message' => $message], 401);

        $this->logRequest($request, $client, $apiKey, 401, false, $message);

        return $response;
    }

    private function logRequest(
        Request $request,
        ?Client $client,
        ?string $apiKey,
        int $responseStatus,
        bool $success,
        ?string $errorMessage
    ): void {
        try {
            ApiLog::create([
                'client_id'        => $client?->id,
                'api_key'          => $apiKey,
                'endpoint'         => $request->getPathInfo(),
                'method'           => strtoupper($request->method()),
                'request_ip'       => $request->ip(),
                'request_headers'  => $this->headersForLog($request),
                'response_status'  => $responseStatus,
                'success'          => $success,
                'error_message'    => $errorMessage,
            ]);
        } catch (\Throwable) {
            // Logging must never block an API response.
        }
    }

    private function headersForLog(Request $request): array
    {
        $headers = $request->headers->all();

        if (isset($headers['x-signature'])) {
            $headers['x-signature'] = ['[masked]'];
        }

        return $headers;
    }
}
