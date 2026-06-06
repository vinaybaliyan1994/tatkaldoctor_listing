@extends('layouts.admin')

@section('title', 'Edit Location — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-locations.index') }}" class="hover:text-gray-700">Master Locations</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit Location</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Edit Location</h1>
    <p class="text-gray-500 text-sm mb-6">Update the location name or status.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-locations.update', $masterLocation) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- City — read-only display --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-50">
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 font-mono font-semibold text-xs rounded">
                        {{ $masterLocation->city->country_code }}
                    </span>
                    <span class="text-sm text-gray-700">{{ $masterLocation->city->name }}</span>
                    <span class="text-xs text-gray-400 ml-1">— {{ $masterLocation->city->country->name }}</span>
                </div>
                <input type="hidden" name="master_city_id" value="{{ $masterLocation->master_city_id }}">
            </div>

            {{-- Location name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="location">
                    Location Name <span class="text-red-500">*</span>
                </label>
                <input id="location" name="location" type="text"
                       value="{{ old('location', $masterLocation->location) }}"
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
                           {{ old('status', $masterLocation->status ? '1' : '0') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Update Location
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
