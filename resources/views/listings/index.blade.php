@extends('layouts.admin')

@section('title', 'Listings — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Doctor Listings</h1>
        <p class="text-slate-500 text-sm mt-1">All doctor profiles on the platform.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <div class="flex items-center gap-2">
        <a href="{{ route('imported-doctors.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
            Imported Doctors
        </a>
        <a href="{{ route('listings.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Listing
        </a>
    </div>
    @endif
</div>

@include('partials.alerts')

{{-- Filter bar --}}
<form method="GET" action="{{ route('listings.index') }}"
      class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">

    {{-- Country --}}
    <div class="min-w-36">
        <label class="block text-xs font-medium text-slate-500 mb-1">Country</label>
        <select id="filter_country" name="country_code"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Countries</option>
            @foreach ($countries as $country)
                <option value="{{ $country->code }}" {{ request('country_code') === $country->code ? 'selected' : '' }}>
                    {{ $country->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- City --}}
    <div class="min-w-40">
        <label class="block text-xs font-medium text-slate-500 mb-1">City</label>
        <select id="filter_city" name="master_city_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Cities</option>
            @foreach ($cities as $city)
                <option value="{{ $city->id }}" {{ request('master_city_id') == $city->id ? 'selected' : '' }}>
                    {{ $city->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Location --}}
    <div class="min-w-40">
        <label class="block text-xs font-medium text-slate-500 mb-1">Location</label>
        <select id="filter_location" name="master_location_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Locations</option>
            @foreach ($locations as $loc)
                <option value="{{ $loc->id }}" {{ request('master_location_id') == $loc->id ? 'selected' : '' }}>
                    {{ $loc->location }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="all"      {{ request('status', 'all') === 'all'   ? 'selected' : '' }}>All</option>
            <option value="active"   {{ request('status') === 'active'       ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive'     ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    {{-- Service --}}
    <div class="min-w-44">
        <label class="block text-xs font-medium text-slate-500 mb-1">Service</label>
        <select name="service_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Services</option>
            @foreach ($serviceFilters as $service)
                <option value="{{ $service->id }}" {{ request('service_id') == $service->id ? 'selected' : '' }}>
                    {{ $service->service }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Verification Status --}}
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Verification</label>
        <select name="verification_status"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="all"      {{ request('verification_status', 'all') === 'all'       ? 'selected' : '' }}>All</option>
            <option value="pending"  {{ request('verification_status') === 'pending'           ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ request('verification_status') === 'approved'          ? 'selected' : '' }}>Approved</option>
            <option value="rejected" {{ request('verification_status') === 'rejected'          ? 'selected' : '' }}>Rejected</option>
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Source</label>
        <select name="source_type"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="all" {{ request('source_type', 'all') === 'all' ? 'selected' : '' }}>All</option>
            <option value="registered" {{ request('source_type') === 'registered' ? 'selected' : '' }}>Registered</option>
            <option value="imported" {{ request('source_type') === 'imported' ? 'selected' : '' }}>Imported</option>
        </select>
    </div>

    {{-- Search --}}
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Doctor name or hospital…"
               class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['country_code', 'master_city_id', 'master_location_id', 'service_id', 'status', 'search', 'verification_status', 'source_type']))
        <a href="{{ route('listings.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($listings->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <p class="text-sm">No listings found.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('listings.create') }}" class="text-teal-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Doctor Name</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Hospital Name</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">City</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Location</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Verification</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documents</th>
                    <th class="px-4 py-3 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($listings as $listing)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $listing->id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $listing->name }}</p>
                        @if (count($listing->qualification_names))
                            <p class="text-xs text-teal-600 mt-1">{{ implode(', ', $listing->qualification_names) }}</p>
                        @endif
                        @if (count($listing->service_names))
                            <p class="text-xs text-rose-600 mt-1">{{ implode(', ', $listing->service_names) }}</p>
                        @elseif ($listing->is_imported && !empty($listing->meta_data['speciality']))
                            <p class="text-xs text-rose-600 mt-1">{{ $listing->meta_data['speciality'] }}</p>
                        @endif
                        @if ($listing->is_imported)
                            <p class="text-xs text-slate-500 mt-1">Imported display doctor</p>
                        @endif
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ substr($listing->uuid, 0, 8) }}…</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $listing->hospital_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $listing->city?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $listing->location?->location ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($listing->status)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($listing->is_imported)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Imported
                            </span>
                        @elseif ($listing->verification_status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                            </span>
                        @elseif ($listing->verification_status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $documentDisplayListingId = $listing->document_display_listing_id ?? $listing->id;
                            $documentDisplayCount = $listing->document_display_count ?? $listing->documents_count ?? 0;
                            $pendingDocumentDisplayCount = $listing->pending_document_display_count ?? $listing->pending_documents_count ?? 0;
                        @endphp
                        @if ($pendingDocumentDisplayCount > 0)
                            <a href="{{ route('listings.show', $documentDisplayListingId) }}#documents"
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Pending ({{ $pendingDocumentDisplayCount }})
                            </a>
                        @elseif ($documentDisplayCount > 0)
                            <a href="{{ route('listings.show', $documentDisplayListingId) }}#documents"
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 hover:bg-emerald-200 transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ $documentDisplayCount }} Uploaded
                            </a>
                        @else
                            <span class="text-xs text-slate-400">None</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('listings.show', $listing) }}{{ $documentDisplayCount > 0 ? '#documents' : '' }}"
                               class="text-xs text-teal-600 hover:text-teal-800 font-medium">View</a>
                            @if (Auth::user()->isSuperAdmin())
                            <a href="{{ route('listings.edit', $listing) }}{{ $documentDisplayCount > 0 ? '#documents' : '' }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('listings.destroy', $listing) }}"
                                  onsubmit="return confirm('Delete listing for \'{{ addslashes($listing->name) }}\'? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @if ($listings->hasPages())
        <div class="px-4 py-4 border-t border-slate-100">
            {{ $listings->links() }}
        </div>
        @endif
    @endif
</div>

@endsection

@push('scripts')
<script>
(function () {
    const filterCountry  = document.getElementById('filter_country');
    const filterCity     = document.getElementById('filter_city');
    const filterLocation = document.getElementById('filter_location');
    const citiesBase     = '{{ url("listings-cities") }}';
    const locationsBase  = '{{ url("listings-locations") }}';

    filterCountry.addEventListener('change', function () {
        filterCity.innerHTML     = '<option value="">All Cities</option>';
        filterLocation.innerHTML = '<option value="">All Locations</option>';
        if (!this.value) return;
        fetch(citiesBase + '/' + encodeURIComponent(this.value))
            .then(r => r.json())
            .then(data => data.forEach(c => filterCity.appendChild(new Option(c.name, c.id))));
    });

    filterCity.addEventListener('change', function () {
        filterLocation.innerHTML = '<option value="">All Locations</option>';
        if (!this.value) return;
        fetch(locationsBase + '/' + this.value)
            .then(r => r.json())
            .then(data => data.forEach(l => filterLocation.appendChild(new Option(l.location, l.id))));
    });
})();
</script>
@endpush
