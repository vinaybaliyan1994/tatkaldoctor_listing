<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorDocument;
use App\Models\Listing;
use App\Services\ListingAuditService;
use App\Services\ListingRegistrationMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ListingIntakeController extends Controller
{
    public function __construct(
        private readonly ListingAuditService $audit,
        private readonly ListingRegistrationMatcher $matcher,
    ) {}

    /**
     * POST /api/v1/listings/intake
     *
     * Accepts a doctor profile submitted by Solution on behalf of a newly
     * registered doctor. Creates a Listing in `pending` state, invisible
     * to the public, awaiting super-admin approval.
     *
     * Accepts both old format (services as [int], city_id/location_id) and
     * new structured format (services as [{id,name}], city/location as {id,name}).
     *
     * Called by: solution.tatkaldoctor.com — DoctorProfileController
     * Auth: HMAC-SHA256
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'doctor_name'         => ['required', 'string', 'max:191'],
            'listing_uuid'        => ['nullable', 'uuid'],
            'uuid'                => ['nullable', 'uuid'],
            'email'               => ['nullable', 'email', 'max:191'],
            'mobile'              => ['nullable', 'string', 'max:20'],
            'clinic_name'         => ['nullable', 'string', 'max:191'],
            'address'             => ['nullable', 'string', 'max:500'],
            'country_code'        => ['nullable', 'string', 'size:3'],
            // New structured city/location (takes priority over old format)
            'city'                => ['nullable', 'array'],
            'city.id'             => ['nullable', 'integer'],
            'city.name'           => ['nullable', 'string', 'max:100'],
            'location'            => ['nullable', 'array'],
            'location.id'         => ['nullable', 'integer'],
            'location.name'       => ['nullable', 'string', 'max:100'],
            'master_city_id'      => ['nullable', 'integer'],
            'master_location_id'  => ['nullable', 'integer'],
            // Old format (backward compat)
            'city_id'             => ['nullable', 'integer'],
            'location_id'         => ['nullable', 'integer'],
            // Structured services/qualifications: accepts [{id,name}] or [int]
            'service_ids'         => ['nullable', 'array'],
            'service_ids.*'       => ['integer'],
            'qualification_ids'   => ['nullable', 'array'],
            'qualification_ids.*' => ['integer'],
            'services'            => ['nullable', 'array'],
            'qualifications'      => ['nullable', 'array'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            // Extended profile fields (stored in meta_data)
            'bio'                 => ['nullable', 'string', 'max:3000'],
            'experience_years'    => ['nullable', 'integer', 'min:0', 'max:70'],
            'consultation_fee'    => ['nullable', 'numeric', 'min:0'],
            // Text fallbacks (stored in meta_data for admin reference)
            'city_name'           => ['nullable', 'string', 'max:100'],
            'area_name'           => ['nullable', 'string', 'max:100'],
            'specialities_text'   => ['nullable', 'string', 'max:500'],
            'qualifications_text' => ['nullable', 'string', 'max:500'],
            // External photo URL (from solution storage — persisted in meta_data as fallback)
            'profile_photo_url'   => ['nullable', 'string', 'max:2048'],
        ]);

        [$serviceIds, $qualIds, $suggestedServices, $suggestedQuals] = $this->parseServicesQualifications($validated);

        // Resolve city/location: new structured format takes priority over old
        $cityId   = $validated['master_city_id'] ?? $validated['city']['id'] ?? $validated['city_id'] ?? null;
        $cityName = $validated['city']['name']     ?? $validated['city_name']    ?? null;
        $locId    = $validated['master_location_id'] ?? $validated['location']['id'] ?? $validated['location_id'] ?? null;
        $locName  = $validated['location']['name'] ?? $validated['area_name']    ?? null;

        $metaData = array_filter([
            'registration_no'          => $validated['registration_number'] ?? null,
            'experience_years'         => $validated['experience_years'] ?? null,
            'consultation_fee'         => $validated['consultation_fee'] ?? null,
            'city_name'                => $cityName,
            'area_name'                => $locName,
            'specialities_text'        => $validated['specialities_text'] ?? null,
            'qualifications_text'      => $validated['qualifications_text'] ?? null,
            // Suggested values: names submitted without a master ID
            'suggested_services'       => !empty($suggestedServices) ? $suggestedServices : null,
            'suggested_qualifications' => !empty($suggestedQuals) ? $suggestedQuals : null,
            'suggested_city'           => (!$cityId && $cityName) ? $cityName : null,
            'suggested_location'       => (!$locId && $locName) ? $locName : null,
            // External profile photo URL (from solution — used as fallback when file upload fails)
            'profile_photo_url'        => $validated['profile_photo_url'] ?? null,
        ], fn($v) => $v !== null);

        $payload = [
            'name'                => $validated['doctor_name'],
            'email'               => $validated['email'] ?? null,
            'personal_contact_no' => $validated['mobile'] ?? null,
            'hospital_name'       => $validated['clinic_name'] ?? null,
            'address'             => $validated['address'] ?? null,
            'description'         => $validated['bio'] ?? null,
            'country_code'        => strtoupper($validated['country_code'] ?? 'IND'),
            'master_city_id'      => $cityId,
            'master_location_id'  => $locId,
            'services'            => !empty($serviceIds) ? $serviceIds : null,
            'qualifications'      => !empty($qualIds) ? $qualIds : null,
            'meta_data'           => $metaData ?: null,
            'status'              => false,
            'verification_status' => 'pending',
            'source'              => 'solution_registration',
        ];

        $listing = $this->matcher->findExisting([
            'uuid'                => $validated['uuid'] ?? null,
            'listing_uuid'        => $validated['listing_uuid'] ?? null,
            'doctor_name'         => $validated['doctor_name'],
            'email'               => $validated['email'] ?? null,
            'mobile'              => $validated['mobile'] ?? null,
            'registration_number' => $validated['registration_number'] ?? null,
            'country_code'        => $payload['country_code'],
        ]);

        if ($listing) {
            $payload['meta_data'] = array_filter(
                array_merge($listing->meta_data ?? [], $metaData),
                fn ($value) => $value !== null
            );
            $listing->update(array_filter($payload, fn ($value) => $value !== null));
            $auditAction = 'intake_updated_via_api';
            $statusCode = 200;
        } else {
            $listing = Listing::create($payload);
            $auditAction = 'intake_submitted';
            $statusCode = 201;
        }

        $this->audit->log($listing->id, $auditAction, null, [
            'name'   => $listing->name,
            'email'  => $listing->email,
            'source' => $listing->source,
        ]);

        $storedDocuments = $this->storeSubmittedDocuments($request, $listing);

        return response()->json([
            'success' => true,
            'message' => 'Doctor registration intake received. Pending super admin review.',
            'data'    => [
                'listing_uuid'        => $listing->uuid,
                'qr_slug'             => $listing->qr_slug,
                'verification_status' => $listing->verification_status,
                'status'              => $listing->status,
                'documents_received'  => $storedDocuments,
            ],
        ], $statusCode);
    }

    /**
     * PATCH /api/v1/listings/{uuid}
     *
     * Updates an existing listing's profile data.
     * Called by Solution when re-submitting after rejection or updating
     * profile details before verification is complete.
     * Auth: HMAC-SHA256
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $listing = Listing::where('uuid', $uuid)->first();

        if (!$listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        $validated = $request->validate([
            'doctor_name'         => ['nullable', 'string', 'max:191'],
            'email'               => ['nullable', 'email', 'max:191'],
            'mobile'              => ['nullable', 'string', 'max:20'],
            'clinic_name'         => ['nullable', 'string', 'max:191'],
            'address'             => ['nullable', 'string', 'max:500'],
            'country_code'        => ['nullable', 'string', 'size:3'],
            // Structured city/location
            'city'                => ['nullable', 'array'],
            'city.id'             => ['nullable', 'integer'],
            'city.name'           => ['nullable', 'string', 'max:100'],
            'location'            => ['nullable', 'array'],
            'location.id'         => ['nullable', 'integer'],
            'location.name'       => ['nullable', 'string', 'max:100'],
            'master_city_id'      => ['nullable', 'integer'],
            'master_location_id'  => ['nullable', 'integer'],
            // Old format
            'city_id'             => ['nullable', 'integer'],
            'location_id'         => ['nullable', 'integer'],
            // Structured services/qualifications
            'service_ids'         => ['nullable', 'array'],
            'service_ids.*'       => ['integer'],
            'qualification_ids'   => ['nullable', 'array'],
            'qualification_ids.*' => ['integer'],
            'services'            => ['nullable', 'array'],
            'qualifications'      => ['nullable', 'array'],
            // Profile fields
            'registration_number' => ['nullable', 'string', 'max:100'],
            'bio'                 => ['nullable', 'string', 'max:3000'],
            'experience_years'    => ['nullable', 'integer', 'min:0', 'max:70'],
            'consultation_fee'    => ['nullable', 'numeric', 'min:0'],
            // Text fallbacks
            'city_name'           => ['nullable', 'string', 'max:100'],
            'area_name'           => ['nullable', 'string', 'max:100'],
            'specialities_text'   => ['nullable', 'string', 'max:500'],
            'qualifications_text' => ['nullable', 'string', 'max:500'],
            // External photo URL
            'profile_photo_url'   => ['nullable', 'string', 'max:2048'],
        ]);

        // Parse services/qualifications
        [$serviceIds, $qualIds, $suggestedServices, $suggestedQuals] = $this->parseServicesQualifications($validated);

        // Resolve city/location
        $cityId   = $validated['master_city_id'] ?? $validated['city']['id'] ?? $validated['city_id'] ?? null;
        $cityName = $validated['city']['name']     ?? $validated['city_name']    ?? null;
        $locId    = $validated['master_location_id'] ?? $validated['location']['id'] ?? $validated['location_id'] ?? null;
        $locName  = $validated['location']['name'] ?? $validated['area_name']    ?? null;

        $newMeta = array_filter([
            'registration_no'          => $validated['registration_number'] ?? null,
            'experience_years'         => $validated['experience_years'] ?? null,
            'consultation_fee'         => $validated['consultation_fee'] ?? null,
            'city_name'                => $cityName,
            'area_name'                => $locName,
            'specialities_text'        => $validated['specialities_text'] ?? null,
            'qualifications_text'      => $validated['qualifications_text'] ?? null,
            'suggested_services'       => !empty($suggestedServices) ? $suggestedServices : null,
            'suggested_qualifications' => !empty($suggestedQuals) ? $suggestedQuals : null,
            'suggested_city'           => (!$cityId && $cityName) ? $cityName : null,
            'suggested_location'       => (!$locId && $locName) ? $locName : null,
            'profile_photo_url'        => $validated['profile_photo_url'] ?? null,
        ], fn($v) => $v !== null);

        $updates = array_filter([
            'name'                => $validated['doctor_name'] ?? null,
            'email'               => $validated['email'] ?? null,
            'personal_contact_no' => $validated['mobile'] ?? null,
            'hospital_name'       => $validated['clinic_name'] ?? null,
            'address'             => $validated['address'] ?? null,
            'description'         => $validated['bio'] ?? null,
            'country_code'        => isset($validated['country_code']) ? strtoupper($validated['country_code']) : null,
            'meta_data'           => !empty($newMeta) ? array_merge($listing->meta_data ?? [], $newMeta) : null,
        ], fn($v) => $v !== null);

        // Update city/location if provided
        if (array_key_exists('city', $validated) || array_key_exists('city_id', $validated) || array_key_exists('master_city_id', $validated)) {
            $updates['master_city_id'] = $cityId;
        }
        if (array_key_exists('location', $validated) || array_key_exists('location_id', $validated) || array_key_exists('master_location_id', $validated)) {
            $updates['master_location_id'] = $locId;
        }

        // Update services/qualifications if provided
        if (array_key_exists('services', $validated) || array_key_exists('service_ids', $validated)) {
            $updates['services'] = !empty($serviceIds) ? $serviceIds : null;
        }
        if (array_key_exists('qualifications', $validated) || array_key_exists('qualification_ids', $validated)) {
            $updates['qualifications'] = !empty($qualIds) ? $qualIds : null;
        }

        if (!empty($updates)) {
            $listing->update($updates);
        }

        $this->audit->log($listing->id, 'intake_updated_via_api', null, [
            'updated_fields' => array_keys($updates),
        ]);

        $storedDocuments = $this->storeSubmittedDocuments($request, $listing);

        return response()->json([
            'success' => true,
            'message' => 'Listing profile updated.',
            'data'    => [
                'listing_uuid'        => $listing->uuid,
                'verification_status' => $listing->verification_status,
                'documents_received'  => $storedDocuments,
            ],
        ]);
    }

    /**
     * Parse services and qualifications from the request payload.
     * Accepts both [{id, name}] (new structured format) and [int] (old format).
     * Returns [serviceIds, qualIds, suggestedServices, suggestedQuals].
     */
    private function parseServicesQualifications(array $validated): array
    {
        $serviceIds       = [];
        $suggestedServices = [];

        foreach ($validated['service_ids'] ?? [] as $id) {
            if (is_numeric($id)) {
                $serviceIds[] = (int) $id;
            }
        }

        foreach ($validated['services'] ?? [] as $svc) {
            if (is_array($svc)) {
                if (!empty($svc['id'])) {
                    $serviceIds[] = (int) $svc['id'];
                } elseif (!empty($svc['name'])) {
                    $suggestedServices[] = trim($svc['name']);
                }
            } elseif (is_numeric($svc)) {
                $serviceIds[] = (int) $svc;
            }
        }

        $qualIds       = [];
        $suggestedQuals = [];

        foreach ($validated['qualification_ids'] ?? [] as $id) {
            if (is_numeric($id)) {
                $qualIds[] = (int) $id;
            }
        }

        foreach ($validated['qualifications'] ?? [] as $q) {
            if (is_array($q)) {
                if (!empty($q['id'])) {
                    $qualIds[] = (int) $q['id'];
                } elseif (!empty($q['name'])) {
                    $suggestedQuals[] = trim($q['name']);
                }
            } elseif (is_numeric($q)) {
                $qualIds[] = (int) $q;
            }
        }

        return [
            array_values(array_unique($serviceIds)),
            array_values(array_unique($qualIds)),
            array_values(array_unique($suggestedServices)),
            array_values(array_unique($suggestedQuals)),
        ];
    }

    private function storeSubmittedDocuments(Request $request, Listing $listing): int
    {
        $uploads = $this->collectDocumentUploads($request);
        $stored = 0;

        foreach ($uploads as $upload) {
            $file = $upload['file'];
            $documentType = $upload['document_type'];

            if (! $file->isValid()) {
                Log::channel('doctor_listing')->warning('intake_document_upload.invalid_file', [
                    'listing_uuid' => $listing->uuid,
                    'document_type' => $documentType,
                    'original_name' => $file->getClientOriginalName(),
                ]);
                continue;
            }

            if ($file->getSize() > 5 * 1024 * 1024) {
                Log::channel('doctor_listing')->warning('intake_document_upload.too_large', [
                    'listing_uuid' => $listing->uuid,
                    'document_type' => $documentType,
                    'original_name' => $file->getClientOriginalName(),
                ]);
                continue;
            }

            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                Log::channel('doctor_listing')->warning('intake_document_upload.unsupported_extension', [
                    'listing_uuid' => $listing->uuid,
                    'document_type' => $documentType,
                    'original_name' => $file->getClientOriginalName(),
                    'extension' => $extension,
                ]);
                continue;
            }

            $alreadyExists = $listing->documents()
                ->where('document_type', $documentType)
                ->where('original_name', $file->getClientOriginalName())
                ->where('file_size', $file->getSize())
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $filename = time() . '_' . Str::uuid() . '.' . $extension;
            $path = $file->storeAs("doctor-documents/{$listing->id}", $filename, 'public');

            $document = $listing->documents()->create([
                'document_type' => $documentType,
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);

            $this->audit->log(
                $listing->id,
                'document_uploaded_via_intake',
                null,
                ['document_type' => $documentType, 'document_id' => $document->id]
            );

            Log::channel('doctor_listing')->info('intake_document_upload.success', [
                'listing_uuid' => $listing->uuid,
                'document_id' => $document->id,
                'document_type' => $documentType,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @return array<int, array{document_type: string, file: UploadedFile}>
     */
    private function collectDocumentUploads(Request $request): array
    {
        $uploads = [];

        foreach ($this->flattenFiles($request->allFiles()) as $path => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($this->isProfilePhotoField($path)) {
                continue;
            }

            $documentType = $this->documentTypeForFilePath($request, $path);
            if (! $documentType) {
                Log::channel('doctor_listing')->warning('intake_document_upload.unknown_type', [
                    'field' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
                continue;
            }

            $uploads[] = [
                'document_type' => $documentType,
                'file' => $file,
            ];
        }

        return $uploads;
    }

    /**
     * @return array<string, UploadedFile>
     */
    private function flattenFiles(array $files, string $prefix = ''): array
    {
        $flat = [];

        foreach ($files as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if ($value instanceof UploadedFile) {
                $flat[$path] = $value;
                continue;
            }

            if (is_array($value)) {
                $flat += $this->flattenFiles($value, $path);
            }
        }

        return $flat;
    }

    private function documentTypeForFilePath(Request $request, string $path): ?string
    {
        $segments = explode('.', $path);

        $candidatePaths = [
            preg_replace('/\.(file|document|upload)$/', '.document_type', $path),
            preg_replace('/\.(file|document|upload)$/', '.type', $path),
            preg_replace('/\.(file|document|upload)$/', '', $path) . '.document_type',
            'document_type',
        ];

        foreach ($candidatePaths as $candidatePath) {
            $value = data_get($request->all(), $candidatePath);
            if (is_string($value) && $type = $this->normalizeDocumentType($value)) {
                return $type;
            }
        }

        foreach (array_reverse($segments) as $segment) {
            if ($type = $this->normalizeDocumentType($segment)) {
                return $type;
            }
        }

        return null;
    }

    private function normalizeDocumentType(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = Str::of($value)->lower()->replace(['-', ' '], '_')->replaceMatches('/_file$|_document$|_upload$/', '')->toString();

        $aliases = [
            'aadhar' => 'aadhaar',
            'aadhaar_card' => 'aadhaar',
            'pan_card' => 'pan',
            'medical_registration_certificate' => 'medical_registration',
            'registration_certificate' => 'medical_registration',
            'registration_number' => 'medical_registration',
            'registration' => 'medical_registration',
            'degree' => 'degree_certificate',
            'qualification_certificate' => 'degree_certificate',
            'clinic' => 'clinic_license',
            'license' => 'clinic_license',
        ];

        $value = $aliases[$value] ?? $value;

        return array_key_exists($value, DoctorDocument::DOCUMENT_TYPES) ? $value : null;
    }

    private function isProfilePhotoField(string $path): bool
    {
        return Str::of($path)->lower()->contains(['photo', 'profile_photo', 'profile_image', 'avatar']);
    }
}
