@extends('layouts.admin')

@section('title', 'Listing Audit Logs')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Listing Audit Logs</h1>
</div>

@include('partials.alerts')

{{-- Filters --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-5">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Listing</label>
            <select name="listing_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent min-w-[180px]">
                <option value="">All listings</option>
                @foreach ($listings as $lst)
                    <option value="{{ $lst->id }}" {{ request('listing_id') == $lst->id ? 'selected' : '' }}>
                        {{ $lst->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
            <select name="action"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="all">All actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                        {{ $action }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                Filter
            </button>
            <a href="{{ route('listing-audit-logs.index') }}"
               class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">Audit Trail</h2>
        <span class="text-sm text-gray-500">{{ $logs->total() }} entries</span>
    </div>

    @if ($logs->isEmpty())
        <div class="p-10 text-center text-gray-400">
            <p class="text-sm">No audit log entries found.</p>
        </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">
                    <th class="px-5 py-3">ID</th>
                    <th class="px-5 py-3">Listing</th>
                    <th class="px-5 py-3">Action</th>
                    <th class="px-5 py-3">Changed By</th>
                    <th class="px-5 py-3">IP</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($logs as $log)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-gray-400 text-xs font-mono">{{ $log->id }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('listings.show', $log->listing) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium truncate block max-w-[180px]">
                            {{ $log->listing->name }}
                        </a>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-mono
                            @if(str_contains($log->action, 'verif')) bg-amber-50 text-amber-700
                            @elseif(str_contains($log->action, 'creat')) bg-emerald-50 text-emerald-700
                            @elseif(str_contains($log->action, 'delet')) bg-red-50 text-red-600
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $log->changedBy?->name ?? 'System' }}</td>
                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-5 py-3 text-gray-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('listing-audit-logs.show', $log) }}"
                           class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($logs->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
    @endif
</div>

@endsection
