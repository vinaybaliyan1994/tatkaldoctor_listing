@extends('layouts.admin')

@section('title', 'Edit Location — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('master-locations.index') }}" class="hover:text-slate-700">Master Locations</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Edit Location</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Edit Location</h1>
    <p class="text-slate-500 text-sm mb-6">Update the location name or status.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('master-locations.update', $masterLocation) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">City</label>
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50">
                    <span class="px-1.5 py-0.5 bg-teal-50 text-teal-700 font-mono font-semibold text-xs rounded">
                        {{ $masterLocation->city->country_code }}
                    </span>
                    <span class="text-sm text-slate-700">{{ $masterLocation->city->name }}</span>
                    <span class="text-xs text-slate-400 ml-1">— {{ $masterLocation->city->country->name }}</span>
                </div>
                <input type="hidden" name="master_city_id" value="{{ $masterLocation->master_city_id }}">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="location">
                    Location Name <span class="text-red-500">*</span>
                </label>
                <input id="location" name="location" type="text"
                       value="{{ old('location', $masterLocation->location) }}"
                       maxlength="191"
                       class="w-full px-4 py-2.5 rounded-xl border @error('location') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @error('location')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', $masterLocation->status ? '1' : '0') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500">
                    <span class="text-sm text-slate-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
                    Update Location
                </button>
                <a href="{{ route('master-locations.index') }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
