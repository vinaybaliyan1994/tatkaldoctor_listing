@extends('layouts.admin')

@section('title', 'Add Client Subscription — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('client-subscriptions.index') }}" class="hover:text-gray-700">Client Subscriptions</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Create</span>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-1">Add Subscription</h1>
<p class="text-gray-500 text-sm mb-6">Assign a subscription plan to a client.</p>

@include('partials.alerts')

<form method="POST" action="{{ route('client-subscriptions.store') }}" class="space-y-5">
@csrf

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <h2 class="text-base font-semibold text-gray-700 mb-5 pb-3 border-b border-gray-100">Subscription Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="client_id">
                Client <span class="text-red-500">*</span>
            </label>
            <select id="client_id" name="client_id" required
                    class="w-full px-4 py-2.5 rounded-lg border @error('client_id') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">— Select client —</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
            @error('client_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="subscription_plan_id">
                Plan <span class="text-red-500">*</span>
            </label>
            <select id="subscription_plan_id" name="subscription_plan_id" required
                    class="w-full px-4 py-2.5 rounded-lg border @error('subscription_plan_id') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="">— Select plan —</option>
                @foreach ($plans as $plan)
                    <option value="{{ $plan->id }}" {{ old('subscription_plan_id') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} — @if ($plan->price == 0) Free @else ₹{{ number_format($plan->price, 0) }} @endif
                    </option>
                @endforeach
            </select>
            @error('subscription_plan_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="start_date">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input id="start_date" name="start_date" type="date"
                   value="{{ old('start_date', date('Y-m-d')) }}" required
                   class="w-full px-4 py-2.5 rounded-lg border @error('start_date') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="end_date">
                End Date <span class="text-gray-400 text-xs font-normal">(optional)</span>
            </label>
            <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}"
                   class="w-full px-4 py-2.5 rounded-lg border @error('end_date') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="status">Status</label>
            <select id="status" name="status"
                    class="w-full px-4 py-2.5 rounded-lg border @error('status') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="pending"   {{ old('status', 'pending') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="active"    {{ old('status') === 'active'               ? 'selected' : '' }}>Active</option>
                <option value="expired"   {{ old('status') === 'expired'              ? 'selected' : '' }}>Expired</option>
                <option value="cancelled" {{ old('status') === 'cancelled'            ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="payment_status">Payment Status</label>
            <select id="payment_status" name="payment_status"
                    class="w-full px-4 py-2.5 rounded-lg border @error('payment_status') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <option value="unpaid" {{ old('payment_status', 'unpaid') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="free"   {{ old('payment_status') === 'free'             ? 'selected' : '' }}>Free</option>
                <option value="paid"   {{ old('payment_status') === 'paid'             ? 'selected' : '' }}>Paid</option>
                <option value="failed" {{ old('payment_status') === 'failed'           ? 'selected' : '' }}>Failed</option>
            </select>
            @error('payment_status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1" for="amount">Amount (₹)</label>
            <input id="amount" name="amount" type="number" step="0.01" min="0"
                   value="{{ old('amount', '0.00') }}"
                   class="w-full px-4 py-2.5 rounded-lg border @error('amount') border-red-400 bg-red-50 @else border-gray-300 @enderror
                          text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1" for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3"
                      class="w-full px-4 py-2.5 rounded-lg border @error('notes') border-red-400 bg-red-50 @else border-gray-300 @enderror
                             text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 transition resize-none">{{ old('notes') }}</textarea>
            @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        Create Subscription
    </button>
    <a href="{{ route('client-subscriptions.index') }}"
       class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection
