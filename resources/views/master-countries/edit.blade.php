@extends('layouts.admin')

@section('title', 'Edit ' . $masterCountry->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-countries.index') }}" class="hover:text-gray-700">Master Countries</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit {{ $masterCountry->name }}</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Edit Country</h1>
    <p class="text-gray-500 text-sm mb-6">
        The country code <span class="font-mono font-semibold text-gray-700">{{ $masterCountry->code }}</span> cannot be changed. Update the name below.
    </p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-countries.update', $masterCountry) }}" class="space-y-5">
            @csrf @method('PUT')

            {{-- Code (read-only) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country Code</label>
                <div class="flex items-center gap-3">
                    <span class="inline-block px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-mono font-semibold text-gray-500 tracking-widest">
                        {{ $masterCountry->code }}
                    </span>
                    <span class="text-xs text-gray-400">Code is fixed and cannot be edited.</span>
                </div>
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                    Country Name <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $masterCountry->name) }}"
                       class="w-full px-4 py-2.5 rounded-lg border @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                              text-sm text-gray-900
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('master-countries.index') }}"
                   class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
