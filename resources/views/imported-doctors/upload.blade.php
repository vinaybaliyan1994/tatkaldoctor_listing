@extends('layouts.admin')

@section('title', 'Bulk Import Imported Doctors - TatkalDoctor Admin')

@section('content')
<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('imported-doctors.index') }}" class="hover:text-slate-700 transition-colors">Imported Doctors</a>
    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-700 font-medium">Bulk Import</span>
</div>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Bulk Import</h1>
    <p class="text-slate-500 text-sm mt-1">Upload CSV or XLSX, review preview, then confirm import.</p>
</div>

@include('partials.alerts')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    {{-- Upload Form --}}
    <form method="POST" action="{{ route('imported-doctors.preview') }}" enctype="multipart/form-data"
          class="lg:col-span-2 bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        @csrf

        <h2 class="text-sm font-semibold text-slate-700 mb-4">Select File</h2>

        {{-- Drop zone --}}
        <label for="import_file"
               class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 hover:border-teal-400 hover:bg-teal-50/30 cursor-pointer transition-colors group">
            <svg class="w-10 h-10 text-slate-300 group-hover:text-teal-400 mb-3 transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            <p class="text-sm font-medium text-slate-600 group-hover:text-teal-600 transition-colors">Click to upload <span class="text-slate-400 font-normal">or drag and drop</span></p>
            <p class="text-xs text-slate-400 mt-1">CSV, TXT or XLSX</p>
            <input id="import_file" type="file" name="import_file" accept=".csv,.txt,.xlsx" required class="hidden">
        </label>

        {{-- Selected file name display --}}
        <p id="file-name-display" class="mt-2 text-xs text-slate-400 hidden">
            <span class="font-medium text-slate-600" id="file-name-text"></span>
        </p>

        @error('import_file')
        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $message }}
        </p>
        @enderror

        <div class="mt-5 flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Preview Import
            </button>
            <a href="{{ route('imported-doctors.index') }}"
               class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                Cancel
            </a>
        </div>
    </form>

    {{-- Format Guide --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 space-y-4">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Required Format</h2>

        <div>
            <p class="text-xs font-medium text-slate-500 mb-1.5">Column order</p>
            <pre class="text-[11px] bg-slate-50 border border-slate-100 rounded-xl p-3 overflow-auto text-slate-700 leading-relaxed">doctor_name
speciality
clinic_name
clinic_address
clinic_mobile
city
location
google_business_url
status</pre>
        </div>

        <div class="space-y-2 text-xs text-slate-500">
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-red-400 flex-shrink-0"></span>
                <span><strong class="text-slate-700">Required:</strong> doctor_name, speciality, clinic_address</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-amber-400 flex-shrink-0"></span>
                <span><strong class="text-slate-700">Status:</strong> active or inactive (defaults to active)</span>
            </div>
            <div class="flex items-start gap-2">
                <span class="mt-0.5 w-1.5 h-1.5 rounded-full bg-teal-400 flex-shrink-0"></span>
                <span>Duplicate rows (same doctor + clinic) are skipped automatically</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('import_file').addEventListener('change', function () {
    const display = document.getElementById('file-name-display');
    const text = document.getElementById('file-name-text');
    if (this.files.length) {
        text.textContent = this.files[0].name;
        display.classList.remove('hidden');
    } else {
        display.classList.add('hidden');
    }
});
</script>
@endpush
@endsection
