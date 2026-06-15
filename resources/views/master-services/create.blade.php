@extends('layouts.admin')

@section('title', 'Add Service — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('master-services.index') }}" class="hover:text-slate-700">Master Services</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Add Service</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Add Service</h1>
    <p class="text-slate-500 text-sm mb-6">Choose whether this is a main category or a sub-service of an existing one.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('master-services.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="parent_id">
                    Category Type
                </label>
                <select id="parent_id" name="parent_id"
                        class="w-full px-4 py-2.5 rounded-xl border @error('parent_id') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                    <option value="0" {{ old('parent_id', '0') === '0' ? 'selected' : '' }}>
                        — Main Category (no parent) —
                    </option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->service }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="service">
                    Service Name <span class="text-red-500">*</span>
                </label>
                <input id="service" name="service" type="text"
                       value="{{ old('service') }}"
                       placeholder="e.g. Cardiologist"
                       maxlength="191"
                       class="w-full px-4 py-2.5 rounded-xl border @error('service') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @error('service')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', '1') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500">
                    <span class="text-sm text-slate-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
                    Save Service
                </button>
                <a href="{{ route('master-services.index') }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
