@extends('layouts.admin')

@section('title', 'Audit Log #' . $listingAuditLog->id)

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('listing-audit-logs.index') }}" class="hover:text-slate-700">Audit Logs</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-800 font-medium">#{{ $listingAuditLog->id }}</span>
    </div>
    <a href="{{ route('listing-audit-logs.index') }}"
       class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
        ← Back
    </a>
</div>

@include('partials.alerts')

<div class="max-w-2xl space-y-5">

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Event Summary</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Action</dt>
                <dd>
                    <span class="inline-flex px-2.5 py-0.5 rounded text-xs font-mono
                        @if(str_contains($listingAuditLog->action, 'verif')) bg-amber-50 text-amber-700
                        @elseif(str_contains($listingAuditLog->action, 'creat')) bg-emerald-50 text-emerald-700
                        @elseif(str_contains($listingAuditLog->action, 'delet')) bg-red-50 text-red-600
                        @else bg-slate-100 text-slate-600
                        @endif">
                        {{ $listingAuditLog->action }}
                    </span>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Listing</dt>
                <dd>
                    <a href="{{ route('listings.show', $listingAuditLog->listing) }}"
                       class="text-teal-600 hover:text-teal-800 font-medium">
                        {{ $listingAuditLog->listing->name }}
                    </a>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Changed By</dt>
                <dd class="text-slate-800">{{ $listingAuditLog->changedBy?->name ?? 'System' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Date / Time</dt>
                <dd class="text-slate-800">{{ $listingAuditLog->created_at->format('d M Y, H:i:s') }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">IP Address</dt>
                <dd class="text-slate-600 font-mono text-xs">{{ $listingAuditLog->ip_address ?? '—' }}</dd>
            </div>
            @if ($listingAuditLog->user_agent)
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">User Agent</dt>
                <dd class="text-slate-500 text-xs break-all">{{ $listingAuditLog->user_agent }}</dd>
            </div>
            @endif
            @if ($listingAuditLog->remarks)
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500 flex-shrink-0">Remarks</dt>
                <dd class="text-slate-700">{{ $listingAuditLog->remarks }}</dd>
            </div>
            @endif
        </dl>
    </div>

    @if ($listingAuditLog->old_values)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Before</h2>
        <pre class="bg-slate-50 rounded-xl p-4 text-xs font-mono text-slate-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($listingAuditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

    @if ($listingAuditLog->new_values)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">After</h2>
        <pre class="bg-emerald-50 rounded-xl p-4 text-xs font-mono text-slate-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($listingAuditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif

</div>

@endsection
