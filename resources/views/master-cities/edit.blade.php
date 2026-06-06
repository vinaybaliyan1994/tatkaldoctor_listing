@extends('layouts.admin')

@section('title', 'Edit ' . $masterCity->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-cities.index') }}" class="hover:text-gray-700">Master Cities</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit {{ $masterCity->name }}</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Edit City</h1>
    <p class="text-gray-500 text-sm mb-6">Update the city details below.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-cities.update', $masterCity) }}" class="space-y-5">
            @csrf @method('PUT')

            {{-- Country --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="country_code">
                    Country <span class="text-red-500">*</span>
                </label>
                <select id="country_code" name="country_code"
                        class="w-full px-4 py-2.5 rounded-lg border @error('country_code') border-red-400 bg-red-50 @else border-gray-300 @enderror
                               text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <option value="">— Select country —</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->code }}"
                            {{ old('country_code', $masterCity->country_code) === $country->code ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->code }})
                        </option>
                    @endforeach
                </select>
                @error('country_code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                    City Name <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $masterCity->name) }}"
                       class="w-full px-4 py-2.5 rounded-lg border @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                              text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="1"
                               {{ old('status', $masterCity->status ? '1' : '0') === '1' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="0"
                               {{ old('status', $masterCity->status ? '1' : '0') === '0' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Inactive</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('master-cities.index') }}"
                   class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
