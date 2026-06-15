@extends('layouts.admin')

@section('title', 'Verify Document — ' . $doctorDocument->document_type_label)

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('listings.show', $doctorDocument->listing) }}" class="hover:text-slate-700 truncate max-w-xs">
            {{ $doctorDocument->listing->name }}
        </a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('doctor-documents.index', $doctorDocument->listing) }}" class="hover:text-slate-700">Documents</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-800 font-medium">Verify</span>
    </div>
    <a href="{{ route('doctor-documents.show', $doctorDocument) }}"
       class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
        ← Back
    </a>
</div>

@include('partials.alerts')

<div class="max-w-xl space-y-5">

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Document</h2>
        <dl class="space-y-2 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Type</dt>
                <dd class="text-slate-800 font-medium">{{ $doctorDocument->document_type_label }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">File</dt>
                <dd class="text-slate-700">{{ $doctorDocument->original_name ?? basename($doctorDocument->file_path) }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Current Status</dt>
                <dd>
                    @if ($doctorDocument->status === 'approved')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                        </span>
                    @elseif ($doctorDocument->status === 'rejected')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Rejected
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                        </span>
                    @endif
                </dd>
            </div>
        </dl>
        <div class="mt-4">
            <a href="{{ route('doctor-documents.download', $doctorDocument) }}"
               class="inline-flex items-center text-sm text-teal-600 hover:text-teal-800 font-medium">
                Download to review
                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-amber-700 uppercase tracking-wide mb-5">Update Verification Status</h2>

        <form method="POST" action="{{ route('doctor-documents.update-status', $doctorDocument) }}" class="space-y-5">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">New Status <span class="text-red-500">*</span></label>
                <select name="status" id="docStatus" required onchange="toggleDocRemarks(this.value)"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition {{ $errors->has('status') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">
                    <option value="pending"   {{ old('status', $doctorDocument->status) === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="approved"  {{ old('status', $doctorDocument->status) === 'approved'  ? 'selected' : '' }}>Approved</option>
                    <option value="rejected"  {{ old('status', $doctorDocument->status) === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                </select>
                @error('status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div id="remarksBlock">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                    Remarks <span id="remarksRequired" class="text-red-500 hidden">*</span>
                </label>
                <textarea name="remarks" rows="3" placeholder="Add notes or rejection reason..."
                          class="w-full px-4 py-2.5 rounded-xl border text-sm text-slate-800 resize-none focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-amber-400 transition {{ $errors->has('remarks') ? 'border-red-400 bg-red-50' : 'border-slate-200 bg-slate-50' }}">{{ old('remarks', $doctorDocument->remarks) }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium rounded-xl transition-colors">
                    Save Status
                </button>
                <a href="{{ route('doctor-documents.show', $doctorDocument) }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<script>
function toggleDocRemarks(status) {
    const req = document.getElementById('remarksRequired');
    if (status === 'rejected') {
        req.classList.remove('hidden');
    } else {
        req.classList.add('hidden');
    }
}
toggleDocRemarks(document.getElementById('docStatus').value);
</script>

@endsection
