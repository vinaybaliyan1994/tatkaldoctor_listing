@extends('layouts.admin')

@section('title', 'Edit Qualification — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('master-qualifications.index') }}" class="hover:text-gray-700">Master Qualifications</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">Edit Qualification</span>
</div>

<div class="max-w-lg">
    <h1 class="text-2xl font-bold text-gray-800 mb-1">Edit Qualification</h1>
    <p class="text-gray-500 text-sm mb-6">Update the qualification acronym or status.</p>

    @include('partials.alerts')

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('master-qualifications.update', $masterQualification) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Qualification --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1" for="qualification">
                    Qualification <span class="text-red-500">*</span>
                </label>
                <input id="qualification" name="qualification" type="text"
                       value="{{ old('qualification', $masterQualification->qualification) }}"
                       maxlength="191"
                       oninput="this.value = this.value.toUpperCase()"
                       class="w-full px-4 py-2.5 rounded-lg border @error('qualification') border-red-400 bg-red-50 @else border-gray-300 @enderror
                              text-sm text-gray-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                @error('qualification')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="status" value="1"
                           {{ old('status', $masterQualification->status ? '1' : '0') === '1' ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Active</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Update Qualification
                </button>
                <a href="{{ route('master-qualifications.index') }}"
                   class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
