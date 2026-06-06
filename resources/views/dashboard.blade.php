@extends('layouts.admin')

@section('title', 'Dashboard — TatkalDoctor Admin')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500 text-sm mt-1">Welcome back, {{ Auth::user()->name }}.</p>
</div>

{{-- Role badge --}}
<div class="mb-8">
    @php $role = Auth::user()->role; @endphp
    @if ($role === 'super_admin')
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
            Super Admin
        </span>
    @elseif ($role === 'admin')
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
            Admin
        </span>
    @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
            User
        </span>
    @endif
</div>

{{-- Quick-access cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

    @if (Auth::user()->isSuperAdmin())

    {{-- API Clients card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">API Clients</h3>
            <p class="text-sm text-gray-500 mt-1">Manage HMAC client credentials and access windows.</p>
        </div>
        <a href="{{ route('clients.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
            Manage clients
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Master Countries card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Master Countries</h3>
            <p class="text-sm text-gray-500 mt-1">Manage ISO alpha-3 country codes used across the platform.</p>
        </div>
        <a href="{{ route('master-countries.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-800 transition-colors">
            Manage countries
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Master Qualifications card — superadmin only --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-amber-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Master Qualifications</h3>
            <p class="text-sm text-gray-500 mt-1">Manage medical qualifications such as MBBS, MD, BDS.</p>
        </div>
        <a href="{{ route('master-qualifications.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-amber-600 hover:text-amber-800 transition-colors">
            Manage qualifications
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Master Services card — superadmin only --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Master Services</h3>
            <p class="text-sm text-gray-500 mt-1">Manage service categories and sub-services for doctors.</p>
        </div>
        <a href="{{ route('master-services.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-rose-600 hover:text-rose-800 transition-colors">
            Manage services
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @endif

    @if (Auth::user()->isAdmin())
    {{-- API Logs card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-cyan-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 17v-6a2 2 0 012-2h8m0 0l-3-3m3 3l-3 3M13 7H5a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-1"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-cyan-700">{{ \Illuminate\Support\Facades\Schema::hasTable('api_logs') ? \App\Models\ApiLog::count() : 0 }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">API Logs</h3>
            <p class="text-sm text-gray-500 mt-1">Review HMAC API request history and failures.</p>
        </div>
        <a href="{{ route('api-logs.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-cyan-600 hover:text-cyan-800 transition-colors">
            View logs
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    @endif

    {{-- Master Cities card — visible to all roles --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-sky-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Master Cities</h3>
            <p class="text-sm text-gray-500 mt-1">
                @if (Auth::user()->isSuperAdmin())
                    Add and manage cities linked to countries.
                @else
                    Browse the list of cities.
                @endif
            </p>
        </div>
        <a href="{{ route('master-cities.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-sky-600 hover:text-sky-800 transition-colors">
            {{ Auth::user()->isSuperAdmin() ? 'Manage cities' : 'View cities' }}
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Master Locations card — visible to all roles --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="w-10 h-10 bg-violet-50 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Master Locations</h3>
            <p class="text-sm text-gray-500 mt-1">
                @if (Auth::user()->isSuperAdmin())
                    Add and manage locations within cities.
                @else
                    Browse the list of locations.
                @endif
            </p>
        </div>
        <a href="{{ route('master-locations.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-violet-600 hover:text-violet-800 transition-colors">
            {{ Auth::user()->isSuperAdmin() ? 'Manage locations' : 'View locations' }}
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Listings card — visible to all roles --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-green-700">{{ \App\Models\Listing::count() }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Doctor Listings</h3>
            @php
                $pendingCount  = \App\Models\Listing::where('verification_status', 'pending')->count();
                $approvedCount = \App\Models\Listing::where('verification_status', 'approved')->count();
                $rejectedCount = \App\Models\Listing::where('verification_status', 'rejected')->count();
            @endphp
            <div class="flex flex-wrap gap-2 mt-2">
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendingCount }} pending</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $approvedCount }} approved</span>
                @if ($rejectedCount)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">{{ $rejectedCount }} rejected</span>
                @endif
            </div>
        </div>
        <a href="{{ route('listings.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-green-600 hover:text-green-800 transition-colors">
            {{ Auth::user()->isSuperAdmin() ? 'Manage listings' : 'View listings' }}
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Subscription Plans card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-purple-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-purple-700">{{ \App\Models\SubscriptionPlan::count() }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Subscription Plans</h3>
            <p class="text-sm text-gray-500 mt-1">Manage pricing tiers and feature access for clients.</p>
        </div>
        <a href="{{ route('subscription-plans.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-800 transition-colors">
            View plans
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    @if (Auth::user()->isAdmin())
    {{-- Doctor Documents card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-orange-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-orange-700">{{ \Illuminate\Support\Facades\Schema::hasTable('doctor_documents') ? \App\Models\DoctorDocument::count() : 0 }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Doctor Documents</h3>
            @php
                $pendingDocs  = \Illuminate\Support\Facades\Schema::hasTable('doctor_documents') ? \App\Models\DoctorDocument::where('status', 'pending')->count() : 0;
                $approvedDocs = \Illuminate\Support\Facades\Schema::hasTable('doctor_documents') ? \App\Models\DoctorDocument::where('status', 'approved')->count() : 0;
                $rejectedDocs = \Illuminate\Support\Facades\Schema::hasTable('doctor_documents') ? \App\Models\DoctorDocument::where('status', 'rejected')->count() : 0;
            @endphp
            <div class="flex flex-wrap gap-2 mt-2">
                @if ($pendingDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendingDocs }} pending</span>
                @endif
                @if ($approvedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">{{ $approvedDocs }} approved</span>
                @endif
                @if ($rejectedDocs)
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600">{{ $rejectedDocs }} rejected</span>
                @endif
                @if (!$pendingDocs && !$approvedDocs && !$rejectedDocs)
                <span class="text-xs text-gray-400">No documents yet</span>
                @endif
            </div>
        </div>
        <a href="{{ route('listings.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-orange-600 hover:text-orange-800 transition-colors">
            View listings
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Audit Logs card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-pink-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-pink-700">{{ \Illuminate\Support\Facades\Schema::hasTable('listing_audit_logs') ? \App\Models\ListingAuditLog::count() : 0 }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Audit Logs</h3>
            <p class="text-sm text-gray-500 mt-1">Track all listing changes and verification actions.</p>
        </div>
        <a href="{{ route('listing-audit-logs.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-pink-600 hover:text-pink-800 transition-colors">
            View audit trail
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Client Subscriptions card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-teal-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-teal-700">{{ \App\Models\ClientSubscription::count() }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Client Subscriptions</h3>
            <p class="text-sm text-gray-500 mt-1">Track active, expired and pending client subscriptions.</p>
        </div>
        <a href="{{ route('client-subscriptions.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-teal-600 hover:text-teal-800 transition-colors">
            View subscriptions
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Settings card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 flex flex-col gap-4">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 bg-slate-50 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <span class="text-2xl font-bold text-slate-700">{{ \App\Models\Setting::count() }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-800">Global Settings</h3>
            <p class="text-sm text-gray-500 mt-1">Configure site-wide settings, appearance and API options.</p>
        </div>
        <a href="{{ route('settings.index') }}" class="mt-auto inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
            Manage settings
            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    @endif

</div>

@endsection
