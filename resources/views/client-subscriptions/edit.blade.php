@extends('layouts.admin')

@section('title', 'Edit Subscription — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('client-subscriptions.index') }}" class="hover:text-slate-700">Client Subscriptions</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('client-subscriptions.show', $clientSubscription) }}" class="hover:text-slate-700">#{{ $clientSubscription->id }}</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Edit</span>
</div>

<h1 class="text-2xl font-bold text-slate-800 mb-1">Edit Subscription</h1>
<p class="text-slate-500 text-sm mb-6">Update subscription details for <strong>{{ $clientSubscription->client->name }}</strong>.</p>

@include('partials.alerts')

<form method="POST" action="{{ route('client-subscriptions.update', $clientSubscription) }}" class="space-y-5">
@csrf @method('PUT')

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Subscription Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="client_id">
                Client <span class="text-red-500">*</span>
            </label>
            <select id="client_id" name="client_id" required
                    class="w-full px-4 py-2.5 rounded-xl border @error('client_id') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                <option value="">— Select client —</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id', $clientSubscription->client_id) == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
            @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="subscription_plan_id">
                Plan <span class="text-red-500">*</span>
            </label>
            <select id="subscription_plan_id" name="subscription_plan_id" required
                    class="w-full px-4 py-2.5 rounded-xl border @error('subscription_plan_id') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                <option value="">— Select plan —</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('subscription_plan_id', $clientSubscription->subscription_plan_id) == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} — @if ($plan->price == 0) Free @else ₹{{ number_format($plan->price, 0) }} @endif
                    </option>
                @endforeach
            </select>
            @error('subscription_plan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="start_date">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input id="start_date" name="start_date" type="date"
                   value="{{ old('start_date', $clientSubscription->start_date->format('Y-m-d')) }}" required
                   class="w-full px-4 py-2.5 rounded-xl border @error('start_date') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="end_date">
                End Date <span class="text-slate-400 text-xs font-normal">(optional)</span>
            </label>
            <input id="end_date" name="end_date" type="date"
                   value="{{ old('end_date', $clientSubscription->end_date?->format('Y-m-d')) }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('end_date') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="status">Status</label>
            <select id="status" name="status"
                    class="w-full px-4 py-2.5 rounded-xl border @error('status') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @foreach (['pending', 'active', 'expired', 'cancelled'] as $s)
                <option value="{{ $s }}" {{ old('status', $clientSubscription->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="payment_status">Payment Status</label>
            <select id="payment_status" name="payment_status"
                    class="w-full px-4 py-2.5 rounded-xl border @error('payment_status') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
                @foreach (['unpaid', 'free', 'paid', 'failed'] as $ps)
                <option value="{{ $ps }}" {{ old('payment_status', $clientSubscription->payment_status) === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                @endforeach
            </select>
            @error('payment_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="amount">Amount (₹)</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0"
                   value="{{ old('amount', $clientSubscription->amount) }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('amount') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full px-4 py-2.5 rounded-xl border @error('notes') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition resize-none">{{ old('notes', $clientSubscription->notes) }}</textarea>
            @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        Update Subscription
    </button>
    <a href="{{ route('client-subscriptions.show', $clientSubscription) }}"
       class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection
