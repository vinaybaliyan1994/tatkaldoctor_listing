@extends('layouts.admin')

@section('title', 'Add Qualification — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('master-qualifications.index') }}" class="hover:text-slate-700">Master Qualifications</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Add Qualification</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Add Qualification</h1>
    <p class="text-slate-500 text-sm mb-6">Enter the qualification acronym (e.g. MBBS, MD, BDS).</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('master-qualifications.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="qualification">
                    Qualification <span class="text-red-500">*</span>
                </label>
                <input id="qualification" name="qualification" type="text"
                       value="{{ old('qualification') }}"
                       placeholder="e.g. MBBS"
                       maxlength="191"
                       oninput="this.value = this.value.toUpperCase()"
                       class="w-full px-4 py-2.5 rounded-xl border @error('qualification') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @error('qualification')
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
                    Save Qualification
                </button>
                <a href="{{ route('master-qualifications.index') }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
