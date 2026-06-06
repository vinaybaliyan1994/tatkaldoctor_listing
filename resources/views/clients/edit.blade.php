@extends('layouts.admin')

@section('title', 'Edit ' . $client->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('clients.index') }}" class="hover:text-gray-700">Clients</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('clients.show', $client) }}" class="hover:text-gray-700">{{ $client->name }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit</span>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Edit Client</h1>
    <p class="text-gray-500 text-sm mb-6">Update name, availability window, or status. API keys are not changed here.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-5">
            @csrf @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="name">Client Name <span class="text-red-500">*</span></label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $client->name) }}"
                       class="w-full px-4 py-2.5 rounded-lg border @error('name') border-red-400 @else border-gray-300 @enderror text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="avail_from_date">Available From</label>
                    <input id="avail_from_date" name="avail_from_date" type="date"
                           value="{{ old('avail_from_date', $client->avail_from_date?->toDateString()) }}"
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="mt-1 text-xs text-gray-400">Leave blank for no start restriction.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="avail_to_date">Available To</label>
                    <input id="avail_to_date" name="avail_to_date" type="date"
                           value="{{ old('avail_to_date', $client->avail_to_date?->toDateString()) }}"
                           class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    <p class="mt-1 text-xs text-gray-400">Leave blank for no expiry.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="active"
                               {{ old('status', $client->status) === 'active' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status" value="inactive"
                               {{ old('status', $client->status) === 'inactive' ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700">Inactive</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('clients.show', $client) }}"
                   class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
