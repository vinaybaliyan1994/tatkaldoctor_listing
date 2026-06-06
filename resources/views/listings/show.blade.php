@extends('layouts.admin')

@section('title', $listing->name . ' - TatkalDoctor Admin')

@section('content')

{{-- Breadcrumb + actions --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('listings.index') }}" class="hover:text-gray-700">Listings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium truncate max-w-xs">{{ $listing->name }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('listings.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('listings.edit', $listing) }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Edit
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

{{-- Header card --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $listing->name }}</h1>
            @if ($listing->hospital_name)
            <p class="text-gray-500 mt-1">{{ $listing->hospital_name }}</p>
            @endif
            <p class="text-xs text-gray-400 font-mono mt-2">UUID: {{ $listing->uuid }}</p>
        </div>
        <div class="flex flex-col gap-2 items-end">
            @if ($listing->status)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                </span>
            @endif
            @if ($listing->verification_status === 'approved')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified
                </span>
            @elseif ($listing->verification_status === 'rejected')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Verification
                </span>
            @endif
        </div>
    </div>

    @if ($listing->average_rating > 0)
    <div class="mt-3">
        <span class="text-yellow-500 font-semibold">★ {{ number_format($listing->average_rating, 2) }}</span>
        <span class="text-xs text-gray-400 ml-1">average rating</span>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Location details --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Location</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500 flex-shrink-0">Country</dt>
                <dd class="text-gray-800 font-medium">
                    <span class="font-mono text-xs bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded mr-1">{{ $listing->country_code }}</span>
                    {{ $listing->country?->name ?? '—' }}
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500 flex-shrink-0">City</dt>
                <dd class="text-gray-800">{{ $listing->city?->name ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500 flex-shrink-0">Location</dt>
                <dd class="text-gray-800">{{ $listing->location?->location ?? '—' }}</dd>
            </div>
            @if ($listing->address)
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500 flex-shrink-0">Address</dt>
                <dd class="text-gray-800">{{ $listing->address }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Contact --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Contact</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Personal No.</dt>
                <dd class="text-gray-800">{{ $listing->personal_contact_no ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Appointment No.</dt>
                <dd class="text-gray-800">{{ $listing->appointment_no ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Qualifications --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Qualifications</h2>
        @php $qualNames = $listing->qualification_names; @endphp
        @if (count($qualNames))
            <div class="flex flex-wrap gap-2">
                @foreach ($qualNames as $name)
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-mono font-semibold text-xs rounded-lg">
                        {{ $name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-sm">No qualifications assigned.</p>
        @endif
    </div>

    {{-- Services --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Services</h2>
        @php $svcNames = $listing->service_names; @endphp
        @if (count($svcNames))
            <div class="flex flex-wrap gap-2">
                @foreach ($svcNames as $name)
                    <span class="px-2.5 py-1 bg-rose-50 text-rose-700 text-xs font-medium rounded-lg">
                        {{ $name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-gray-400 text-sm">No services assigned.</p>
        @endif
    </div>

    {{-- Description --}}
    @if ($listing->description)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Description</h2>
        <p class="text-gray-700 text-sm leading-relaxed whitespace-pre-line">{{ $listing->description }}</p>
    </div>
    @endif

    {{-- Coordinates --}}
    @if ($listing->latitude || $listing->longitude)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Coordinates</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500">Latitude</dt>
                <dd class="text-gray-800 font-mono">{{ $listing->latitude ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-gray-500">Longitude</dt>
                <dd class="text-gray-800 font-mono">{{ $listing->longitude ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- Verification --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Verification</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Status</dt>
                <dd>
                    @if ($listing->verification_status === 'approved')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                        </span>
                    @elseif ($listing->verification_status === 'rejected')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                        </span>
                    @endif
                </dd>
            </div>
            @if ($listing->verified_at)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Verified At</dt>
                <dd class="text-gray-800">{{ $listing->verified_at->format('d M Y, H:i') }}</dd>
            </div>
            @endif
            @if ($listing->verifiedBy)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Verified By</dt>
                <dd class="text-gray-800">{{ $listing->verifiedBy->name }}</dd>
            </div>
            @endif
            @if ($listing->rejection_reason)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Rejection Reason</dt>
                <dd class="text-red-700">{{ $listing->rejection_reason }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- QR / Public Profile --}}
    @if (Auth::user()->isAdmin())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">QR &amp; Public Profile</h2>
            @if ($listing->verification_status === 'approved')
            <form method="POST" action="{{ route('listings.generate-qr', $listing) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                    {{ $listing->qr_slug ? 'Regenerate QR' : 'Generate QR' }}
                </button>
            </form>
            @else
            <span class="text-xs text-gray-400">Approve listing to generate QR</span>
            @endif
        </div>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">QR Slug</dt>
                <dd class="text-gray-700 font-mono text-xs">{{ $listing->qr_slug ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Public Profile URL</dt>
                <dd>
                    @if ($listing->public_profile_url)
                        <a href="{{ $listing->public_profile_url }}" target="_blank"
                           class="text-blue-600 hover:text-blue-800 text-xs break-all">
                            {{ $listing->public_profile_url }}
                        </a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>
            </div>
            @if ($listing->qr_generated_at)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Generated At</dt>
                <dd class="text-gray-800">{{ $listing->qr_generated_at->format('d M Y, H:i') }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- Documents --}}
    @if (Auth::user()->isAdmin())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Documents</h2>
            <div class="flex gap-2">
                <a href="{{ route('doctor-documents.index', $listing) }}"
                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">View All</a>
                <a href="{{ route('doctor-documents.create', $listing) }}"
                   class="text-xs text-white bg-blue-600 hover:bg-blue-700 px-2 py-1 rounded font-medium">+ Upload</a>
            </div>
        </div>
        @php $docCount = $listing->doctorDocuments()->count(); @endphp
        @if ($docCount)
            @php
                $pendingDocs  = $listing->doctorDocuments()->where('status', 'pending')->count();
                $approvedDocs = $listing->doctorDocuments()->where('status', 'approved')->count();
                $rejectedDocs = $listing->doctorDocuments()->where('status', 'rejected')->count();
            @endphp
            <div class="flex flex-wrap gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $docCount }} total</span>
                @if ($pendingDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendingDocs }} pending</span>
                @endif
                @if ($approvedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $approvedDocs }} approved</span>
                @endif
                @if ($rejectedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">{{ $rejectedDocs }} rejected</span>
                @endif
            </div>
        @else
            <p class="text-gray-400 text-sm">No documents uploaded.</p>
        @endif
    </div>

    {{-- Audit Logs --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Audit Log</h2>
            <a href="{{ route('listing-audit-logs.index', ['listing_id' => $listing->id]) }}"
               class="text-xs text-blue-600 hover:text-blue-800 font-medium">View All</a>
        </div>
        @php $recentLogs = $listing->auditLogs()->with('changedBy')->latest()->take(5)->get(); @endphp
        @if ($recentLogs->isEmpty())
            <p class="text-gray-400 text-sm">No audit entries yet.</p>
        @else
            <ul class="space-y-2">
                @foreach ($recentLogs as $log)
                <li class="flex items-start gap-2 text-xs">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-mono bg-gray-100 text-gray-600 flex-shrink-0">
                        {{ $log->action }}
                    </span>
                    <span class="text-gray-500">
                        {{ $log->changedBy?->name ?? 'System' }} &middot; {{ $log->created_at->diffForHumans() }}
                    </span>
                </li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

</div>

@endsection
