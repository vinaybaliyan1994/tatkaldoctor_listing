@extends('layouts.admin')

@section('title', 'Locations — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Master Locations</h1>
        <p class="text-slate-500 text-sm mt-1">Locations linked to their respective cities.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('master-locations.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Location
    </a>
    @endif
</div>

@include('partials.alerts')

{{-- Filters --}}
<form method="GET" action="{{ route('master-locations.index') }}"
      class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-4">

    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-slate-500 mb-1">City</label>
        <select name="master_city_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Cities</option>
            @foreach ($countries as $country)
                @if ($country->cities->isNotEmpty())
                <optgroup label="{{ $country->name }} ({{ $country->code }})">
                    @foreach ($country->cities as $city)
                        <option value="{{ $city->id }}"
                                {{ request('master_city_id') == $city->id ? 'selected' : '' }}>
                            {{ $city->name }}
                        </option>
                    @endforeach
                </optgroup>
                @endif
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="all"      {{ request('status', 'all') === 'all'      ? 'selected' : '' }}>All</option>
            <option value="active"   {{ request('status') === 'active'          ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive'        ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['master_city_id', 'status']))
        <a href="{{ route('master-locations.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($locations->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm">No locations found.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('master-locations.create') }}" class="text-teal-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Location Name</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">City</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Country</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    @if (Auth::user()->isSuperAdmin())
                    <th class="px-6 py-3 w-28"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($locations as $loc)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-3.5 text-slate-400 text-xs">{{ $loc->id }}</td>
                    <td class="px-6 py-3.5 font-medium text-slate-800">{{ $loc->location }}</td>
                    <td class="px-6 py-3.5 text-slate-700">{{ $loc->city->name }}</td>
                    <td class="px-6 py-3.5">
                        <span class="inline-flex items-center gap-1.5 text-slate-600 text-xs">
                            <span class="px-1.5 py-0.5 bg-teal-50 text-teal-700 font-mono font-semibold rounded">
                                {{ $loc->city->country_code }}
                            </span>
                            {{ $loc->city->country->name }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5">
                        @if ($loc->status)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                            </span>
                        @endif
                    </td>
                    @if (Auth::user()->isSuperAdmin())
                    <td class="px-6 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('master-locations.edit', $loc) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('master-locations.destroy', $loc) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($loc->location) }}\'? This cannot be undone.')">
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

        @if ($locations->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $locations->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
