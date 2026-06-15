<?php

namespace App\Services;

use App\Models\Listing;
use App\Models\MasterQualification;
use App\Models\MasterService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SolutionSyncService
{
    private string $apiUrl;
    private string $serviceToken;
    private string $syncUrl;
    private string $syncToken;

    public function __construct()
    {
        $this->apiUrl       = rtrim((string) config('services.solution.api_url'), '/');
        $this->serviceToken = (string) config('services.solution.service_token');
        $this->syncUrl   = rtrim((string) config('services.solution.sync_url'), '/');
        $this->syncToken = (string) config('services.solution.sync_token');

        if ($this->apiUrl !== '' && !str_ends_with($this->apiUrl, '/api/v1')) {
            $this->apiUrl .= '/api/v1';
        }
    }

    public function syncListing(Listing $listing, string $event = 'listing.saved'): void
    {
        if (!$this->serviceToken || !$this->apiUrl) {
            Log::channel('doctor_listing')->warning('solution_sync.failed', [
                'listing_id' => $listing->id,
                'listing_uuid' => $listing->uuid,
                'event' => $event,
                'reason' => 'solution_api_url_or_service_token_not_configured',
            ]);
            return;
        }

        $listing->loadMissing(['country', 'city', 'location']);
        $endpoint = $this->apiUrl . '/integration/listing-cache-sync';
        $payload = $this->buildListingPayload($listing);

        Log::channel('doctor_listing')->info('solution_sync.started', [
            'listing_id' => $listing->id,
            'listing_uuid' => $listing->uuid,
            'event' => $event,
        ]);

        try {
            $response = Http::timeout(10)
                ->retry(3, 1000, fn (\Throwable $e) => $e instanceof \Illuminate\Http\Client\ConnectionException)
                ->withHeaders([
                    'X-Service-Token' => $this->serviceToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($endpoint, $payload);

            $safeBody = $this->safeResponseBody($response->json() ?? $response->body());
            $logData = [
                'listing_id' => $listing->id,
                'listing_uuid' => $listing->uuid,
                'event' => $event,
                'response_status' => $response->status(),
                'response_body' => $safeBody,
            ];

            if ($response->successful()) {
                Log::channel('doctor_listing')->info('solution_sync.success', $logData);
            } else {
                Log::channel('doctor_listing')->warning('solution_sync.failed', $logData);
            }
        } catch (\Throwable $e) {
            Log::channel('doctor_listing')->error('solution_sync.failed', [
                'listing_id' => $listing->id,
                'listing_uuid' => $listing->uuid,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function syncVerificationStatus(
        string  $listingUuid,
        string  $verificationStatus,
        ?string $remarks    = null,
        ?string $approvedAt = null,
        ?int    $approvedBy = null
    ): bool {
        if (!$this->syncToken || !$this->syncUrl) {
            Log::channel('doctor_listing')->warning('SolutionSyncService: sync_url or sync_token not configured — sync skipped', [
                'listing_uuid' => $listingUuid,
            ]);
            return false;
        }

        $endpoint = $this->syncUrl . '/api/v1/internal/listing-verification-sync';

        $payload = [
            'listing_uuid'        => $listingUuid,
            'verification_status' => $verificationStatus,
            'status'              => $this->statusValue($verificationStatus),
            'remarks'             => $remarks,
            'approved_at'         => $approvedAt,
            'approved_by'         => $approvedBy,
        ];

        $listing = Listing::where('uuid', $listingUuid)->first();
        if ($listing) {
            $payload['email'] = $listing->email;
            $payload['personal_contact_no'] = $listing->personal_contact_no;
            $payload['appointment_no'] = $listing->appointment_no;
            $payload['qr_slug'] = $listing->qr_slug;
            $payload['qr_code_url'] = $listing->qr_code_path ? Storage::disk('public')->url($listing->qr_code_path) : null;
            $payload['qr_generated_at'] = $listing->qr_generated_at?->toIso8601String();
        }

        Log::channel('doctor_listing')->info('solution_sync.started', [
            'listing_uuid'        => $listingUuid,
            'verification_status' => $verificationStatus,
            'solution_sync_url'   => $endpoint,
        ]);
        Log::channel('doctor_listing')->info('solution_sync.payload', array_merge($payload, [
            'qr_code_url' => isset($payload['qr_code_url']) ? (bool) $payload['qr_code_url'] : null,
        ]));

        try {
            $response = Http::timeout(10)
                ->retry(3, 1000, fn (\Throwable $e) => $e instanceof \Illuminate\Http\Client\ConnectionException)
                ->withHeaders(['X-Sync-Token' => $this->syncToken])
                ->post($endpoint, array_filter($payload, fn($v) => $v !== null));

            Log::channel('doctor_listing')->info('solution_sync.response', [
                'listing_uuid'   => $listingUuid,
                'response_status' => $response->status(),
                'response_body'   => $response->body(),
            ]);

            if ($response->successful()) {
                Log::channel('doctor_listing')->info('solution_sync.success', [
                    'listing_uuid' => $listingUuid,
                    'verification_status' => $verificationStatus,
                    'response_status' => $response->status(),
                ]);
                return true;
            }

            Log::channel('doctor_listing')->warning('solution_sync.failed', [
                'listing_uuid'    => $listingUuid,
                'response_status' => $response->status(),
                'response_body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::channel('doctor_listing')->error('SolutionSyncService: sync call exception', [
                'listing_uuid' => $listingUuid,
                'error'        => $e->getMessage(),
                'file'         => $e->getFile(),
                'line'         => $e->getLine(),
            ]);
            return false;
        }
    }

    private function buildListingPayload(Listing $listing): array
    {
        $profileData = [
            'uuid' => $listing->uuid,
            'name' => $listing->name,
            'hospital_name' => $listing->hospital_name,
            'email' => $listing->email,
            'personal_contact_no' => $listing->personal_contact_no,
            'appointment_contact_no' => $listing->appointment_no,
            'country' => $listing->country?->name,
            'country_code' => $listing->country_code,
            'city' => $listing->city?->name,
            'location' => $listing->location?->location,
            'address' => $listing->address,
            'services' => $this->services($listing),
            'qualifications' => $this->qualifications($listing),
            'profile_photo_url' => $listing->profile_photo_path
                ? Storage::url($listing->profile_photo_path)
                : null,
            'qr_slug' => $listing->qr_slug,
            'public_profile_url' => $listing->public_profile_url,
            'qr_code_url' => $listing->qr_code_path
                ? Storage::disk('public')->url($listing->qr_code_path)
                : null,
            'qr_generated_at' => $listing->qr_generated_at?->toIso8601String(),
            'verification_status' => $listing->verification_status,
            'status' => (bool) $listing->status,
            'description' => $listing->description,
            'latitude' => $listing->latitude === null ? null : (float) $listing->latitude,
            'longitude' => $listing->longitude === null ? null : (float) $listing->longitude,
        ];

        return [
            'listing_uuid' => $listing->uuid,
            'verification_status' => $listing->verification_status,
            'status' => (bool) $listing->status,
            'profile_data' => $profileData,
        ];
    }

    private function statusValue(string $verificationStatus): string
    {
        return $verificationStatus === 'approved' ? 'active' : 'inactive';
    }

    private function services(Listing $listing): array
    {
        $ids = array_values(array_filter((array) $listing->services));

        if ($ids === []) {
            return [];
        }

        return MasterService::whereIn('id', $ids)
            ->orderBy('service')
            ->get()
            ->map(fn (MasterService $service) => [
                'id' => $service->id,
                'name' => $service->service,
            ])
            ->values()
            ->toArray();
    }

    private function qualifications(Listing $listing): array
    {
        $ids = array_values(array_filter((array) $listing->qualifications));

        if ($ids === []) {
            return [];
        }

        return MasterQualification::whereIn('id', $ids)
            ->orderBy('qualification')
            ->get()
            ->map(fn (MasterQualification $qualification) => [
                'id' => $qualification->id,
                'name' => $qualification->qualification,
            ])
            ->values()
            ->toArray();
    }

    private function safeResponseBody(mixed $body): mixed
    {
        if (is_string($body)) {
            return mb_substr($body, 0, 1000);
        }

        if (!is_array($body)) {
            return $body;
        }

        unset($body['token'], $body['api_key'], $body['api_secret'], $body['secret']);

        return $body;
    }
}
