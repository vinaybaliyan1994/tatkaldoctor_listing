@extends('layouts.app')

@section('title', 'Login - TatkalDoctor Listing Admin')

@section('content')
<div class="min-h-screen grid lg:grid-cols-[minmax(0,0.95fr)_minmax(420px,0.65fr)] bg-[radial-gradient(circle_at_top_left,_#dbeafe,_transparent_34%),linear-gradient(135deg,_#f8fafc_0%,_#eff6ff_45%,_#ecfdf5_100%)]">

    <section class="hidden lg:flex flex-col justify-between px-12 py-10 bg-gradient-to-br from-blue-700 via-blue-800 to-emerald-700 text-white">
        <div>
            <img src="{{ asset('assets/brand/tatkaldoctor-logo.png') }}"
                 alt="TatkalDoctor"
                 class="h-16 w-auto brightness-0 invert">
        </div>

        <div class="max-w-xl">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">Listing Admin</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight">Manage verified doctor listings with cleaner controls and faster review flow.</h1>
            <div class="mt-8 grid grid-cols-1 gap-3 text-sm text-blue-50">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3">Approve and maintain doctor profiles</div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3">Manage cities, locations, services and qualifications</div>
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3">Sync approved profiles back to TatkalDoctor Solution</div>
            </div>
        </div>

        <p class="text-sm text-blue-100">&copy; {{ date('Y') }} TatkalDoctor. All rights reserved.</p>
    </section>

    <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-8">
        <div class="w-full max-w-md">
            <div class="mb-7 text-center lg:hidden">
                <img src="{{ asset('assets/brand/tatkaldoctor-logo.png') }}"
                     alt="TatkalDoctor"
                     class="mx-auto h-16 w-auto">
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Listing Admin</p>
            </div>

            <div class="hidden lg:block mb-7">
                <img src="{{ asset('assets/brand/tatkaldoctor-logo.png') }}"
                     alt="TatkalDoctor"
                     class="h-14 w-auto">
                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Listing Admin</p>
            </div>

            <div class="overflow-hidden rounded-3xl border border-white/70 bg-white/95 shadow-2xl shadow-blue-900/10 backdrop-blur">
                <div class="px-6 py-7 sm:px-8 sm:py-8">
                    <h2 class="text-2xl font-bold text-slate-900">Welcome back</h2>
                    <p class="mt-1 text-sm text-slate-500">Sign in to the doctor listing admin portal.</p>

                    @if (session('status'))
                        <div class="mt-6 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-6 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email address</label>
                            <input id="email"
                                   name="email"
                                   type="email"
                                   autocomplete="email"
                                   autofocus
                                   value="{{ old('email') }}"
                                   placeholder="admin@example.com"
                                   class="w-full rounded-xl border px-4 py-3 text-sm text-slate-900 placeholder-slate-400 transition
                                          focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400
                                          @error('email') border-red-400 bg-red-50 @else border-slate-200 bg-slate-50 @enderror">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                            <div class="relative">
                                <input id="password"
                                       name="password"
                                       type="password"
                                       autocomplete="current-password"
                                       placeholder="Password"
                                       class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 pr-11 text-sm text-slate-900 placeholder-slate-400 transition
                                              focus:outline-none focus:ring-2 focus:ring-teal-500/30 focus:border-teal-400">
                                <button type="button" onclick="togglePassword()"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600"
                                        aria-label="Toggle password visibility">
                                    <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                   class="h-4 w-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500">
                            <label for="remember" class="ml-2 text-sm text-slate-600">Keep me signed in</label>
                        </div>

                        <button type="submit"
                                class="w-full rounded-xl bg-teal-600 px-4 py-3 text-sm font-semibold text-white transition
                                       hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                            Sign in
                        </button>
                    </form>
                </div>
                <div class="border-t border-slate-100 bg-slate-50 px-8 py-4 text-center">
                    <p class="text-xs text-slate-400">Authorised TatkalDoctor personnel only</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('eye-icon');
        const isText = input.type === 'text';
        input.type = isText ? 'password' : 'text';
        icon.innerHTML = isText
            ? `<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
               <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`
            : `<path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.584 10.587a2 2 0 002.828 2.826"/>
               <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 4.879A9.97 9.97 0 0112 4.65c4.478 0 8.268 2.943 9.542 7a10.042 10.042 0 01-4.046 5.161M6.228 6.228A10.043 10.043 0 002.458 11.65c1.274 4.057 5.064 7 9.542 7 1.1 0 2.15-.178 3.13-.506"/>`;
    }
</script>
@endpush
