@extends('layouts.admin')

@section('title', 'API Log #' . $apiLog->id . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('api-logs.index') }}" class="hover:text-slate-700">API Logs</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Log #{{ $apiLog->id }}</span>
</div>

@include('partials.alerts')

<div class="max-w-4xl space-y-5">
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h1 class="font-semibold text-slate-800">Request Summary</h1>
            @if ($apiLog->success)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    {{ $apiLog->response_status }} Success
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                    {{ $apiLog->response_status }} Failed
                </span>
            @endif
        </div>

        <dl class="divide-y divide-slate-100">
            @foreach ([
                ['label' => 'Client', 'value' => $apiLog->client?->name ?? 'Unknown'],
                ['label' => 'API Key', 'value' => $apiLog->api_key ?? '-'],
                ['label' => 'Endpoint', 'value' => $apiLog->endpoint],
                ['label' => 'Method', 'value' => $apiLog->method],
                ['label' => 'Request IP', 'value' => $apiLog->request_ip ?? '-'],
                ['label' => 'Created At', 'value' => $apiLog->created_at?->format('d M Y, h:i:s A') ?? '-'],
            ] as $row)
                <div class="px-6 py-3.5 flex gap-4">
                    <dt class="w-36 flex-shrink-0 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ $row['label'] }}</dt>
                    <dd class="text-sm text-slate-800 font-mono break-all">{{ $row['value'] }}</dd>
                </div>
            @endforeach

            <div class="px-6 py-3.5 flex gap-4">
                <dt class="w-36 flex-shrink-0 text-xs font-medium text-slate-500 uppercase tracking-wide">Error Message</dt>
                <dd class="text-sm {{ $apiLog->error_message ? 'text-red-700' : 'text-slate-400 italic' }}">
                    {{ $apiLog->error_message ?? 'No error recorded.' }}
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="font-semibold text-slate-800 mb-3">Request Headers</h2>
        <pre class="text-xs bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($apiLog->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>

@endsection
