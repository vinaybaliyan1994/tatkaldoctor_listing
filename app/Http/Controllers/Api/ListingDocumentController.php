<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorDocument;
use App\Models\Listing;
use App\Services\ListingAuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ListingDocumentController extends Controller
{
    public function __construct(private ListingAuditService $audit) {}

    public function index(string $uuid): JsonResponse
    {
        $listing = Listing::where('uuid', $uuid)->first();

        if (! $listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        $documents = $listing->doctorDocuments()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (DoctorDocument $d) => [
                'id'            => $d->id,
                'document_type' => $d->document_type,
                'original_name' => $d->original_name,
                'mime_type'     => $d->mime_type,
                'file_size'     => $d->file_size,
                'status'        => $d->status,
                'remarks'       => $d->remarks,
                'created_at'    => $d->created_at->toDateTimeString(),
            ]);

        return response()->json(['success' => true, 'data' => $documents]);
    }

    public function store(Request $request, string $uuid): JsonResponse
    {
        $listing = Listing::where('uuid', $uuid)->first();

        if (! $listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        $request->validate([
            'document_type' => ['required', Rule::in(array_keys(DoctorDocument::DOCUMENT_TYPES))],
            'document'      => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        Log::channel('doctor_listing')->info('document_upload.started', [
            'listing_uuid' => $listing->uuid,
            'document_type' => $request->input('document_type'),
        ]);

        $file = $request->file('document');
        $ext  = $file->getClientOriginalExtension();
        $path = "doctor-documents/{$listing->id}/" . time() . '_' . Str::uuid() . '.' . $ext;

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        $document = DoctorDocument::create([
            'listing_id'    => $listing->id,
            'document_type' => $request->input('document_type'),
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'status'        => 'pending',
        ]);

        Log::channel('doctor_listing')->info('document_upload.success', [
            'listing_uuid' => $listing->uuid,
            'document_id' => $document->id,
            'document_type' => $document->document_type,
        ]);

        $this->audit->log(
            $listing->id,
            'document_uploaded_via_api',
            null,
            ['document_type' => $request->input('document_type')]
        );

        return response()->json(['success' => true, 'message' => 'Document uploaded. Pending review.'], 201);
    }

    public function updateProfilePhoto(Request $request, string $uuid): JsonResponse
    {
        $listing = Listing::where('uuid', $uuid)->first();

        if (! $listing) {
            return response()->json(['success' => false, 'message' => 'Listing not found.'], 404);
        }

        $request->validate([
            'photo' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png'],
        ]);

        $file = $request->file('photo');
        $ext  = $file->getClientOriginalExtension();
        $path = "listing-profile-photos/{$listing->id}/" . time() . '_' . Str::uuid() . '.' . $ext;

        if ($listing->profile_photo_path) {
            Storage::disk('public')->delete($listing->profile_photo_path);
        }

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        $oldPath = $listing->profile_photo_path;
        $listing->update(['profile_photo_path' => $path]);

        $this->audit->log(
            $listing->id,
            'profile_photo_updated_via_api',
            ['profile_photo_path' => $oldPath],
            ['profile_photo_path' => $path]
        );

        return response()->json([
            'success'           => true,
            'message'           => 'Profile photo updated.',
            'profile_photo_url' => Storage::disk('public')->url($path),
        ]);
    }
}
