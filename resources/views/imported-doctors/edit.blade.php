@extends('layouts.admin')

@section('title', 'Edit Imported Doctor — TatkalDoctor Admin')

@section('content')
<div class="mb-6">
    <a href="{{ route('imported-doctors.show', $doctor) }}" class="text-sm text-slate-500 hover:text-slate-700">{{ $doctor->name }}</a>
    <h1 class="text-2xl font-bold text-slate-800 mt-2">Edit Imported Doctor</h1>
</div>

@include('partials.alerts')

<form method="POST" action="{{ route('imported-doctors.update', $doctor) }}" class="space-y-5">
    @csrf @method('PUT')
    @include('imported-doctors._form', ['doctor' => $doctor])
    <div class="flex items-center gap-3">
        <button class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">Update Imported Doctor</button>
        <a href="{{ route('imported-doctors.show', $doctor) }}" class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">Cancel</a>
    </div>
</form>
@endsection
