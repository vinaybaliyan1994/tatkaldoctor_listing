@extends('layouts.admin')

@section('title', 'Create Subscription Plan — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
    <a href="{{ route('subscription-plans.index') }}" class="hover:text-slate-700">Subscription Plans</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-slate-800 font-medium">Create</span>
</div>

<h1 class="text-2xl font-bold text-slate-800 mb-1">Create Subscription Plan</h1>
<p class="text-slate-500 text-sm mb-6">Add a new pricing tier for clients.</p>

@include('partials.alerts')

<form method="POST" action="{{ route('subscription-plans.store') }}" class="space-y-5">
@csrf

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-700 mb-5 pb-3 border-b border-slate-100">Plan Details</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="name">
                Name <span class="text-red-500">*</span>
            </label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="191" required
                   class="w-full px-4 py-2.5 rounded-xl border @error('name') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="slug">
                Slug <span class="text-slate-400 text-xs font-normal">(auto-generated if blank)</span>
            </label>
            <input id="slug" name="slug" type="text" value="{{ old('slug') }}" maxlength="191"
                   class="w-full px-4 py-2.5 rounded-xl border @error('slug') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="description">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full px-4 py-2.5 rounded-xl border @error('description') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition resize-none">{{ old('description') }}</textarea>
            @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="price">
                Price (₹) <span class="text-red-500">*</span>
            </label>
            <input id="price" name="price" type="number" step="0.01" min="0"
                   value="{{ old('price', '0.00') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border @error('price') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="duration_days">
                Duration (days) <span class="text-red-500">*</span>
            </label>
            <input id="duration_days" name="duration_days" type="number" min="1"
                   value="{{ old('duration_days', '30') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border @error('duration_days') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('duration_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="max_staff">
                Max Staff <span class="text-slate-400 text-xs font-normal">(blank = unlimited)</span>
            </label>
            <input id="max_staff" name="max_staff" type="number" min="1" value="{{ old('max_staff') }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('max_staff') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('max_staff')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="max_locations">
                Max Locations <span class="text-slate-400 text-xs font-normal">(blank = unlimited)</span>
            </label>
            <input id="max_locations" name="max_locations" type="number" min="1" value="{{ old('max_locations') }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('max_locations') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('max_locations')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="max_appointments">
                Max Appointments/month <span class="text-slate-400 text-xs font-normal">(blank = unlimited)</span>
            </label>
            <input id="max_appointments" name="max_appointments" type="number" min="1" value="{{ old('max_appointments') }}"
                   class="w-full px-4 py-2.5 rounded-xl border @error('max_appointments') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition">
            @error('max_appointments')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1" for="features">
                Features <span class="text-slate-400 text-xs font-normal">(one per line)</span>
            </label>
            <textarea id="features" name="features" rows="5" placeholder="Priority listing&#10;WhatsApp notifications&#10;API access"
                      class="w-full px-4 py-2.5 rounded-xl border @error('features') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400 transition resize-y">{{ old('features', is_array(old('features')) ? implode("\n", old('features')) : '') }}</textarea>
            @error('features')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

    </div>
</div>

<div class="bg-white rounded-xl border border-slate-100 shadow-sm p-6">
    <h2 class="text-base font-semibold text-slate-700 mb-4 pb-3 border-b border-slate-100">Status</h2>
    <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" name="status" value="1"
               {{ old('status', '1') === '1' ? 'checked' : '' }}
               class="w-4 h-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500">
        <div>
            <p class="text-sm font-medium text-slate-700">Active</p>
            <p class="text-xs text-slate-400">Plan is available for new subscriptions.</p>
        </div>
    </label>
</div>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="px-6 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        Create Plan
    </button>
    <a href="{{ route('subscription-plans.index') }}"
       class="px-6 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl transition-colors">
        Cancel
    </a>
</div>

</form>

@endsection
