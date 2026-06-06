@extends('layouts.admin')

@section('title', $setting->key . ' — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('settings.index') }}" class="hover:text-gray-700">Settings</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium font-mono">{{ $setting->key }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('settings.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('settings.edit', $setting) }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Edit
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800 font-mono">{{ $setting->key }}</h1>
            <div class="flex items-center gap-2 mt-2">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $setting->type }}</span>
                @if ($setting->group)
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">{{ $setting->group }}</span>
                @endif
                @if ($setting->is_public)
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Public API</span>
                @else
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">Private</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Value</h2>
    @if ($setting->value !== null && $setting->value !== '')
        <pre class="text-sm text-gray-800 font-mono bg-gray-50 rounded-lg p-4 whitespace-pre-wrap break-words">{{ $setting->value }}</pre>
    @else
        <p class="text-gray-400 text-sm italic">Empty value</p>
    @endif
    <p class="text-xs text-gray-400 mt-3">Last updated: {{ $setting->updated_at->format('d M Y H:i') }}</p>
</div>

@endsection
