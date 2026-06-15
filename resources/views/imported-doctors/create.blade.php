@extends('layouts.admin')

@section('title', 'Add Imported Doctor — TatkalDoctor Admin')

@section('content')
<div class="mb-6">
    <a href="{{ route('imported-doctors.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Imported Doctors</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Add Imported Doctor</h1>
    <p class="text-slate-500 text-sm mt-1">Display-only profile. No account, subscription, verification, booking, or QR is created.</p>
</div>

@include('partials.alerts')

<form method="POST" action="{{ route('imported-doctors.store') }}" class="space-y-5">
    @csrf
    @include('imported-doctors._form')
    <div class="flex items-center gap-3">
        <button class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">Save Imported Doctor</button>
        <a href="{{ route('imported-doctors.index') }}" class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">Cancel</a>
    </div>
</form>
@endsection
