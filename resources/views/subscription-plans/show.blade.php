@extends('layouts.admin')

@section('title', $subscriptionPlan->name . ' — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('subscription-plans.index') }}" class="hover:text-slate-700">Subscription Plans</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-slate-800 font-medium">{{ $subscriptionPlan->name }}</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('subscription-plans.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
            ← Back
        </a>
        @if (Auth::user()->isSuperAdmin())
        <a href="{{ route('subscription-plans.edit', $subscriptionPlan) }}"
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
            <h1 class="text-2xl font-bold text-slate-800">{{ $subscriptionPlan->name }}</h1>
            <p class="text-xs text-slate-400 font-mono mt-1">{{ $subscriptionPlan->slug }}</p>
            @if ($subscriptionPlan->description)
            <p class="text-slate-600 text-sm mt-2">{{ $subscriptionPlan->description }}</p>
            @endif
        </div>
        <div class="flex flex-col items-end gap-2">
            <div class="text-3xl font-extrabold text-purple-700">
                @if ($subscriptionPlan->price == 0)
                    <span class="text-green-600">Free</span>
                @else
                    ₹{{ number_format($subscriptionPlan->price, 0) }}
                    <span class="text-sm font-normal text-slate-400">/ {{ $subscriptionPlan->duration_days }} days</span>
                @endif
            </div>
            @if ($subscriptionPlan->status)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive
                </span>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Limits</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500">Max Staff</dt>
                <dd class="font-medium text-slate-800">{{ $subscriptionPlan->max_staff ?? '∞ Unlimited' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500">Max Locations</dt>
                <dd class="font-medium text-slate-800">{{ $subscriptionPlan->max_locations ?? '∞ Unlimited' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500">Max Appointments</dt>
                <dd class="font-medium text-slate-800">{{ $subscriptionPlan->max_appointments ? $subscriptionPlan->max_appointments . '/month' : '∞ Unlimited' }}</dd>
            </div>
            <div class="flex gap-3">
                <dt class="w-40 text-slate-500">Duration</dt>
                <dd class="font-medium text-slate-800">{{ $subscriptionPlan->duration_days }} days</dd>
            </div>
        </dl>
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Features</h2>
        @if ($subscriptionPlan->features && count($subscriptionPlan->features))
            <ul class="space-y-2">
                @foreach ($subscriptionPlan->features as $feature)
                <li class="flex items-start gap-2 text-sm text-slate-700">
                    <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
        @else
            <p class="text-slate-400 text-sm">No features listed.</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">Subscribers</h2>
        <p class="text-3xl font-bold text-teal-700">{{ $subscriptionPlan->clientSubscriptions()->count() }}</p>
        <p class="text-xs text-slate-400 mt-1">total subscriptions</p>
        <p class="text-sm text-teal-600 font-semibold mt-1">{{ $subscriptionPlan->clientSubscriptions()->where('status', 'active')->count() }} active</p>
    </div>

</div>

@endsection
