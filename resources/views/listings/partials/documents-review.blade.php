@php
    $documentListing = $documentListing ?? $listing;
    $documents = $documentListing->documents ?? collect();
    $docCount = $documents->count();
    $pendingDocs = $documents->where('status', 'pending')->count();
    $approvedDocs = $documents->where('status', 'approved')->count();
    $rejectedDocs = $documents->where('status', 'rejected')->count();
@endphp

<div id="documents" class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 {{ $sectionClass ?? 'lg:col-span-2' }} scroll-mt-6">
    <div class="flex items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Documents</h2>
            <div class="mt-2 flex flex-wrap gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $docCount }} total</span>
                @if ($pendingDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendingDocs }} pending</span>
                @endif
                @if ($approvedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $approvedDocs }} approved</span>
                @endif
                @if ($rejectedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">{{ $rejectedDocs }} rejected</span>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('doctor-documents.index', $documentListing) }}"
               class="text-xs text-teal-600 hover:text-teal-800 font-medium">Review Page</a>
            <a href="{{ route('doctor-documents.create', $documentListing) }}"
               class="text-xs text-white bg-teal-600 hover:bg-teal-700 px-2 py-1 rounded-lg font-medium">+ Upload</a>
        </div>
    </div>

    @if ($documentListing->id !== $listing->id)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800">
            Documents are attached to duplicate listing #{{ $documentListing->id }}. Review actions below update those document records.
        </div>
    @endif

    @if ($documents->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-200 p-8 text-center">
            <p class="text-slate-400 text-sm">No documents uploaded yet.</p>
        </div>
    @else
        <div class="overflow-x-auto border border-slate-100 rounded-xl">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Document Type</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Uploaded At</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">File</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Remarks</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($documents as $doc)
                    @php
                        $fileUrl = Storage::disk('public')->url($doc->file_path);
                        $mime = $doc->mime_type ?? '';
                        $isImage = str_starts_with($mime, 'image/');
                        $isPdf = $mime === 'application/pdf' || str_ends_with(strtolower($doc->file_path), '.pdf');
                    @endphp
                    <tr class="align-top">
                        <td class="px-3 py-3">
                            <div class="font-medium text-slate-800">{{ $doc->document_type_label }}</div>
                            <div class="text-xs text-slate-400">ID #{{ $doc->id }}</div>
                        </td>
                        <td class="px-3 py-3">
                            @if($doc->status === 'approved')
                                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                            @elseif($doc->status === 'rejected')
                                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">Rejected</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Pending</span>
                            @endif
                            @if($doc->verifiedBy)
                                <div class="text-xs text-slate-400 mt-1">by {{ $doc->verifiedBy->name }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-slate-500 text-xs whitespace-nowrap">
                            {{ $doc->created_at?->format('d M Y, H:i') ?? '-' }}
                        </td>
                        <td class="px-3 py-3 min-w-56">
                            <div class="flex items-center gap-3">
                                @if($isImage)
                                    <a href="{{ $fileUrl }}" target="_blank" class="block flex-shrink-0">
                                        <img src="{{ $fileUrl }}"
                                             alt="{{ $doc->original_name ?? $doc->document_type_label }}"
                                             class="h-14 w-14 rounded-lg object-cover border border-slate-200">
                                    </a>
                                @elseif($isPdf)
                                    <a href="{{ $fileUrl }}" target="_blank"
                                       class="h-14 w-14 rounded-lg border border-red-100 bg-red-50 text-red-600 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        PDF
                                    </a>
                                @else
                                    <div class="h-14 w-14 rounded-lg border border-slate-200 bg-slate-50 text-slate-400 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        FILE
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ $fileUrl }}" target="_blank"
                                       class="text-teal-600 hover:text-teal-800 font-medium break-all">
                                        {{ $doc->original_name ?? basename($doc->file_path) }}
                                    </a>
                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $doc->file_size ? number_format($doc->file_size / 1024, 1) . ' KB' : 'Size unknown' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 text-slate-600 min-w-48">
                            {{ $doc->remarks ?: '-' }}
                        </td>
                        <td class="px-3 py-3 min-w-72">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('doctor-documents.show', $doc) }}"
                                   class="text-xs text-teal-600 hover:text-teal-800 font-medium">View</a>
                                <a href="{{ route('doctor-documents.download', $doc) }}"
                                   class="text-xs text-slate-600 hover:text-slate-800 font-medium">Download</a>
                                <a href="{{ route('doctor-documents.verify', $doc) }}"
                                   class="text-xs text-amber-600 hover:text-amber-800 font-medium">Review</a>
                            </div>
                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <form method="POST" action="{{ route('doctor-documents.update-status', $doc) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <input type="hidden" name="redirect_to" value="listing_documents">
                                    <button type="submit"
                                            class="w-full px-2 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded-lg transition-colors">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('doctor-documents.update-status', $doc) }}" class="space-y-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="hidden" name="redirect_to" value="listing_documents">
                                    <input type="text" name="remarks" required maxlength="500"
                                           placeholder="Reject remarks"
                                           class="w-full px-2 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-xs focus:ring-2 focus:ring-red-400 focus:border-transparent">
                                    <button type="submit"
                                            class="w-full px-2 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-lg transition-colors">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
