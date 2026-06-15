@extends('layouts.admin')

@section('title', 'Edit ' . $client->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('clients.index') }}" class="hover:text-slate-700">Clients</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('clients.show', $client) }}" class="hover:text-slate-700">{{ $client->name }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Edit</span>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">Edit Client</h1>
    <p class="text-slate-500 text-sm mb-6">Update name, availability window, or status. API keys are not changed here.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1" for="name">Client Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $client->name) }}"
                       class="w-full px-4 py-2.5 rounded-xl border @error('name') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="avail_from_date">Available From</label>
                    <input id="avail_from_date" name="avail_from_date" type="date"
                           value="{{ old('avail_from_date', $client->avail_from_date?->toDateString()) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                    <p class="mt-1 text-xs text-slate-400">Leave blank for no start restriction.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1" for="avail_to_date">Available To</label>
                    <input id="avail_to_date" name="avail_to_date" type="date"
                           value="{{ old('avail_to_date', $client->avail_to_date?->toDateString()) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                    <p class="mt-1 text-xs text-slate-400">Leave blank for no expiry.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Status <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="active"
                               {{ old('status', $client->status) === 'active' ? 'checked' : '' }}
                               class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-slate-700">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="inactive"
                               {{ old('status', $client->status) === 'inactive' ? 'checked' : '' }}
                               class="text-teal-600 focus:ring-teal-500">
                        <span class="text-sm text-slate-700">Inactive</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('clients.show', $client) }}"
                   class="px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
