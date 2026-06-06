@extends('layouts.admin')

@section('title', 'Subscription Plans — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Subscription Plans</h1>
        <p class="text-gray-500 text-sm mt-1">Pricing tiers available to clients.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('subscription-plans.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Plan
    </a>
    @endif
</div>

@include('partials.alerts')

{{-- Filters --}}
<form method="GET" action="{{ route('subscription-plans.index') }}"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="all"      {{ request('status', 'all') === 'all'   ? 'selected' : '' }}>All</option>
            <option value="active"   {{ request('status') === 'active'       ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive'     ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Plan name…"
               class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
    </div>
    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['status', 'search']))
        <a href="{{ route('subscription-plans.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    @if ($plans->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <p class="text-sm">No plans found.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('subscription-plans.create') }}" class="text-blue-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Price</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Duration</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Limits</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 w-32"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($plans as $plan)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $plan->id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $plan->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $plan->slug }}</p>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800">
                        @if ($plan->price == 0)
                            <span class="text-green-600">Free</span>
                        @else
                            ₹{{ number_format($plan->price, 0) }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $plan->duration_days }} days</td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        <div>Staff: {{ $plan->max_staff ?? '∞' }}</div>
                        <div>Locations: {{ $plan->max_locations ?? '∞' }}</div>
                        <div>Appointments: {{ $plan->max_appointments ?? '∞' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($plan->status)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('subscription-plans.show', $plan) }}"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium">View</a>
                            @if (Auth::user()->isSuperAdmin())
                            <a href="{{ route('subscription-plans.edit', $plan) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('subscription-plans.destroy', $plan) }}"
                                  onsubmit="return confirm('Delete plan \'{{ addslashes($plan->name) }}\'?')">
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

        @if ($plans->hasPages())
        <div class="px-4 py-4 border-t border-gray-100">
            {{ $plans->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
