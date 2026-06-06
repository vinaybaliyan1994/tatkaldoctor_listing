<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DoctorDocument;
use App\Models\Listing;
use App\Services\ListingAuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorDocumentController extends Controller
{
    public function __construct(private readonly ListingAuditService $audit) {}

    public function index(Listing $listing): View
    {
        $documents = $listing->doctorDocuments()
            ->with('verifiedBy')
            ->latest()
            ->get();

        return view('doctor-documents.index', compact('listing', 'documents'));
    }

    public function create(Listing $listing): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        return view('doctor-documents.create', [
            'listing'       => $listing,
            'documentTypes' => DoctorDocument::DOCUMENT_TYPES,
        ]);
    }

    public function store(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $request->validate([
            'document_type' => 'required|in:' . implode(',', array_keys(DoctorDocument::DOCUMENT_TYPES)),
            'document'      => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'remarks'       => 'nullable|string|max:500',
        ]);

        $file     = $request->file('document');
        $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs("doctor-documents/{$listing->id}", $filename, 'public');

        $document = $listing->doctorDocuments()->create([
            'document_type' => $validated['document_type'],
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'status'        => 'pending',
            'remarks'       => $validated['remarks'] ?? null,
        ]);

        $this->audit->log(
            $listing->id,
            'document_uploaded',
            null,
            ['document_type' => $document->document_type, 'document_id' => $document->id],
            "Uploaded {$document->document_type_label}"
        );

        return redirect()->route('doctor-documents.index', $listing)
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(DoctorDocument $doctorDocument): View
    {
        $doctorDocument->load(['listing', 'verifiedBy']);

        return view('doctor-documents.show', compact('doctorDocument'));
    }

    public function verify(DoctorDocument $doctorDocument): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $doctorDocument->load(['listing', 'verifiedBy']);

        return view('doctor-documents.verify', compact('doctorDocument'));
    }

    public function updateStatus(Request $request, DoctorDocument $doctorDocument): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $validated = $request->validate([
            'status'  => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|required_if:status,rejected|max:500',
        ]);

        $oldStatus = $doctorDocument->status;
        $newStatus = $validated['status'];

        if ($newStatus === 'approved') {
            $doctorDocument->verified_at = now();
            $doctorDocument->verified_by = Auth::id();
            $doctorDocument->remarks     = $validated['remarks'] ?? null;
        } elseif ($newStatus === 'rejected') {
            $doctorDocument->verified_at = null;
            $doctorDocument->verified_by = null;
            $doctorDocument->remarks     = $validated['remarks'];
        } else {
            $doctorDocument->verified_at = null;
            $doctorDocument->verified_by = null;
            $doctorDocument->remarks     = $validated['remarks'] ?? null;
        }

        $doctorDocument->status = $newStatus;
        $doctorDocument->save();

        $action = $newStatus === 'approved' ? 'document_verified' : 'document_rejected';
        if ($newStatus === 'pending') {
            $action = 'document_updated';
        }

        $this->audit->log(
            $doctorDocument->listing_id,
            $action,
            ['status' => $oldStatus],
            ['status' => $newStatus, 'document_id' => $doctorDocument->id],
            $validated['remarks'] ?? null
        );

        return redirect()->route('doctor-documents.show', $doctorDocument)
            ->with('success', 'Document status updated.');
    }

    public function download(DoctorDocument $doctorDocument): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($doctorDocument->file_path), 404);

        return Storage::disk('public')->download(
            $doctorDocument->file_path,
            $doctorDocument->original_name ?? basename($doctorDocument->file_path)
        );
    }

    public function destroy(DoctorDocument $doctorDocument): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $listingId = $doctorDocument->listing_id;

        if (Storage::disk('public')->exists($doctorDocument->file_path)) {
            Storage::disk('public')->delete($doctorDocument->file_path);
        }

        $this->audit->log(
            $listingId,
            'document_deleted',
            ['document_type' => $doctorDocument->document_type, 'document_id' => $doctorDocument->id],
            null
        );

        $doctorDocument->delete();

        return redirect()->route('doctor-documents.index', $listingId)
            ->with('success', 'Document deleted.');
    }
}
