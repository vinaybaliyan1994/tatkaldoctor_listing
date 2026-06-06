@extends('layouts.admin')

@section('title', 'Add Listing — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('listings.index') }}" class="hover:text-gray-700">Listings</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Add Listing</span>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-1">Add Doctor Listing</h1>
<p class="text-gray-500 text-sm mb-6">Fill in the doctor's profile details below.</p>

@include('partials.alerts')

<form method="POST" action="{{ route('listings.store') }}" class="space-y-5">
@csrf

{{-- Card 1: Basic Information --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Basic Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="country_code">
                Country <span class="text-red-500">*</span>
            </label>
            <select id="country_code" name="country_code" required
                    class="w-full px-4 py-2.5 rounded-lg border @error('country_code') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">— Select country —</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->code }}"
                            {{ old('country_code') === $country->code ? 'selected' : '' }}>
                        {{ $country->name }} ({{ $country->code }})
                    </option>
                @endforeach
            </select>
            @error('country_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="master_city_id">
                City <span class="text-red-500">*</span>
            </label>
            <select id="master_city_id" name="master_city_id" required
                    class="w-full px-4 py-2.5 rounded-lg border @error('master_city_id') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">— Select city —</option>
                @foreach ($cities as $city)
                    <option value="{{ $city->id }}"
                            {{ old('master_city_id') == $city->id ? 'selected' : '' }}>
                        {{ $city->name }}
                    </option>
                @endforeach
            </select>
            @error('master_city_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="master_location_id">
                Location <span class="text-gray-400 text-xs font-normal">(optional)</span>
            </label>
            <select id="master_location_id" name="master_location_id"
                    class="w-full px-4 py-2.5 rounded-lg border @error('master_location_id') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">— Select location —</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}"
                            {{ old('master_location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->location }}
                    </option>
                @endforeach
            </select>
            @error('master_location_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                Doctor Full Name <span class="text-red-500">*</span>
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   placeholder="e.g. Dr. Ravi Kumar" maxlength="191" required
                   class="w-full px-4 py-2.5 rounded-lg border @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="hospital_name">
                Hospital Name <span class="text-gray-400 text-xs font-normal">(optional)</span>
            </label>
            <input id="hospital_name" name="hospital_name" type="text"
                   value="{{ old('hospital_name') }}" maxlength="191"
                   placeholder="e.g. City General Hospital"
                   class="w-full px-4 py-2.5 rounded-lg border @error('hospital_name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('hospital_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

{{-- Card 2: Details --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Details</h2>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="address">Address</label>
            <textarea id="address" name="address" rows="2" maxlength="500"
                      placeholder="Street address, landmark…"
                      class="w-full px-4 py-2.5 rounded-lg border @error('address') border-red-400 bg-red-50 @else border-gray-300 @enderror
                             text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none">{{ old('address') }}</textarea>
            @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="description">Description</label>
            <textarea id="description" name="description" rows="4" maxlength="2000"
                      placeholder="Brief bio or profile summary…"
                      class="w-full px-4 py-2.5 rounded-lg border @error('description') border-red-400 bg-red-50 @else border-gray-300 @enderror
                             text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-y">{{ old('description') }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Card 3: Contact Information --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Contact Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="personal_contact_no">Personal Contact No.</label>
            <input id="personal_contact_no" name="personal_contact_no" type="text"
                   value="{{ old('personal_contact_no') }}" maxlength="20" placeholder="+91 9XXXXXXXXX"
                   class="w-full px-4 py-2.5 rounded-lg border @error('personal_contact_no') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('personal_contact_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="appointment_no">Appointment Contact No.</label>
            <input id="appointment_no" name="appointment_no" type="text"
                   value="{{ old('appointment_no') }}" maxlength="20" placeholder="+91 9XXXXXXXXX"
                   class="w-full px-4 py-2.5 rounded-lg border @error('appointment_no') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('appointment_no')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Card 4: Qualifications --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Qualifications</h2>
    @if ($qualifications->isEmpty())
        <p class="text-gray-400 text-sm">No active qualifications found.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            @foreach ($qualifications as $qual)
            <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-gray-50 transition">
                <input type="checkbox" name="qualifications[]" value="{{ $qual->id }}"
                       {{ in_array($qual->id, $selectedQualifications) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-mono font-semibold text-gray-700">{{ $qual->qualification }}</span>
            </label>
            @endforeach
        </div>
    @endif
    @error('qualifications')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

{{-- Card 5: Services --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Services</h2>
    @if ($services->isEmpty())
        <p class="text-gray-400 text-sm">No active services found.</p>
    @else
        <div class="space-y-5">
            @foreach ($services as $parent)
                @if ($parent->children->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">{{ $parent->service }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 pl-1">
                            @foreach ($parent->children as $child)
                            <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-gray-50 transition">
                                <input type="checkbox" name="services[]" value="{{ $child->id }}"
                                       {{ in_array($child->id, $selectedServices) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ $child->service }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                @else
                    <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-gray-50 transition w-fit">
                        <input type="checkbox" name="services[]" value="{{ $parent->id }}"
                               {{ in_array($parent->id, $selectedServices) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">{{ $parent->service }}</span>
                    </label>
                @endif
            @endforeach
        </div>
    @endif
    @error('services')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
</div>

{{-- Card 6: Coordinates --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">
        Coordinates <span class="text-gray-400 text-xs font-normal">(optional)</span>
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 max-w-md">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="latitude">Latitude</label>
            <input id="latitude" name="latitude" type="number" step="any"
                   value="{{ old('latitude') }}" placeholder="e.g. 19.0760"
                   class="w-full px-4 py-2.5 rounded-lg border @error('latitude') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('latitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="longitude">Longitude</label>
            <input id="longitude" name="longitude" type="number" step="any"
                   value="{{ old('longitude') }}" placeholder="e.g. 72.8777"
                   class="w-full px-4 py-2.5 rounded-lg border @error('longitude') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('longitude')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

{{-- Card 7: Average Rating --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Average Rating</h2>
    <div class="max-w-xs">
        <input type="text" value="0.00" readonly
               class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-500">
        <p class="text-xs text-gray-400 mt-1">Ratings are managed by platform activity.</p>
    </div>
</div>

{{-- Card 7: Status --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Status</h2>
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', '1') === '1' ? 'checked' : '' }}
               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <div>
            <p class="text-sm font-medium text-gray-700">Active</p>
            <p class="text-xs text-gray-400">Listing is live and visible via the API.</p>
        </div>
    </label>
</div>

{{-- Submit --}}
<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        Save Listing
    </button>
    <a href="{{ route('listings.index') }}"
       class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const countrySelect = document.getElementById('country_code');
    const citySelect = document.getElementById('master_city_id');
    const locationSelect = document.getElementById('master_location_id');

    if (!countrySelect || !citySelect || !locationSelect) return;

    countrySelect.addEventListener('change', function () {
        const countryCode = this.value;

        citySelect.innerHTML = '<option value="">Select City</option>';
        locationSelect.innerHTML = '<option value="">Select Location</option>';

        if (!countryCode) return;

        fetch('/listings-cities/' + countryCode)
            .then(response => response.json())
            .then(cities => {
                cities.forEach(city => {
                    citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                });
            })
            .catch(error => console.error('City loading error:', error));
    });

    citySelect.addEventListener('change', function () {
        const cityId = this.value;

        locationSelect.innerHTML = '<option value="">Select Location</option>';

        if (!cityId) return;

        fetch('/listings-locations/' + cityId)
            .then(response => response.json())
            .then(locations => {
                locations.forEach(location => {
                    locationSelect.innerHTML += `<option value="${location.id}">${location.location}</option>`;
                });
            })
            .catch(error => console.error('Location loading error:', error));
    });
});
</script>
@endpush
