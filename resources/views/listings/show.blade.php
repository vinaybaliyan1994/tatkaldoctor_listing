@extends('layouts.admin')

@section('title', $listing->name . ' - TatkalDoctor Admin')

@section('content')

{{-- Breadcrumb + actions --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('listings.index') }}" class="hover:text-slate-700">Listings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-800 font-medium truncate max-w-xs">{{ $listing->name }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('listings.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('listings.edit', $listing) }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            Edit
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

{{-- Header card --}}
<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-2xl font-bold text-slate-800">{{ $listing->name }}</h1>
                @if ($listing->source === 'solution_registration')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-violet-100 text-violet-700">
                    Solution Registration
                </span>
                @endif
            </div>
            @if ($listing->hospital_name)
            <p class="text-slate-500 mt-1">{{ $listing->hospital_name }}</p>
            @endif
            <p class="text-xs text-slate-400 font-mono mt-2">UUID: {{ $listing->uuid }}</p>
        </div>
        <div class="flex flex-col gap-2 items-end">
            @if ($listing->status)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                </span>
            @endif
            @if ($listing->is_imported)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Imported Display
                </span>
            @elseif ($listing->verification_status === 'approved')
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
        <span class="text-xs text-slate-400 ml-1">average rating</span>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Profile Photo (intake submissions) --}}
    @if ($listing->profile_photo_path)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2 flex items-center gap-5">
        <img src="{{ asset('storage/' . $listing->profile_photo_path) }}"
             alt="Profile Photo"
             class="w-24 h-24 rounded-xl object-cover border border-slate-100">
        <div>
            <p class="text-sm font-semibold text-slate-700">Profile Photo</p>
            <p class="text-xs text-slate-400 font-mono mt-1">{{ $listing->profile_photo_path }}</p>
        </div>
    </div>
    @endif

    {{-- Location details --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Location</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500 flex-shrink-0">Country</dt>
                <dd class="text-slate-800 font-medium">
                    <span class="font-mono text-xs bg-teal-50 text-teal-700 px-1.5 py-0.5 rounded mr-1">{{ $listing->country_code }}</span>
                    {{ $listing->country?->name ?? '—' }}
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500 flex-shrink-0">City</dt>
                <dd class="text-slate-800">
                    {{ $listing->city?->name ?? ($listing->meta_data['city_name'] ?? '—') }}
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500 flex-shrink-0">Area</dt>
                <dd class="text-slate-800">
                    {{ $listing->location?->location ?? ($listing->meta_data['area_name'] ?? '—') }}
                </dd>
            </div>
            @if ($listing->address)
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500 flex-shrink-0">Address</dt>
                <dd class="text-slate-800">{{ $listing->address }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Contact --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Contact</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Personal No.</dt>
                <dd class="text-slate-800">{{ $listing->personal_contact_no ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Appointment No.</dt>
                <dd class="text-slate-800">{{ $listing->appointment_no ?? '—' }}</dd>
            </div>
            @if ($listing->email)
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Email</dt>
                <dd class="text-slate-800">{{ $listing->email }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Professional Details (from meta_data for intake submissions) --}}
    @php
        $meta = $listing->meta_data ?? [];
        $hasMetaDetails = !empty($meta['experience_years']) || !empty($meta['consultation_fee'])
            || !empty($meta['specialities_text']) || !empty($meta['qualifications_text'])
            || !empty($meta['registration_no']);
    @endphp
    @if ($hasMetaDetails)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Professional Details</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            @if (!empty($meta['registration_no']))
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500 flex-shrink-0">Registration No.</dt>
                <dd class="text-slate-800 font-mono">{{ $meta['registration_no'] }}</dd>
            </div>
            @endif
            @if (!empty($meta['experience_years']))
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500 flex-shrink-0">Experience</dt>
                <dd class="text-slate-800">{{ $meta['experience_years'] }} years</dd>
            </div>
            @endif
            @if (!empty($meta['consultation_fee']))
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500 flex-shrink-0">Consultation Fee</dt>
                <dd class="text-slate-800">₹{{ number_format($meta['consultation_fee']) }}</dd>
            </div>
            @endif
            @if (!empty($meta['specialities_text']))
            <div class="flex gap-3 sm:col-span-2">
                <dt class="w-40 text-slate-500 flex-shrink-0">Specialities</dt>
                <dd class="text-slate-800">{{ $meta['specialities_text'] }}</dd>
            </div>
            @endif
            @if (!empty($meta['qualifications_text']))
            <div class="flex gap-3 sm:col-span-2">
                <dt class="w-40 text-slate-500 flex-shrink-0">Qualifications</dt>
                <dd class="text-slate-800">{{ $meta['qualifications_text'] }}</dd>
            </div>
            @endif
        </dl>
    </div>
    @endif

    {{-- Doctor Submitted Data (structured — solution_registration source) --}}
    @php
        $suggestedServices       = $meta['suggested_services']       ?? [];
        $suggestedQualifications = $meta['suggested_qualifications'] ?? [];
        $suggestedCity           = $meta['suggested_city']           ?? null;
        $suggestedLocation       = $meta['suggested_location']       ?? null;
        $hasSubmittedData = !empty($suggestedServices) || !empty($suggestedQualifications)
            || $suggestedCity || $suggestedLocation;
        // Also show if services/qualifications have master IDs assigned
        $assignedServiceNames = $listing->service_names;
        $assignedQualNames    = $listing->qualification_names;
        $hasMasterData = !empty($assignedServiceNames) || !empty($assignedQualNames)
            || $listing->master_city_id || $listing->master_location_id;
        $showSubmittedSection = $listing->source === 'solution_registration'
            && ($hasSubmittedData || $hasMasterData);
    @endphp
    @if ($showSubmittedSection)
    <div class="bg-violet-50 rounded-xl border border-violet-200 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-violet-800 uppercase tracking-wide mb-4 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Doctor Submitted Data
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

            {{-- Services / Specialities --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Specialities / Services</p>
                @if(!empty($assignedServiceNames))
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach($assignedServiceNames as $name)
                    <span class="px-2 py-0.5 bg-violet-100 text-violet-700 text-xs font-medium rounded">
                        {{ $name }}
                    </span>
                    @endforeach
                </div>
                @endif
                @if(!empty($suggestedServices))
                <p class="text-xs text-amber-700 font-medium mb-1">
                    <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    New services suggested (not in master list):
                </p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($suggestedServices as $name)
                    <div class="flex items-center gap-1">
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-medium rounded border border-amber-200">{{ $name }}</span>
                        @if(Auth::user()->isSuperAdmin())
                        <form method="POST" action="{{ route('master-services.store') }}" class="inline">
                            @csrf
                            <input type="hidden" name="service" value="{{ $name }}">
                            <input type="hidden" name="status" value="1">
                            <button type="submit" title="Add '{{ $name }}' to master services"
                                    class="px-1.5 py-0.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition-colors"
                                    onclick="return confirm('Add \'{{ $name }}\' to Master Services?')">
                                + Add
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
                @if(empty($assignedServiceNames) && empty($suggestedServices))
                <p class="text-slate-400 text-xs">Not submitted</p>
                @endif
            </div>

            {{-- Qualifications --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Qualifications</p>
                @if(!empty($assignedQualNames))
                <div class="flex flex-wrap gap-1.5 mb-2">
                    @foreach($assignedQualNames as $name)
                    <span class="px-2 py-0.5 bg-violet-100 text-violet-700 text-xs font-medium rounded">
                        {{ $name }}
                    </span>
                    @endforeach
                </div>
                @endif
                @if(!empty($suggestedQualifications))
                <p class="text-xs text-amber-700 font-medium mb-1">
                    <svg class="w-3 h-3 inline mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    New qualifications suggested (not in master list):
                </p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($suggestedQualifications as $name)
                    <div class="flex items-center gap-1">
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs font-medium rounded border border-amber-200">{{ $name }}</span>
                        @if(Auth::user()->isSuperAdmin())
                        <form method="POST" action="{{ route('master-qualifications.store') }}" class="inline">
                            @csrf
                            <input type="hidden" name="qualification" value="{{ $name }}">
                            <input type="hidden" name="status" value="1">
                            <button type="submit" title="Add '{{ $name }}' to master qualifications"
                                    class="px-1.5 py-0.5 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition-colors"
                                    onclick="return confirm('Add \'{{ $name }}\' to Master Qualifications?')">
                                + Add
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
                @if(empty($assignedQualNames) && empty($suggestedQualifications))
                <p class="text-slate-400 text-xs">Not submitted</p>
                @endif
            </div>

            {{-- City --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">City</p>
                @if($listing->city)
                <span class="text-sm text-slate-800">{{ $listing->city->name }}</span>
                @elseif($suggestedCity)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-800">{{ $suggestedCity }}</span>
                    <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded border border-amber-200">
                        New City Suggested
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Not yet in master city list — assign after adding.</p>
                @elseif($meta['city_name'] ?? null)
                <span class="text-sm text-slate-800">{{ $meta['city_name'] }}</span>
                @else
                <p class="text-slate-400 text-xs">Not submitted</p>
                @endif
            </div>

            {{-- Location / Area --}}
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Area / Location</p>
                @if($listing->location)
                <span class="text-sm text-slate-800">{{ $listing->location->location }}</span>
                @elseif($suggestedLocation)
                <div class="flex items-center gap-2">
                    <span class="text-sm text-slate-800">{{ $suggestedLocation }}</span>
                    <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded border border-amber-200">
                        New Location Suggested
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Not yet in master location list — assign after adding.</p>
                @elseif($meta['area_name'] ?? null)
                <span class="text-sm text-slate-800">{{ $meta['area_name'] }}</span>
                @else
                <p class="text-slate-400 text-xs">Not submitted</p>
                @endif
            </div>

        </div>
    </div>
    @endif

    {{-- Qualifications --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Qualifications</h2>
        @php $qualNames = $listing->qualification_names; @endphp
        @if (count($qualNames))
            <div class="flex flex-wrap gap-2">
                @foreach ($qualNames as $name)
                    <span class="px-2.5 py-1 bg-teal-50 text-teal-700 font-mono font-semibold text-xs rounded-lg">
                        {{ $name }}
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-slate-400 text-sm">No qualifications assigned.</p>
        @endif
    </div>

    {{-- Services --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Services</h2>
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
            <p class="text-slate-400 text-sm">No services assigned.</p>
        @endif
    </div>

    {{-- Description --}}
    @if ($listing->description)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Description</h2>
        <p class="text-slate-700 text-sm leading-relaxed whitespace-pre-line">{{ $listing->description }}</p>
    </div>
    @endif

    {{-- Coordinates --}}
    @if ($listing->latitude || $listing->longitude)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Coordinates</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500">Latitude</dt>
                <dd class="text-slate-800 font-mono">{{ $listing->latitude ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-28 text-slate-500">Longitude</dt>
                <dd class="text-slate-800 font-mono">{{ $listing->longitude ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    @endif

    {{-- Verification --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Verification</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Status</dt>
                <dd>
                    @if ($listing->is_imported)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Imported Display
                        </span>
                    @elseif ($listing->verification_status === 'approved')
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
                <dt class="w-36 text-slate-500 flex-shrink-0">Verified At</dt>
                <dd class="text-slate-800">{{ $listing->verified_at->format('d M Y, H:i') }}</dd>
            </div>
            @endif
            @if ($listing->verifiedBy)
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Verified By</dt>
                <dd class="text-slate-800">{{ $listing->verifiedBy->name }}</dd>
            </div>
            @endif
            @if ($listing->rejection_reason)
            <div class="flex gap-3">
                <dt class="w-36 text-slate-500 flex-shrink-0">Rejection Reason</dt>
                <dd class="text-red-700">{{ $listing->rejection_reason }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- QR / Public Profile / WhatsApp QR --}}
    @if (Auth::user()->isAdmin())
    @php
        $waPhone   = config('tatkaldoctor.whatsapp_business_phone', '919999999999');
        $waLink    = $listing->qr_slug
            ? 'https://wa.me/' . $waPhone . '?text=' . urlencode('qr:' . $listing->qr_slug)
            : null;
    @endphp
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">QR &amp; Public Profile</h2>
            @if ($listing->verification_status === 'approved')
            <form method="POST" action="{{ route('listings.generate-qr', $listing) }}">
                @csrf
                <button type="submit"
                        class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-medium rounded-xl transition-colors">
                    {{ $listing->qr_slug ? 'Regenerate QR' : 'Generate QR' }}
                </button>
            </form>
            @else
            <span class="text-xs text-slate-400">Approve listing to generate QR</span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Left: metadata --}}
            <dl class="space-y-3 text-sm">
                <div class="flex gap-3">
                    <dt class="w-36 text-slate-500 flex-shrink-0">QR Slug</dt>
                    <dd class="text-slate-700 font-mono text-xs">{{ $listing->qr_slug ?? '—' }}</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-36 text-slate-500 flex-shrink-0">Public Profile URL</dt>
                    <dd>
                        @if ($listing->public_profile_url)
                            <a href="{{ $listing->public_profile_url }}" target="_blank"
                               class="text-teal-600 hover:text-teal-800 text-xs break-all">
                                {{ $listing->public_profile_url }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </dd>
                </div>
                @if ($waLink)
                <div class="flex gap-3">
                    <dt class="w-36 text-slate-500 flex-shrink-0">WhatsApp Link</dt>
                    <dd>
                        <a href="{{ $waLink }}" target="_blank"
                           class="text-green-600 hover:text-green-800 text-xs break-all">
                            {{ $waLink }}
                        </a>
                    </dd>
                </div>
                @endif
                @if ($listing->qr_generated_at)
                <div class="flex gap-3">
                    <dt class="w-36 text-slate-500 flex-shrink-0">Generated At</dt>
                    <dd class="text-slate-800">{{ $listing->qr_generated_at->format('d M Y, H:i') }}</dd>
                </div>
                @endif
            </dl>

            {{-- Right: WhatsApp QR image (server-generated SVG) --}}
            @if ($listing->qr_code_path)
            <div class="flex flex-col items-center gap-3">
                <p class="text-xs font-medium text-slate-600 self-start">WhatsApp QR</p>
                <div class="border border-slate-100 rounded-xl p-2 bg-white inline-block">
                    <img src="{{ Storage::disk('public')->url($listing->qr_code_path) }}"
                         alt="WhatsApp QR Code"
                         width="180" height="180"
                         class="block">
                </div>
                <a href="{{ Storage::disk('public')->url($listing->qr_code_path) }}"
                   download="whatsapp-qr-{{ $listing->qr_slug }}.svg"
                   class="px-3 py-1.5 text-xs bg-slate-800 hover:bg-slate-700 text-white rounded-xl transition-colors">
                    Download SVG
                </a>
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- Documents --}}
    @if (Auth::user()->isAdmin())
    <div id="documents" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2 scroll-mt-6">
        @php
            $documentListing = $documentListing ?? $listing;
            $documents = $documentListing->documents;
            $docCount = $documents->count();
            $pendingDocs = $documents->where('status', 'pending')->count();
            $approvedDocs = $documents->where('status', 'approved')->count();
            $rejectedDocs = $documents->where('status', 'rejected')->count();
        @endphp
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Documents</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $docCount }} total</span>
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
            </div>
            <div class="flex gap-2">
                <a href="{{ route('doctor-documents.index', $documentListing) }}"
                   class="text-xs text-teal-600 hover:text-teal-800 font-medium">Review Page</a>
                <a href="{{ route('doctor-documents.create', $documentListing) }}"
                   class="text-xs text-white bg-teal-600 hover:bg-teal-700 px-2 py-1 rounded font-medium transition-colors">+ Upload</a>
            </div>
        </div>

        @if ($documentListing->id !== $listing->id)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
                Documents are attached to duplicate listing #{{ $documentListing->id }}. Review actions below update those document records.
            </div>
        @endif

        @if ($documents->isEmpty())
            <div class="rounded-lg border border-dashed border-slate-200 p-8 text-center">
                <p class="text-slate-400 text-sm">No documents uploaded yet.</p>
            </div>
        @else
            <div class="overflow-x-auto border border-slate-100 rounded-xl">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Document Type</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Uploaded At</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">File</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Remarks</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($documents as $doc)
                        @php
                            $fileUrl = Storage::disk('public')->url($doc->file_path);
                            $mime = $doc->mime_type ?? '';
                            $isImage = str_starts_with($mime, 'image/');
                            $isPdf = $mime === 'application/pdf' || str_ends_with(strtolower($doc->file_path), '.pdf');
                        @endphp
                        <tr class="align-top">
                            <td class="px-3 py-3">
                                <div class="font-medium text-slate-800">{{ $doc->document_type_label }}</div>
                                <div class="text-xs text-slate-400">ID #{{ $doc->id }}</div>
                            </td>
                            <td class="px-3 py-3">
                                @if($doc->status === 'approved')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                                @elseif($doc->status === 'rejected')
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">Rejected</span>
                                @else
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                                @endif
                                @if($doc->verifiedBy)
                                    <div class="text-xs text-slate-400 mt-1">by {{ $doc->verifiedBy->name }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-500 text-xs whitespace-nowrap">
                                {{ $doc->created_at?->format('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-3 py-3 min-w-56">
                                <div class="flex items-center gap-3">
                                    @if($isImage)
                                        <a href="{{ $fileUrl }}" target="_blank" class="block flex-shrink-0">
                                            <img src="{{ $fileUrl }}"
                                                 alt="{{ $doc->original_name ?? $doc->document_type_label }}"
                                                 class="h-14 w-14 rounded object-cover border border-slate-100">
                                        </a>
                                    @elseif($isPdf)
                                        <a href="{{ $fileUrl }}" target="_blank"
                                           class="h-14 w-14 rounded border border-red-100 bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            PDF
                                        </a>
                                    @else
                                        <div class="h-14 w-14 rounded border border-slate-100 bg-slate-50 text-slate-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            FILE
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <a href="{{ $fileUrl }}" target="_blank"
                                           class="text-teal-600 hover:text-teal-800 font-medium break-all">
                                            {{ $doc->original_name ?? basename($doc->file_path) }}
                                        </a>
                                        <div class="text-xs text-slate-400 mt-1">
                                            {{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : 'Size unknown' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-slate-600 min-w-48">
                                {{ $doc->remarks ?: '-' }}
                            </td>
                            <td class="px-3 py-3 min-w-72">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('doctor-documents.show', $doc) }}"
                                       class="text-xs text-teal-600 hover:text-teal-800 font-medium">View</a>
                                    <a href="{{ route('doctor-documents.download', $doc) }}"
                                       class="text-xs text-slate-600 hover:text-slate-800 font-medium">Download</a>
                                    <a href="{{ route('doctor-documents.verify', $doc) }}"
                                       class="text-xs text-amber-600 hover:text-amber-800 font-medium">Review</a>
                                </div>
                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <form method="POST" action="{{ route('doctor-documents.update-status', $doc) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <input type="hidden" name="redirect_to" value="listing_documents">
                                        <button type="submit"
                                                class="w-full px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded transition-colors">
                                            Approve
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('doctor-documents.update-status', $doc) }}" class="space-y-2">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <input type="hidden" name="redirect_to" value="listing_documents">
                                        <input type="text" name="remarks" required maxlength="500"
                                               placeholder="Reject remarks"
                                               class="w-full px-2 py-1.5 rounded border border-slate-200 text-xs focus:ring-2 focus:ring-red-400 focus:border-transparent">
                                        <button type="submit"
                                                class="w-full px-2 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded transition-colors">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Audit Logs --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Audit Log</h2>
            <a href="{{ route('listing-audit-logs.index', ['listing_id' => $listing->id]) }}"
               class="text-xs text-teal-600 hover:text-teal-800 font-medium">View All</a>
        </div>
        @php $recentLogs = $listing->auditLogs()->with('changedBy')->latest()->take(5)->get(); @endphp
        @if ($recentLogs->isEmpty())
            <p class="text-slate-400 text-sm">No audit entries yet.</p>
        @else
            <ul class="space-y-2">
                @foreach ($recentLogs as $log)
                <li class="flex items-start gap-2 text-xs">
                    <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-mono bg-slate-100 text-slate-600 flex-shrink-0">
                        {{ $log->action }}
                    </span>
                    <span class="text-slate-500">
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
