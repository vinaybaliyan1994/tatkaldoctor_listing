@extends('layouts.admin')

@section('title', 'Client Subscriptions — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Client Subscriptions</h1>
        <p class="text-slate-500 text-sm mt-1">All client plan subscriptions on the platform.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('client-subscriptions.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Subscription
    </a>
    @endif
</div>

@include('partials.alerts')

<form method="GET" action="{{ route('client-subscriptions.index') }}"
      class="bg-white rounded-xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">

    <div class="min-w-40">
        <label class="block text-xs font-medium text-slate-500 mb-1">Plan</label>
        <select name="plan_id"
                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All Plans</option>
            @foreach ($plans as $plan)
                <option value="{{ $plan->id }}" {{ request('plan_id') == $plan->id ? 'selected' : '' }}>
                    {{ $plan->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All</option>
            <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
            <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
            <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expired</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">Payment</label>
        <select name="payment_status"
                class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            <option value="">All</option>
            <option value="free"   {{ request('payment_status') === 'free'   ? 'selected' : '' }}>Free</option>
            <option value="paid"   {{ request('payment_status') === 'paid'   ? 'selected' : '' }}>Paid</option>
            <option value="unpaid" {{ request('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
            <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Failed</option>
        </select>
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
    </div>

    <div>
        <label class="block text-xs font-medium text-slate-500 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               class="px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['plan_id', 'status', 'payment_status', 'date_from', 'date_to']))
        <a href="{{ route('client-subscriptions.index') }}"
           class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 text-sm font-medium rounded-xl transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($subscriptions->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <p class="text-sm">No subscriptions found.</p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Client</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Plan</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Period</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Amount</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Payment</th>
                    <th class="px-4 py-3 w-28"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($subscriptions as $sub)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $sub->id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-800">{{ $sub->client->name }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $sub->client->api_key }}</p>
                    </td>
                    <td class="px-4 py-3 text-slate-700">{{ $sub->plan->name }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">
                        <div>{{ $sub->start_date->format('d M Y') }}</div>
                        @if ($sub->end_date)
                        <div class="text-slate-400">→ {{ $sub->end_date->format('d M Y') }}</div>
                        @else
                        <div class="text-slate-400">→ No end</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 font-semibold text-slate-800">
                        @if ($sub->amount == 0)
                            <span class="text-green-600 text-xs">Free</span>
                        @else
                            ₹{{ number_format($sub->amount, 0) }}
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'active'    => 'bg-green-100 text-green-700',
                                'pending'   => 'bg-amber-100 text-amber-700',
                                'expired'   => 'bg-slate-100 text-slate-500',
                                'cancelled' => 'bg-red-100 text-red-600',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$sub->status] ?? 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $payColors = [
                                'free'   => 'bg-green-100 text-green-700',
                                'paid'   => 'bg-teal-100 text-teal-700',
                                'unpaid' => 'bg-amber-100 text-amber-700',
                                'failed' => 'bg-red-100 text-red-600',
                            ];
                        @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $payColors[$sub->payment_status] ?? 'bg-slate-100 text-slate-500' }}">
                            {{ ucfirst($sub->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('client-subscriptions.show', $sub) }}"
                               class="text-xs text-teal-600 hover:text-teal-800 font-medium">View</a>
                            @if (Auth::user()->isSuperAdmin())
                            <a href="{{ route('client-subscriptions.edit', $sub) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('client-subscriptions.destroy', $sub) }}"
                                  onsubmit="return confirm('Delete this subscription?')">
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
        </div>

        @if ($subscriptions->hasPages())
        <div class="px-4 py-4 border-t border-slate-100">
            {{ $subscriptions->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
