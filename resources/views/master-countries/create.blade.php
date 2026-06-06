@extends('layouts.admin')

@section('title', 'Add Country — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-countries.index') }}" class="hover:text-gray-700">Master Countries</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Add Country</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Add Country</h1>
    <p class="text-gray-500 text-sm mb-6">Use the ISO 3166-1 alpha-3 code (e.g. IND, USA, GBR).</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-countries.store') }}" class="space-y-5">
            @csrf

            {{-- Code --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="code">
                    Country Code <span class="text-red-500">*</span>
                    <span class="ml-1 text-xs text-gray-400 font-normal">(3 letters, ISO alpha-3)</span>
                </label>
                <input id="code" name="code" type="text"
                       value="{{ old('code') }}"
                       maxlength="3"
                       placeholder="IND"
                       style="text-transform:uppercase"
                       class="w-full px-4 py-2.5 rounded-lg border @error('code') border-red-400 bg-red-50 @else border-gray-300 @enderror
                              text-sm text-gray-900 font-mono tracking-widest uppercase placeholder-gray-300
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('code')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">
                    Country Name <span class="text-red-500">*</span>
                </label>
                <input id="name" name="name" type="text"
                       value="{{ old('name') }}"
                       placeholder="India"
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
                    Add Country
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

@push('scripts')
<script>
    // Auto-uppercase the code field as user types
    document.getElementById('code').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });
</script>
@endpush
