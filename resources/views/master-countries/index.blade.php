@extends('layouts.admin')

@section('title', 'Countries — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Master Countries</h1>
        <p class="text-slate-500 text-sm mt-1">ISO 3-letter country codes used across the platform.</p>
    </div>
    <a href="{{ route('master-countries.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-xl transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Country
    </a>
</div>

@include('partials.alerts')

<div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
    @if ($countries->isEmpty())
        <div class="text-center py-16 text-slate-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"/>
            </svg>
            <p class="text-sm">No countries yet. <a href="{{ route('master-countries.create') }}" class="text-teal-600 hover:underline">Add the first one.</a></p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide w-32">Code</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Name</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Added On</th>
                    <th class="px-6 py-3 w-28"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($countries as $country)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-6 py-3.5">
                        <span class="inline-block px-2.5 py-0.5 bg-teal-50 text-teal-700 font-mono font-semibold text-xs rounded">
                            {{ $country->code }}
                        </span>
                    </td>
                    <td class="px-6 py-3.5 font-medium text-slate-800">{{ $country->name }}</td>
                    <td class="px-6 py-3.5 text-slate-500 text-xs">{{ $country->created_at->format('d M Y') }}</td>
                    <td class="px-6 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('master-countries.edit', $country) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('master-countries.destroy', $country) }}"
                                  onsubmit="return confirm('Delete {{ $country->name }} ({{ $country->code }})? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($countries->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $countries->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
