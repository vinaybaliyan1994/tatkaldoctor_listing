@extends('layouts.admin')

@section('title', 'Document — ' . $doctorDocument->document_type_label)

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('listings.index') }}" class="hover:text-gray-700">Listings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('listings.show', $doctorDocument->listing) }}" class="hover:text-gray-700 truncate max-w-xs">
            {{ $doctorDocument->listing->name }}
        </a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('doctor-documents.index', $doctorDocument->listing) }}" class="hover:text-gray-700">Documents</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">{{ $doctorDocument->document_type_label }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('doctor-documents.index', $doctorDocument->listing) }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isAdmin())
        <a href="{{ route('doctor-documents.verify', $doctorDocument) }}"
           class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-lg transition-colors">
            Verify / Update
        </a>
        <a href="{{ route('doctor-documents.download', $doctorDocument) }}"
           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition-colors">
            Download
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

<div class="max-w-2xl space-y-5">

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $doctorDocument->document_type_label }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $doctorDocument->listing->name }}</p>
            </div>
            @if ($doctorDocument->status === 'approved')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                </span>
            @elseif ($doctorDocument->status === 'rejected')
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Review
                </span>
            @endif
        </div>
    </div>

    {{-- File details --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">File Details</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Original Name</dt>
                <dd class="text-gray-800">{{ $doctorDocument->original_name ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">MIME Type</dt>
                <dd class="text-gray-600 font-mono text-xs">{{ $doctorDocument->mime_type ?? '—' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">File Size</dt>
                <dd class="text-gray-800">
                    {{ $doctorDocument->file_size ? number_format($doctorDocument->file_size / 1024, 1) . ' KB' : '—' }}
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Uploaded</dt>
                <dd class="text-gray-800">{{ $doctorDocument->created_at->format('d M Y, H:i') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Verification --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Verification</h2>
        <dl class="space-y-3 text-sm">
            @if ($doctorDocument->verified_at)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Verified At</dt>
                <dd class="text-gray-800">{{ $doctorDocument->verified_at->format('d M Y, H:i') }}</dd>
            </div>
            @endif
            @if ($doctorDocument->verifiedBy)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Verified By</dt>
                <dd class="text-gray-800">{{ $doctorDocument->verifiedBy->name }}</dd>
            </div>
            @endif
            @if ($doctorDocument->remarks)
            <div class="flex gap-3">
                <dt class="w-36 text-gray-500 flex-shrink-0">Remarks</dt>
                <dd class="text-gray-700">{{ $doctorDocument->remarks }}</dd>
            </div>
            @endif
            @if (!$doctorDocument->verified_at && !$doctorDocument->remarks)
            <p class="text-gray-400">Not yet reviewed.</p>
            @endif
        </dl>
    </div>

    {{-- Danger zone --}}
    @if (Auth::user()->isAdmin())
    <div class="bg-white rounded-xl border border-red-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-red-600 uppercase tracking-wide mb-3">Danger Zone</h2>
        <form method="POST" action="{{ route('doctor-documents.destroy', $doctorDocument) }}"
              onsubmit="return confirm('Permanently delete this document and its file?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                Delete Document
            </button>
        </form>
    </div>
    @endif

</div>

@endsection
