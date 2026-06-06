<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'avail_from_date' => 'nullable|date',
            'avail_to_date'   => 'nullable|date|after_or_equal:avail_from_date',
            'status'          => 'required|in:active,inactive',
        ]);

        $client      = Client::create($validated);
        $plainSecret = $client->getDecryptedSecretKey();

        // Flash plain secret key once — it will not be shown again
        return redirect()->route('clients.show', $client)
            ->with('secret_key', $plainSecret)
            ->with('api_key', $client->api_key)
            ->with('success', 'Client created. Copy the secret key now — it will not be shown again.');
    }

    public function show(Client $client): View
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:191',
            'avail_from_date' => 'nullable|date',
            'avail_to_date'   => 'nullable|date|after_or_equal:avail_from_date',
            'status'          => 'required|in:active,inactive',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', "Client \"{$client->name}\" updated successfully.");
    }

    public function destroy(Client $client): RedirectResponse
    {
        $name = $client->name;
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Client \"{$name}\" deleted successfully.");
    }

    public function regenerateKeys(Client $client): RedirectResponse
    {
        $newApiKey    = Client::generateApiKey();
        $newSecretKey = Client::generateSecretKey();

        $client->update([
            'api_key'    => $newApiKey,
            'secret_key' => Crypt::encryptString($newSecretKey),
        ]);

        return redirect()->route('clients.show', $client)
            ->with('secret_key', $newSecretKey)
            ->with('api_key', $newApiKey)
            ->with('success', 'Keys regenerated. Copy the new secret key now — it will not be shown again.');
    }
}
