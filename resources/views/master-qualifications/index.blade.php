@extends('layouts.admin')

@section('title', 'Qualifications — TatkalDoctor Admin')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Master Qualifications</h1>
        <p class="text-gray-500 text-sm mt-1">Medical qualifications available on the platform.</p>
    </div>
    @if (Auth::user()->isSuperAdmin())
    <a href="{{ route('master-qualifications.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Add Qualification
    </a>
    @endif
</div>

@include('partials.alerts')

{{-- Filters --}}
<form method="GET" action="{{ route('master-qualifications.index') }}"
      class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-4">

    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
        <select name="status"
                class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
            <option value="all"      {{ request('status', 'all') === 'all'      ? 'selected' : '' }}>All</option>
            <option value="active"   {{ request('status') === 'active'          ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive'        ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>

    <div class="flex items-center gap-2">
        <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            Filter
        </button>
        @if (request()->hasAny(['status']))
        <a href="{{ route('master-qualifications.index') }}"
           class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
            Clear
        </a>
        @endif
    </div>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    @if ($qualifications->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">No qualifications found.
                @if (Auth::user()->isSuperAdmin())
                    <a href="{{ route('master-qualifications.create') }}" class="text-blue-600 hover:underline">Add the first one.</a>
                @endif
            </p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Qualification</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    @if (Auth::user()->isSuperAdmin())
                    <th class="px-6 py-3 w-28"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($qualifications as $qual)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-3.5 text-gray-400 text-xs">{{ $qual->id }}</td>
                    <td class="px-6 py-3.5 font-semibold text-gray-800 font-mono tracking-wide">{{ $qual->qualification }}</td>
                    <td class="px-6 py-3.5">
                        @if ($qual->status)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                            </span>
                        @endif
                    </td>
                    @if (Auth::user()->isSuperAdmin())
                    <td class="px-6 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('master-qualifications.edit', $qual) }}"
                               class="text-xs text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                            <form method="POST" action="{{ route('master-qualifications.destroy', $qual) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($qual->qualification) }}\'? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-500 hover:text-red-700 font-medium">Delete</button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($qualifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $qualifications->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
