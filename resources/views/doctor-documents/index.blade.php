@extends('layouts.admin')

@section('title', 'Documents — ' . $listing->name)

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
        <span class="text-gray-800 font-medium">Documents</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('listings.show', $listing) }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isAdmin())
        <a href="{{ route('doctor-documents.create', $listing) }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            + Upload Document
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">Documents for {{ $listing->name }}</h2>
        <span class="text-sm text-gray-500">{{ $documents->count() }} document(s)</span>
    </div>

    @if ($documents->isEmpty())
        <div class="p-10 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">No documents uploaded yet.</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">File</th>
                    <th class="px-5 py-3">Size</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Verified By</th>
                    <th class="px-5 py-3">Uploaded</th>
                    <th class="px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($documents as $doc)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-medium text-gray-800">{{ $doc->document_type_label }}</td>
                    <td class="px-5 py-3 text-gray-600 max-w-xs truncate">{{ $doc->original_name ?? basename($doc->file_path) }}</td>
                    <td class="px-5 py-3 text-gray-500">
                        {{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : '—' }}
                    </td>
                    <td class="px-5 py-3">
                        @if ($doc->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                            </span>
                        @elseif ($doc->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-600">{{ $doc->verifiedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $doc->created_at->format('d M Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('doctor-documents.show', $doc) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            @if (Auth::user()->isAdmin())
                            <a href="{{ route('doctor-documents.verify', $doc) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Verify</a>
                            <a href="{{ route('doctor-documents.download', $doc) }}"
                               class="text-xs text-gray-600 hover:text-gray-800 font-medium">Download</a>
                            <form method="POST" action="{{ route('doctor-documents.destroy', $doc) }}"
                                  onsubmit="return confirm('Delete this document?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
