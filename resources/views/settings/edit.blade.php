@extends('layouts.admin')

@section('title', 'Edit Setting — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('settings.index') }}" class="hover:text-gray-700">Settings</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('settings.show', $setting) }}" class="hover:text-gray-700 font-mono text-xs">{{ $setting->key }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit</span>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-1">Edit Setting</h1>
<p class="text-gray-500 text-sm mb-6 font-mono">{{ $setting->key }}</p>

@include('partials.alerts')

<form method="POST" action="{{ route('settings.update', $setting) }}" class="space-y-5">
@csrf
@method('PUT')

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Setting Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="key">
                Key <span class="text-red-500">*</span>
            </label>
            <input id="key" name="key" type="text" value="{{ old('key', $setting->key) }}" maxlength="191" required
                   class="w-full px-4 py-2.5 rounded-lg border @error('key') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="type">Type</label>
            <select id="type" name="type"
                    class="w-full px-4 py-2.5 rounded-lg border @error('type') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                @foreach (['string', 'text', 'boolean', 'integer', 'float', 'json'] as $t)
                <option value="{{ $t }}" {{ old('type', $setting->type) === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
            @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="group">
                Group <span class="text-gray-400 text-xs font-normal">(optional)</span>
            </label>
            <input id="group" name="group" type="text" value="{{ old('group', $setting->group) }}" maxlength="100"
                   class="w-full px-4 py-2.5 rounded-lg border @error('group') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('group')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="value">Value</label>
            <textarea id="value" name="value" rows="4"
                      class="w-full px-4 py-2.5 rounded-lg border @error('value') border-red-400 bg-red-50 @else border-gray-300 @enderror
                             text-sm text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-y">{{ old('value', $setting->value) }}</textarea>
            @error('value')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-4 pb-3 border-b border-gray-100">Visibility</h2>
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="is_public" value="1"
               {{ old('is_public', $setting->is_public ? '1' : '0') === '1' ? 'checked' : '' }}
               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
        <div>
            <p class="text-sm font-medium text-gray-700">Public</p>
            <p class="text-xs text-gray-400">Expose this setting via the public API endpoint.</p>
        </div>
    </label>
</div>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        Update Setting
    </button>
    <a href="{{ route('settings.show', $setting) }}"
       class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection
