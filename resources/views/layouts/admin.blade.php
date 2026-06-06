<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TatkalDoctor Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="min-h-screen flex flex-col">

    {{-- Top navbar --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-gray-800 text-lg">TatkalDoctor</span>
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('dashboard') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('dashboard') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Dashboard
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('master-locations.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('master-locations.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Locations
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('listings.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('listings.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Listings
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('subscription-plans.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('subscription-plans.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Plans
                </a>
                @if (Auth::user()->isAdmin())
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('client-subscriptions.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('client-subscriptions.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Subscriptions
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('settings.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('settings.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Settings
                </a>
                @endif
                @if (Auth::user()->isSuperAdmin())
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('master-qualifications.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('master-qualifications.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Qualifications
                </a>
                <span class="hidden sm:inline text-gray-300">|</span>
                <a href="{{ route('master-services.index') }}"
                   class="hidden sm:inline text-sm transition-colors
                          {{ request()->routeIs('master-services.*') ? 'text-blue-600 font-medium' : 'text-gray-500 hover:text-blue-600' }}">
                    Services
                </a>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', Auth::user()->role) }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    <footer class="text-center text-xs text-gray-400 py-4">
        © {{ date('Y') }} TatkalDoctor. All rights reserved.
    </footer>
</div>

@stack('scripts')
</body>
</html>
