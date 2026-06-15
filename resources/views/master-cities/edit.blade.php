@extends('layouts.admin')

@section('title', 'Edit ' . $masterCity->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('master-cities.index') }}" class="hover:text-slate-700">Master Cities</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Edit {{ $masterCity->name }}</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Edit City</h1>
    <p class="text-slate-500 text-sm mb-6">Update the city details below.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('master-cities.update', $masterCity) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="country_code">
                    Country <span class="text-red-500">*</span>
                </label>
                <select id="country_code" name="country_code"
                        class="w-full px-4 py-2.5 rounded-xl border @error('country_code') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
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

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                    City Name <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $masterCity->name) }}"
                       class="w-full px-4 py-2.5 rounded-xl border @error('name') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="1"
                               {{ old('status', $masterCity->status ? '1' : '0') === '1' ? 'checked' : '' }}
                               class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="0"
                               {{ old('status', $masterCity->status ? '1' : '0') === '0' ? 'checked' : '' }}
                               class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-slate-700">Inactive</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('master-cities.index') }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
