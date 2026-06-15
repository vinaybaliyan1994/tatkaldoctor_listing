@extends('layouts.admin')

@section('title', 'Imported Doctors - TatkalDoctor Admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Imported Doctors</h1>
        <p class="text-slate-500 text-sm mt-1">Display-only Google Business doctor profiles.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('imported-doctors.upload') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">Bulk Import</a>
        <a href="{{ route('imported-doctors.create') }}" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">Add Imported Doctor</a>
    </div>
</div>

@include('partials.alerts')

@if (session('import_summary'))
@php
    $summary = session('import_summary');
    $summaryCards = [
        'total_rows'  => ['Total Rows',  'border-l-slate-400',   'text-slate-700'],
        'imported'    => ['Imported',    'border-l-teal-500',    'text-teal-700'],
        'skipped'     => ['Skipped',     'border-l-amber-500',   'text-amber-700'],
        'duplicates'  => ['Duplicates',  'border-l-orange-400',  'text-orange-700'],
        'errors'      => ['Errors',      'border-l-red-500',     'text-red-700'],
    ];
@endphp
<div class="mb-5 grid grid-cols-2 md:grid-cols-5 gap-3">
    @foreach ($summaryCards as $key => [$label, $border, $numColor])
    <div class="bg-white border border-slate-100 border-l-4 {{ $border }} rounded-xl shadow-sm p-4">
        <p class="text-[11px] font-medium text-slate-500 leading-tight">{{ $label }}</p>
        <p class="text-2xl font-black {{ $numColor }} leading-none mt-1.5 tabular-nums">{{ $summary[$key] ?? 0 }}</p>
    </div>
    @endforeach
</div>
@endif

<form method="GET" action="{{ route('imported-doctors.index') }}" class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="flex-1 min-w-64">
        <label class="block text-xs font-medium text-slate-500 mb-1">Search</label>
        <input name="search" value="{{ request('search') }}" placeholder="Doctor, speciality, clinic, city…"
               class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
    </div>
    <button class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">Filter</button>
    @if(request()->hasAny(['status', 'search']))
    <a href="{{ route('imported-doctors.index') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">Clear</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if($doctors->isEmpty())
    <div class="py-16 text-center text-slate-400">
        <p class="text-sm">No imported doctors found.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Doctor</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Clinic</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Mobile</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($doctors as $doctor)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $doctor->id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $doctor->name }}</p>
                        <p class="text-xs text-teal-600 mt-1">{{ $doctor->meta_data['speciality'] ?? '-' }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $doctor->hospital_name ?: '-' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ implode(', ', array_filter([$doctor->location?->location ?? ($doctor->meta_data['location_name'] ?? null), $doctor->city?->name ?? ($doctor->meta_data['city_name'] ?? null)])) ?: '-' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $doctor->personal_contact_no ?: '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $doctor->status ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                            {{ $doctor->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('imported-doctors.show', $doctor) }}" class="text-xs font-medium text-teal-600 hover:text-teal-800">View</a>
                        <a href="{{ route('imported-doctors.edit', $doctor) }}" class="ml-3 text-xs font-medium text-amber-600 hover:text-amber-800">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($doctors->hasPages())
    <div class="px-4 py-4 border-t border-slate-100">{{ $doctors->links() }}</div>
    @endif
    @endif
</div>
@endsection
