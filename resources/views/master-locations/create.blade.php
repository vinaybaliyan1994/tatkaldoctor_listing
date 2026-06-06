@extends('layouts.admin')

@section('title', 'Add Location — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-locations.index') }}" class="hover:text-gray-700">Master Locations</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Add Location</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Add Location</h1>
    <p class="text-gray-500 text-sm mb-6">Select a city and enter the location name.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-locations.store') }}" class="space-y-5">
            @csrf

            {{-- City (grouped by country) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="master_city_id">
                    City <span class="text-red-500">*</span>
                </label>
                <select id="master_city_id" name="master_city_id" required
                        class="w-full px-4 py-2.5 rounded-lg border @error('master_city_id') border-red-400 bg-red-50 @else border-gray-300 @enderror
                               text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">— Select city —</option>
                    @foreach ($countries as $country)
                        @if ($country->cities->isNotEmpty())
                        <optgroup label="{{ $country->name }} ({{ $country->code }})">
                            @foreach ($country->cities as $city)
                                <option value="{{ $city->id }}"
                                        {{ old('master_city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </optgroup>
                        @endif
                    @endforeach
                </select>
                @error('master_city_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Location name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                    Location Name <span class="text-red-500">*</span>
                </label>
                <input id="location" name="location" type="text"
                       value="{{ old('location') }}"
                       placeholder="e.g. Bandra West"
                       maxlength="191"
                       class="w-full px-4 py-2.5 rounded-lg border @error('location') border-red-400 bg-red-50 @else border-gray-300 @enderror
                              text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('location')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', '1') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Location
                </button>
                <a href="{{ route('master-locations.index') }}"
                   class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
