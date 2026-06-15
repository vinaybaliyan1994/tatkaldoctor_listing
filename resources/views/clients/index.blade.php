@extends('layouts.admin')

@section('title', 'Clients — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">API Clients</h1>
        <p class="text-slate-500 text-sm mt-1">Manage HMAC client credentials and access windows.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Client
        </a>
    @endif
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($clients->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <p class="text-sm">No clients yet.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('clients.create') }}" class="text-teal-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">API Key</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Available From</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Available To</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($clients as $client)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-4 text-slate-400 text-xs">{{ $client->id }}</td>
                    <td class="px-6 py-4 font-medium text-slate-800">{{ $client->name }}</td>
                    <td class="px-6 py-4">
                        <code class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded font-mono">{{ $client->api_key }}</code>
                    </td>
                    <td class="px-6 py-4 text-slate-600">{{ $client->avail_from_date?->format('d M Y') ?? '—' }}</td>
                    <td class="px-6 py-4 text-slate-600">{{ $client->avail_to_date?->format('d M Y') ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if ($client->status === 'active')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('clients.show', $client) }}"
                               class="text-xs text-teal-600 hover:text-teal-800 font-medium">View</a>
                            @if (Auth::user()->isSuperAdmin())
                            <a href="{{ route('clients.edit', $client) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                  onsubmit="return confirm('Delete client \'{{ $client->name }}\'? This cannot be undone.')">
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

        @if ($clients->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $clients->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
