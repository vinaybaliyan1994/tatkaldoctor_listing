@extends('layouts.admin')

@section('title', 'Global Settings — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Global Settings</h1>
        <p class="text-gray-500 text-sm mt-1">Site-wide configuration keys and values.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('settings.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Setting
    </a>
    @endif
</div>

@include('partials.alerts')

<form method="GET" action="{{ route('settings.index') }}"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Group</label>
        <select name="group"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="">All Groups</option>
            @foreach ($groups as $group)
                <option value="{{ $group }}" {{ request('group') === $group ? 'selected' : '' }}>{{ ucfirst($group) }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Visibility</label>
        <select name="is_public"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="">All</option>
            <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>Public</option>
            <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>Private</option>
        </select>
    </div>

    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Key or value…"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['group', 'is_public', 'search']))
        <a href="{{ route('settings.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    @if ($settings->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-sm">No settings found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Key</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Value</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Group</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Public</th>
                    <th class="px-4 py-3 w-28"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($settings as $setting)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-blue-700">{{ $setting->key }}</td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate">
                        @if (strlen($setting->value ?? '') > 60)
                            {{ substr($setting->value, 0, 60) }}…
                        @else
                            {{ $setting->value ?? '—' }}
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $setting->type }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $setting->group ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if ($setting->is_public)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Public</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-400">Private</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('settings.show', $setting) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            @if (Auth::user()->isSuperAdmin())
                            <a href="{{ route('settings.edit', $setting) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('settings.destroy', $setting) }}"
                                  onsubmit="return confirm('Delete setting \'{{ addslashes($setting->key) }}\'?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>

        @if ($settings->hasPages())
        <div class="px-4 py-4 border-t border-gray-100">
            {{ $settings->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
