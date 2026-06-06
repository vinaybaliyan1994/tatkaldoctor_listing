@extends('layouts.admin')

@section('title', 'API Logs - TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">API Logs</h1>
        <p class="text-gray-500 text-sm mt-1">HMAC API request history across external integrations.</p>
    </div>
</div>

@include('partials.alerts')

<form method="GET" action="{{ route('api-logs.index') }}"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
    <div class="min-w-44">
        <label class="block text-xs font-medium text-gray-500 mb-1">Client</label>
        <select name="client_id" class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="">All Clients</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                    {{ $client->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status" class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All</option>
            <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Success</option>
            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
    </div>

    <div class="min-w-56 flex-1">
        <label class="block text-xs font-medium text-gray-500 mb-1">Endpoint</label>
        <input type="text" name="endpoint" value="{{ request('endpoint') }}"
               placeholder="/api/v1/listings/search"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
    </div>

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
        <input type="date" name="date" value="{{ request('date') }}"
               class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
    </div>

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['client_id', 'status', 'endpoint', 'date']))
            <a href="{{ route('api-logs.index') }}" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                Clear
            </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    @if ($logs->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-sm">No API logs found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Time</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Method</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Endpoint</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">IP</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $log->created_at?->format('d M Y, h:i:s A') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $log->client?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $log->api_key ? substr($log->api_key, 0, 10).'...' : '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-mono">{{ $log->method }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-mono text-xs">{{ $log->endpoint }}</td>
                            <td class="px-4 py-3">
                                @if ($log->success)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        {{ $log->response_status }} Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                        {{ $log->response_status }} Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $log->request_ip ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('api-logs.show', $log) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-4 py-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
