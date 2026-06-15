@extends('layouts.admin')

@section('title', $doctor->name . ' — Imported Doctor')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <a href="{{ route('imported-doctors.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Imported Doctors</a>
        <h1 class="text-2xl font-bold text-slate-800 mt-2">{{ $doctor->name }}</h1>
        <p class="text-slate-500 text-sm mt-1">Display-only Google Business import.</p>
    </div>
    <a href="{{ route('imported-doctors.edit', $doctor) }}"
       class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        Edit
    </a>
</div>

@include('partials.alerts')

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase mb-4">Profile</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Speciality</dt><dd class="text-slate-800">{{ $doctor->meta_data['speciality'] ?? '-' }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Clinic</dt><dd class="text-slate-800">{{ $doctor->hospital_name ?: '-' }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Mobile</dt><dd class="text-slate-800">{{ $doctor->personal_contact_no ?: '-' }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Status</dt><dd class="text-slate-800">{{ $doctor->status ? 'Active' : 'Inactive' }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Verified</dt><dd class="text-slate-800">No</dd></div>
        </dl>
    </div>
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase mb-4">Location</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Address</dt><dd class="text-slate-800">{{ $doctor->address ?: '-' }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">City</dt><dd class="text-slate-800">{{ $doctor->city?->name ?? ($doctor->meta_data['city_name'] ?? '-') }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Location</dt><dd class="text-slate-800">{{ $doctor->location?->location ?? ($doctor->meta_data['location_name'] ?? '-') }}</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Google URL</dt><dd class="text-slate-800 break-all">@if($doctor->external_url)<a class="text-teal-600 hover:text-teal-800" href="{{ $doctor->external_url }}" target="_blank">{{ $doctor->external_url }}</a>@else - @endif</dd></div>
            <div class="flex gap-3"><dt class="w-32 text-slate-500">Public Slug</dt><dd class="text-slate-800 font-mono text-xs">{{ $doctor->qr_slug ?: '-' }}</dd></div>
        </dl>
    </div>
</div>
@endsection
