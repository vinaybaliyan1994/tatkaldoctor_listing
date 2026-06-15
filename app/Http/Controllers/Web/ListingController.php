<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use App\Models\MasterLocation;
use App\Models\MasterQualification;
use App\Models\MasterService;
use App\Services\ListingAuditService;
use App\Services\ListingQrService;
use App\Services\SolutionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function __construct(
        private readonly ListingAuditService  $audit,
        private readonly ListingQrService     $qr,
        private readonly SolutionSyncService  $solutionSync,
    ) {}

    public function index(Request $request): View
    {
        $query = Listing::with(['country', 'city', 'location'])
            ->withCount([
                'documents',
                'documents as pending_documents_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->orderByDesc('id');

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->country_code);
        }
        if ($request->filled('master_city_id')) {
            $query->where('master_city_id', $request->master_city_id);
        }
        if ($request->filled('master_location_id')) {
            $query->where('master_location_id', $request->master_location_id);
        }
        if ($request->filled('service_id')) {
            $query->whereJsonContains('services', (int) $request->service_id);
        }
        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hospital_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('verification_status') && $request->verification_status !== 'all') {
            $query->where('verification_status', $request->verification_status);
        }
        if ($request->filled('source_type') && $request->source_type !== 'all') {
            if ($request->source_type === 'imported') {
                $query->where('is_imported', true);
            } elseif ($request->source_type === 'registered') {
                $query->where('is_imported', false);
            }
        }

        $listings = $query->paginate(20)->withQueryString();
        $this->attachDuplicateDocumentDisplayCounts($listings->getCollection());

        $countries = MasterCountry::orderBy('name')->get();
        $cities    = $request->filled('country_code')
            ? MasterCity::where('country_code', $request->country_code)->where('status', true)->orderBy('name')->get()
            : collect();
        $locations = $request->filled('master_city_id')
            ? MasterLocation::where('master_city_id', $request->master_city_id)->where('status', true)->orderBy('location')->get()
            : collect();
        $serviceFilters = MasterService::where('status', true)->orderBy('service')->get();

        return view('listings.index', compact('listings', 'countries', 'cities', 'locations', 'serviceFilters'));
    }

    public function create(): View
    {
        $countries = MasterCountry::orderBy('name')->get();

        $cities = old('country_code')
            ? MasterCity::where('country_code', old('country_code'))->where('status', true)->orderBy('name')->get()
            : collect();
        $locations = old('master_city_id')
            ? MasterLocation::where('master_city_id', old('master_city_id'))->where('status', true)->orderBy('location')->get()
            : collect();

        $qualifications = MasterQualification::where('status', true)->orderBy('qualification')->get();
        $services       = MasterService::with(['children' => fn ($q) => $q->active()->orderBy('service')])
                            ->parents()->active()->orderBy('service')->get();

        return view('listings.create', [
            'countries'              => $countries,
            'cities'                 => $cities,
            'locations'              => $locations,
            'qualifications'         => $qualifications,
            'services'               => $services,
            'selectedQualifications' => (array) old('qualifications', []),
            'selectedServices'       => (array) old('services', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_code'        => 'required|exists:master_countries,code',
            'master_city_id'      => 'required|exists:master_cities,id',
            'master_location_id'  => 'nullable|exists:master_locations,id',
            'name'                => 'required|string|max:191',
            'hospital_name'       => 'nullable|string|max:191',
            'address'             => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:2000',
            'personal_contact_no' => 'nullable|string|max:20',
            'appointment_no'      => 'nullable|string|max:20',
            'qualifications'      => 'nullable|array',
            'qualifications.*'    => 'integer|exists:master_qualifications,id',
            'services'            => 'nullable|array',
            'services.*'          => 'integer|exists:master_services,id',
            'meta_data'           => 'nullable|array',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'status'              => 'nullable|boolean',
        ]);

        $validated['status']              = $request->boolean('status');
        $validated['verification_status'] = 'pending';
        $validated['is_verified_by_tatkaldoctor'] = false;

        $listing = Listing::create($validated);

        $this->audit->log($listing->id, 'created', null, [
            'name'    => $listing->name,
            'country' => $listing->country_code,
            'city_id' => $listing->master_city_id,
        ]);

        return redirect()->route('listings.index')
                         ->with('success', 'Listing created successfully.');
    }

    public function show(Listing $listing): View
    {
        $listing->load([
            'country',
            'city',
            'location',
            'documents' => fn ($query) => $query->with('verifiedBy')->latest(),
        ]);

        $documentListing = $this->documentSourceListingFor($listing);

        return view('listings.show', compact('listing', 'documentListing'));
    }

    public function edit(Listing $listing): View
    {
        $listing->load(['country', 'city', 'location']);
        $documentListing = $this->documentSourceListingFor($listing);

        $countries      = MasterCountry::orderBy('name')->get();
        $cities         = MasterCity::where('country_code', $listing->country_code)
                            ->where('status', true)->orderBy('name')->get();
        $locations      = MasterLocation::where('master_city_id', $listing->master_city_id)
                            ->where('status', true)->orderBy('location')->get();
        $qualifications = MasterQualification::where('status', true)->orderBy('qualification')->get();
        $services       = MasterService::with(['children' => fn ($q) => $q->active()->orderBy('service')])
                            ->parents()->active()->orderBy('service')->get();

        return view('listings.edit', [
            'listing'                => $listing,
            'countries'              => $countries,
            'cities'                 => $cities,
            'locations'              => $locations,
            'qualifications'         => $qualifications,
            'services'               => $services,
            'selectedQualifications' => (array) old('qualifications', $listing->qualifications ?? []),
            'selectedServices'       => (array) old('services', $listing->services ?? []),
            'documentListing'         => $documentListing,
        ]);
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'country_code'        => 'required|exists:master_countries,code',
            'master_city_id'      => 'required|exists:master_cities,id',
            'master_location_id'  => 'nullable|exists:master_locations,id',
            'name'                => 'required|string|max:191',
            'hospital_name'       => 'nullable|string|max:191',
            'address'             => 'nullable|string|max:500',
            'description'         => 'nullable|string|max:2000',
            'personal_contact_no' => 'nullable|string|max:20',
            'appointment_no'      => 'nullable|string|max:20',
            'qualifications'      => 'nullable|array',
            'qualifications.*'    => 'integer|exists:master_qualifications,id',
            'services'            => 'nullable|array',
            'services.*'          => 'integer|exists:master_services,id',
            'meta_data'           => 'nullable|array',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'status'              => 'nullable|boolean',
            'verification_status' => 'nullable|in:pending,approved,rejected',
            'rejection_reason'    => 'nullable|string|required_if:verification_status,rejected',
        ]);

        $validated['status'] = $request->boolean('status');

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isSuperAdmin() && $request->filled('verification_status')) {
            $newStatus = $validated['verification_status'];

            if ($newStatus === 'approved') {
                $validated['verified_at']       = now();
                $validated['verified_by']       = $user->id;
                $validated['rejection_reason']  = null;
                $validated['is_verified_by_tatkaldoctor'] = ! $listing->isImported();
            } elseif ($newStatus === 'rejected') {
                $validated['verified_at']       = null;
                $validated['verified_by']       = null;
                $validated['is_verified_by_tatkaldoctor'] = false;
            } else {
                // pending
                $validated['verified_at']       = null;
                $validated['verified_by']       = null;
                $validated['rejection_reason']  = null;
                $validated['is_verified_by_tatkaldoctor'] = false;
            }
        } else {
            unset($validated['verification_status'], $validated['rejection_reason']);
        }

        $old = $listing->only(['name', 'status', 'verification_status', 'country_code']);

        DB::transaction(function () use ($listing, $validated, $old, &$action) {
            $listing->update($validated);
            $listing->refresh();

            $action = isset($validated['verification_status']) ? 'verification_changed' : 'updated';

            $this->audit->log($listing->id, $action, $old, [
                'name'                => $listing->name,
                'status'              => $listing->status,
                'verification_status' => $listing->verification_status,
            ]);
        });

        $action ??= 'updated';
        $syncFailed = false;

        if ($action === 'verification_changed' && $listing->uuid) {
            Log::channel('doctor_listing')->info('ListingController: verification_status changed — firing solution sync', [
                'listing_id'          => $listing->id,
                'listing_uuid'        => $listing->uuid,
                'source'              => $listing->source,
                'verification_status' => $listing->verification_status,
            ]);

            $synced = $this->solutionSync->syncVerificationStatus(
                listingUuid:        $listing->uuid,
                verificationStatus: $listing->verification_status,
                remarks:            $listing->rejection_reason,
                approvedAt:         $listing->verified_at?->toIso8601String(),
                approvedBy:         $listing->verified_by,
            );

            $syncFailed = !$synced;
        }

        if ($syncFailed) {
            return redirect()->route('listings.index')
                ->with('success', 'Listing updated successfully.')
                ->with('warning', 'Solution sync failed — doctor dashboard may not reflect this change yet. Re-save the listing to retry.');
        }

        return redirect()->route('listings.index')
                         ->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        if ($listing->documents()->exists()) {
            return redirect()->route('listings.index')
                ->with('error', 'This listing has uploaded documents. Delete or reassign the documents before deleting the listing.');
        }

        $listing->delete();

        return redirect()->route('listings.index')
                         ->with('success', 'Listing deleted successfully.');
    }

    public function getCities($countryCode): JsonResponse
    {
        return response()->json(
            MasterCity::where('country_code', $countryCode)
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    public function getLocations($cityId): JsonResponse
    {
        return response()->json(
            MasterLocation::where('master_city_id', $cityId)
                ->where('status', true)
                ->orderBy('location')
                ->get(['id', 'location'])
        );
    }

    public function generateQr(Request $request, Listing $listing): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        if ($listing->verification_status !== 'approved') {
            return redirect()->route('listings.show', $listing)
                ->with('error', 'QR data can only be generated for approved listings.');
        }
        if ($listing->isImported()) {
            return redirect()->route('listings.show', $listing)
                ->with('error', 'Imported display doctors do not support QR generation.');
        }

        $this->qr->generate($listing);
        $listing->refresh();

        $this->audit->log($listing->id, 'qr_generated', null, [
            'qr_slug'            => $listing->qr_slug,
            'public_profile_url' => $listing->public_profile_url,
        ]);

        $this->solutionSync->syncListing($listing, 'listing.qr_generated');
        $synced = $this->solutionSync->syncVerificationStatus(
            listingUuid:        $listing->uuid,
            verificationStatus: $listing->verification_status,
            remarks:            $listing->rejection_reason,
            approvedAt:         $listing->verified_at?->toIso8601String(),
            approvedBy:         $listing->verified_by,
        );

        $redirect = redirect()->route('listings.show', $listing)
            ->with('success', 'QR data generated successfully.');

        if (!$synced) {
            $redirect->with('warning', 'Solution sync failed — doctor dashboard may not reflect this QR update. Try regenerating QR again.');
        }

        return $redirect;
    }

    private function attachDuplicateDocumentDisplayCounts($listings): void
    {
        $keys = $listings
            ->map(fn (Listing $listing) => $this->duplicateIdentityKey($listing))
            ->filter()
            ->unique()
            ->values();

        if ($keys->isEmpty()) {
            return;
        }

        $siblings = Listing::query()
            ->withCount([
                'documents',
                'documents as pending_documents_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->where(function ($query) use ($listings): void {
                foreach ($listings as $listing) {
                    if (! $listing->email || ! $listing->personal_contact_no || ! $listing->name) {
                        continue;
                    }

                    $query->orWhere(function ($subQuery) use ($listing): void {
                        $subQuery
                            ->whereRaw('LOWER(email) = ?', [strtolower($listing->email)])
                            ->where('personal_contact_no', $listing->personal_contact_no)
                            ->whereRaw('LOWER(name) = ?', [strtolower($listing->name)]);
                    });
                }
            })
            ->get()
            ->groupBy(fn (Listing $listing) => $this->duplicateIdentityKey($listing));

        foreach ($listings as $listing) {
            $listing->document_display_count = $listing->documents_count ?? 0;
            $listing->pending_document_display_count = $listing->pending_documents_count ?? 0;
            $listing->document_display_listing_id = $listing->id;

            $key = $this->duplicateIdentityKey($listing);
            if (! $key || ! isset($siblings[$key])) {
                continue;
            }

            $best = $siblings[$key]
                ->sortByDesc(fn (Listing $sibling) => (($sibling->pending_documents_count ?? 0) * 1000000) + (($sibling->documents_count ?? 0) * 1000) + $sibling->id)
                ->first();

            if ($best && (($best->documents_count ?? 0) > ($listing->documents_count ?? 0))) {
                $listing->document_display_count = $best->documents_count ?? 0;
                $listing->pending_document_display_count = $best->pending_documents_count ?? 0;
                $listing->document_display_listing_id = $best->id;
            }
        }
    }

    private function documentSourceListingFor(Listing $listing): Listing
    {
        $listing->loadMissing([
            'documents' => fn ($query) => $query->with('verifiedBy')->latest(),
        ]);

        if ($listing->documents->isNotEmpty()) {
            return $listing;
        }

        if (! $this->duplicateIdentityKey($listing)) {
            return $listing;
        }

        $documentListing = Listing::query()
            ->with([
                'documents' => fn ($query) => $query->with('verifiedBy')->latest(),
            ])
            ->withCount([
                'documents',
                'documents as pending_documents_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->whereRaw('LOWER(email) = ?', [strtolower($listing->email)])
            ->where('personal_contact_no', $listing->personal_contact_no)
            ->whereRaw('LOWER(name) = ?', [strtolower($listing->name)])
            ->orderByDesc('pending_documents_count')
            ->orderByDesc('documents_count')
            ->orderByDesc('id')
            ->first();

        return $documentListing && $documentListing->documents->isNotEmpty()
            ? $documentListing
            : $listing;
    }

    private function duplicateIdentityKey(Listing $listing): ?string
    {
        if (! $listing->email || ! $listing->personal_contact_no || ! $listing->name) {
            return null;
        }

        return strtolower($listing->email) . '|' . $listing->personal_contact_no . '|' . strtolower($listing->name);
    }
}
