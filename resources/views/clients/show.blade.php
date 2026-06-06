@extends('layouts.admin')

@section('title', $client->name . ' — TatkalDoctor Admin')

@section('content')

<div class="mb-6 flex items-center gap-2 text-sm text-gray-500">
    <a href="{{ route('clients.index') }}" class="hover:text-gray-700">Clients</a>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-gray-800 font-medium">{{ $client->name }}</span>
</div>

@include('partials.alerts')

{{-- One-time key reveal box --}}
@if (session('secret_key'))
<div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 p-5">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-amber-800 mb-3">Save these credentials now — the secret key will not be shown again.</p>

            <div class="space-y-3">
                <div>
                    <p class="text-xs font-medium text-amber-700 mb-1">API Key</p>
                    <div class="flex items-center gap-2">
                        <code id="api-key-val" class="flex-1 text-xs bg-white border border-amber-200 rounded px-3 py-2 font-mono text-gray-800 break-all">{{ session('api_key') }}</code>
                        <button onclick="copyText('api-key-val', this)"
                                class="flex-shrink-0 text-xs px-3 py-2 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded border border-amber-200 transition-colors font-medium">
                            Copy
                        </button>
                    </div>
                </div>
                <div>
                    <p class="text-xs font-medium text-amber-700 mb-1">Secret Key</p>
                    <div class="flex items-center gap-2">
                        <code id="secret-key-val" class="flex-1 text-xs bg-white border border-amber-200 rounded px-3 py-2 font-mono text-gray-800 break-all">{{ session('secret_key') }}</code>
                        <button onclick="copyText('secret-key-val', this)"
                                class="flex-shrink-0 text-xs px-3 py-2 bg-amber-100 hover:bg-amber-200 text-amber-800 rounded border border-amber-200 transition-colors font-medium">
                            Copy
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="max-w-2xl space-y-5">

    {{-- Details card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Client Details</h2>
            @if (Auth::user()->isSuperAdmin())
            <div class="flex items-center gap-2">
                <a href="{{ route('clients.edit', $client) }}"
                   class="text-sm text-amber-600 hover:text-amber-800 font-medium">Edit</a>
                <span class="text-gray-300">|</span>
                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                      onsubmit="return confirm('Delete this client? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button class="text-sm text-red-500 hover:text-red-700 font-medium">Delete</button>
                </form>
            </div>
            @endif
        </div>

        <dl class="divide-y divide-gray-100">
            @foreach ([
                ['label' => 'UUID',           'value' => $client->uuid],
                ['label' => 'Name',           'value' => $client->name],
                ['label' => 'Available From', 'value' => $client->avail_from_date?->format('d M Y') ?? 'No restriction'],
                ['label' => 'Available To',   'value' => $client->avail_to_date?->format('d M Y') ?? 'No expiry'],
                ['label' => 'Created On',     'value' => $client->created_at->format('d M Y, h:i A')],
                ['label' => 'Updated On',     'value' => $client->updated_at->format('d M Y, h:i A')],
            ] as $row)
            <div class="px-6 py-3.5 flex items-center gap-4">
                <dt class="w-36 flex-shrink-0 text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $row['label'] }}</dt>
                <dd class="text-sm text-gray-800 font-mono">{{ $row['value'] }}</dd>
            </div>
            @endforeach

            <div class="px-6 py-3.5 flex items-center gap-4">
                <dt class="w-36 flex-shrink-0 text-xs font-medium text-gray-500 uppercase tracking-wide">Status</dt>
                <dd>
                    @if ($client->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Active
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Inactive
                        </span>
                    @endif
                </dd>
            </div>

            <div class="px-6 py-3.5 flex items-center gap-4">
                <dt class="w-36 flex-shrink-0 text-xs font-medium text-gray-500 uppercase tracking-wide">API Key</dt>
                <dd class="text-sm font-mono text-gray-800 break-all">{{ $client->api_key }}</dd>
            </div>

            <div class="px-6 py-3.5 flex items-center gap-4">
                <dt class="w-36 flex-shrink-0 text-xs font-medium text-gray-500 uppercase tracking-wide">Secret Key</dt>
                <dd class="text-sm text-gray-400 italic">Hidden — regenerate to issue a new one</dd>
            </div>
        </dl>
    </div>

    @if (Auth::user()->isSuperAdmin())
    {{-- Regenerate keys --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="font-semibold text-gray-800 mb-1">Regenerate Keys</h2>
        <p class="text-sm text-gray-500 mb-4">
            Generates a new API Key and Secret Key. The client will need to update their integration immediately.
        </p>
        <form method="POST" action="{{ route('clients.regenerate-keys', $client) }}"
              onsubmit="return confirm('Regenerate keys for \'{{ $client->name }}\'? Their existing credentials will stop working immediately.')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Regenerate Keys
            </button>
        </form>
    </div>
    @endif

</div>

@endsection

@push('scripts')
<script>
function copyText(elementId, btn) {
    const text = document.getElementById(elementId).textContent;
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.textContent;
        btn.textContent = 'Copied!';
        btn.classList.add('bg-green-100', 'text-green-800', 'border-green-200');
        setTimeout(() => {
            btn.textContent = original;
            btn.classList.remove('bg-green-100', 'text-green-800', 'border-green-200');
        }, 2000);
    });
}
</script>
@endpush
