@extends('layouts.admin')

@section('title', 'Subscription #' . $clientSubscription->id . ' — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('client-subscriptions.index') }}" class="hover:text-slate-700">Client Subscriptions</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-800 font-medium">#{{ $clientSubscription->id }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('client-subscriptions.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('client-subscriptions.edit', $clientSubscription) }}"
           class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            Edit
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Subscription #{{ $clientSubscription->id }}</h1>
            <p class="text-slate-500 text-sm mt-1">
                <span class="font-medium text-slate-700">{{ $clientSubscription->client->name }}</span>
                <span class="text-slate-300 mx-1">·</span>
                <span class="font-mono text-xs text-slate-400">{{ $clientSubscription->client->api_key }}</span>
            </p>
        </div>
        <div class="flex flex-col gap-1.5 items-end">
            @php
                $statusColors = ['active' => 'bg-green-100 text-green-700', 'pending' => 'bg-amber-100 text-amber-700', 'expired' => 'bg-slate-100 text-slate-500', 'cancelled' => 'bg-red-100 text-red-600'];
                $payColors    = ['free' => 'bg-green-100 text-green-700', 'paid' => 'bg-teal-100 text-teal-700', 'unpaid' => 'bg-amber-100 text-amber-700', 'failed' => 'bg-red-100 text-red-600'];
            @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$clientSubscription->status] ?? 'bg-slate-100 text-slate-500' }}">
                {{ ucfirst($clientSubscription->status) }}
            </span>
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payColors[$clientSubscription->payment_status] ?? 'bg-slate-100 text-slate-500' }}">
                {{ ucfirst($clientSubscription->payment_status) }}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Plan</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500">Plan</dt>
                <dd class="font-medium text-slate-800">
                    <a href="{{ route('subscription-plans.show', $clientSubscription->plan) }}"
                       class="text-teal-600 hover:text-teal-800">{{ $clientSubscription->plan->name }}</a>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500">Amount</dt>
                <dd class="font-semibold text-slate-800">
                    @if ($clientSubscription->amount == 0)
                        <span class="text-green-600">Free</span>
                    @else
                        ₹{{ number_format($clientSubscription->amount, 2) }}
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Period</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500">Start Date</dt>
                <dd class="text-slate-800">{{ $clientSubscription->start_date->format('d M Y') }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500">End Date</dt>
                <dd class="text-slate-800">{{ $clientSubscription->end_date ? $clientSubscription->end_date->format('d M Y') : 'No end date' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-slate-500">Created</dt>
                <dd class="text-slate-500 text-xs">{{ $clientSubscription->created_at->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    @if ($clientSubscription->notes)
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-3">Notes</h2>
        <p class="text-slate-700 text-sm whitespace-pre-line">{{ $clientSubscription->notes }}</p>
    </div>
    @endif

</div>

@endsection
