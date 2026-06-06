@extends('layouts.admin')

@section('title', 'Subscription #' . $clientSubscription->id . ' — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('client-subscriptions.index') }}" class="hover:text-gray-700">Client Subscriptions</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-800 font-medium">#{{ $clientSubscription->id }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('client-subscriptions.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('client-subscriptions.edit', $clientSubscription) }}"
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Edit
        </a>
        @endif
    </div>
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Subscription #{{ $clientSubscription->id }}</h1>
            <p class="text-gray-500 text-sm mt-1">
                <span class="font-medium text-gray-700">{{ $clientSubscription->client->name }}</span>
                <span class="text-gray-300 mx-1">·</span>
                <span class="font-mono text-xs text-gray-400">{{ $clientSubscription->client->api_key }}</span>
            </p>
        </div>
        <div class="flex flex-col gap-1.5 items-end">
            @php
                $statusColors = ['active' => 'bg-green-100 text-green-700', 'pending' => 'bg-amber-100 text-amber-700', 'expired' => 'bg-gray-100 text-gray-500', 'cancelled' => 'bg-red-100 text-red-600'];
                $payColors    = ['free' => 'bg-green-100 text-green-700', 'paid' => 'bg-blue-100 text-blue-700', 'unpaid' => 'bg-amber-100 text-amber-700', 'failed' => 'bg-red-100 text-red-600'];
            @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$clientSubscription->status] ?? 'bg-gray-100 text-gray-500' }}">
                {{ ucfirst($clientSubscription->status) }}
            </span>
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payColors[$clientSubscription->payment_status] ?? 'bg-gray-100 text-gray-500' }}">
                {{ ucfirst($clientSubscription->payment_status) }}
            </span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Plan</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-gray-500">Plan</dt>
                <dd class="font-medium text-gray-800">
                    <a href="{{ route('subscription-plans.show', $clientSubscription->plan) }}"
                       class="text-blue-600 hover:underline">{{ $clientSubscription->plan->name }}</a>
                </dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-gray-500">Amount</dt>
                <dd class="font-semibold text-gray-800">
                    @if ($clientSubscription->amount == 0)
                        <span class="text-green-600">Free</span>
                    @else
                        ₹{{ number_format($clientSubscription->amount, 2) }}
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-4">Period</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-32 text-gray-500">Start Date</dt>
                <dd class="text-gray-800">{{ $clientSubscription->start_date->format('d M Y') }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-gray-500">End Date</dt>
                <dd class="text-gray-800">{{ $clientSubscription->end_date ? $clientSubscription->end_date->format('d M Y') : 'No end date' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-32 text-gray-500">Created</dt>
                <dd class="text-gray-500 text-xs">{{ $clientSubscription->created_at->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    @if ($clientSubscription->notes)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 lg:col-span-2">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Notes</h2>
        <p class="text-gray-700 text-sm whitespace-pre-line">{{ $clientSubscription->notes }}</p>
    </div>
    @endif

</div>

@endsection
