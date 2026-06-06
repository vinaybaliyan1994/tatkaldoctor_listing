@extends('layouts.admin')

@section('title', 'Cities — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Master Cities</h1>
        <p class="text-gray-500 text-sm mt-1">Cities linked to their respective countries.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('master-cities.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add City
    </a>
    @endif
</div>

@include('partials.alerts')

{{-- Filters --}}
<form method="GET" action="{{ route('master-cities.index') }}"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-4">

    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-gray-500 mb-1">Country</label>
        <select name="country_code"
                class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="">All Countries</option>
            @foreach ($countries as $country)
                <option value="{{ $country->code }}" {{ request('country_code') === $country->code ? 'selected' : '' }}>
                    {{ $country->name }} ({{ $country->code }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="all"      {{ request('status', 'all') === 'all'      ? 'selected' : '' }}>All</option>
            <option value="active"   {{ request('status') === 'active'          ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive'        ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['country_code', 'status']))
        <a href="{{ route('master-cities.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    @if ($cities->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
            </svg>
            <p class="text-sm">No cities found.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('master-cities.create') }}" class="text-blue-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">City</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Country</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Added On</th>
                    @if (Auth::user()->isSuperAdmin())
                    <th class="px-6 py-3 w-28"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($cities as $city)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $city->id }}</td>
                    <td class="px-6 py-3.5 font-medium text-gray-800">{{ $city->name }}</td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-gray-600 text-xs">
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 font-mono font-semibold rounded">
                                {{ $city->country_code }}
                            </span>
                            {{ $city->country->name }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        @if ($city->status)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-3.5 text-gray-500 text-xs">{{ $city->created_at->format('d M Y') }}</td>
                    @if (Auth::user()->isSuperAdmin())
                    <td class="px-6 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('master-cities.edit', $city) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('master-cities.destroy', $city) }}"
                                  onsubmit="return confirm('Delete {{ $city->name }}? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($cities->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $cities->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
