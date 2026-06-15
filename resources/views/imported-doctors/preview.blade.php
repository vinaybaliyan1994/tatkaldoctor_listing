@extends('layouts.admin')

@section('title', 'Preview Imported Doctors — TatkalDoctor Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('imported-doctors.upload') }}" class="text-sm text-slate-500 hover:text-slate-700">Bulk Import</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">Preview Import</h1>
    </div>
    <form method="POST" action="{{ route('imported-doctors.import') }}">
        @csrf
        <button class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors {{ $summary['importable'] ? '' : 'opacity-50 cursor-not-allowed' }}"
                {{ $summary['importable'] ? '' : 'disabled' }}>
            Confirm Import
        </button>
    </form>
</div>

<div class="mb-5 grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach (['total_rows' => 'Total', 'importable' => 'Importable', 'duplicates' => 'Duplicates', 'errors' => 'Errors'] as $key => $label)
    <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <p class="text-xs text-slate-500">{{ $label }}</p>
        <p class="text-xl font-bold text-slate-800 tabular-nums">{{ $summary[$key] ?? 0 }}</p>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Row</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Doctor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Clinic</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Result</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($preview as $item)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-500">{{ $item['row_number'] }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $item['row']['doctor_name'] ?: '-' }}</p>
                        <p class="text-xs text-teal-600">{{ $item['row']['speciality'] ?: '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">
                        <p>{{ $item['row']['clinic_name'] ?: '-' }}</p>
                        <p class="text-xs text-slate-400">{{ $item['row']['clinic_address'] ?: '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $item['row']['status'] }}</td>
                    <td class="px-4 py-3">
                        @if($item['will_import'])
                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Will import</span>
                        @elseif($item['duplicate_listing_id'])
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Duplicate listing #{{ $item['duplicate_listing_id'] }}</span>
                        @elseif($item['duplicate_in_file'])
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Duplicate in file</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ implode(', ', $item['errors']) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
