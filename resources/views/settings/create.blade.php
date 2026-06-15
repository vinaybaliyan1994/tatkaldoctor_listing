@extends('layouts.admin')

@section('title', 'Add Setting — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('settings.index') }}" class="hover:text-slate-700">Settings</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Create</span>
</div>

<h1 class="text-2xl font-bold text-slate-800 mb-1">Add Setting</h1>
<p class="text-slate-500 text-sm mb-6">Create a new global configuration key.</p>

@include('partials.alerts')

<form method="POST" action="{{ route('settings.store') }}" class="space-y-5">
@csrf

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Setting Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="key">
                Key <span class="text-red-500">*</span>
            </label>
            <input id="key" name="key" type="text" value="{{ old('key') }}" maxlength="191" required
                   placeholder="site_name"
                   class="w-full px-4 py-2.5 rounded-xl border @error('key') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="type">Type</label>
            <select id="type" name="type"
                    class="w-full px-4 py-2.5 rounded-xl border @error('type') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @foreach (['string', 'text', 'boolean', 'integer', 'float', 'json'] as $t)
                <option value="{{ $t }}" {{ old('type', 'string') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="group">
                Group <span class="text-slate-400 text-xs font-normal">(optional)</span>
            </label>
            <input id="group" name="group" type="text" value="{{ old('group') }}" maxlength="100"
                   placeholder="general"
                   class="w-full px-4 py-2.5 rounded-xl border @error('group') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('group')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="value">Value</label>
            <textarea id="value" name="value" rows="4"
                      class="w-full px-4 py-2.5 rounded-xl border @error('value') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition resize-y">{{ old('value') }}</textarea>
            @error('value')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Visibility</h2>
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="is_public" value="1"
               {{ old('is_public') ? 'checked' : '' }}
               class="w-4 h-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500">
        <div>
            <p class="text-sm font-medium text-slate-700">Public</p>
            <p class="text-xs text-slate-400">Expose this setting via the public API endpoint.</p>
        </div>
    </label>
</div>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        Create Setting
    </button>
    <a href="{{ route('settings.index') }}"
       class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection
