@extends('layouts.admin')

@section('title', 'Upload Document — ' . $listing->name)

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('listings.index') }}" class="hover:text-gray-700">Listings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('listings.show', $listing) }}" class="hover:text-gray-700 truncate max-w-xs">{{ $listing->name }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('doctor-documents.index', $listing) }}" class="hover:text-gray-700">Documents</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">Upload</span>
    </div>
    <a href="{{ route('doctor-documents.index', $listing) }}"
       class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        ← Back
    </a>
</div>

@include('partials.alerts')

<div class="max-w-xl">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-5">Upload Document for {{ $listing->name }}</h2>

        <form method="POST" action="{{ route('doctor-documents.store', $listing) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Document Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Document Type <span class="text-red-500">*</span></label>
                <select name="document_type" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                               {{ $errors->has('document_type') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                    <option value="">— Select type —</option>
                    @foreach ($documentTypes as $value => $label)
                        <option value="{{ $value }}" {{ old('document_type') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('document_type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- File --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">File <span class="text-red-500">*</span></label>
                <input type="file" name="document" required accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                              {{ $errors->has('document') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <p class="mt-1 text-xs text-gray-400">PDF, JPG, JPEG or PNG. Max 5 MB.</p>
                @error('document')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remarks --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" placeholder="Optional notes..."
                          class="w-full border rounded-lg px-3 py-2 text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                                 {{ $errors->has('remarks') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Upload Document
                </button>
                <a href="{{ route('doctor-documents.index', $listing) }}"
                   class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
