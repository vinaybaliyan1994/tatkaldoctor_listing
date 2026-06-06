<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientSubscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientSubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $query = ClientSubscription::with(['client', 'plan'])->latest();

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->integer('client_id'));
        }
        if ($request->filled('plan_id')) {
            $query->where('subscription_plan_id', $request->integer('plan_id'));
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        $subscriptions = $query->paginate(20)->withQueryString();
        $clients        = Client::orderBy('name')->get(['id', 'name']);
        $plans          = SubscriptionPlan::orderBy('name')->get(['id', 'name']);

        return view('client-subscriptions.index', compact('subscriptions', 'clients', 'plans'));
    }

    public function create(): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $plans   = SubscriptionPlan::where('status', true)->orderBy('price')->get();

        return view('client-subscriptions.create', compact('clients', 'plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'            => 'required|exists:clients,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'start_date'           => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'status'               => ['required', Rule::in(['active', 'expired', 'cancelled', 'pending'])],
            'payment_status'       => ['required', Rule::in(['free', 'paid', 'unpaid', 'failed'])],
            'amount'               => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        ClientSubscription::create($validated);

        return redirect()->route('client-subscriptions.index')
                         ->with('success', 'Subscription created successfully.');
    }

    public function show(Request $request, ClientSubscription $clientSubscription): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $clientSubscription->load(['client', 'plan']);

        return view('client-subscriptions.show', compact('clientSubscription'));
    }

    public function edit(ClientSubscription $clientSubscription): View
    {
        $clients = Client::orderBy('name')->get(['id', 'name']);
        $plans   = SubscriptionPlan::where('status', true)->orderBy('price')->get();

        return view('client-subscriptions.edit', compact('clientSubscription', 'clients', 'plans'));
    }

    public function update(Request $request, ClientSubscription $clientSubscription): RedirectResponse
    {
        $validated = $request->validate([
            'client_id'            => 'required|exists:clients,id',
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'start_date'           => 'required|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'status'               => ['required', Rule::in(['active', 'expired', 'cancelled', 'pending'])],
            'payment_status'       => ['required', Rule::in(['free', 'paid', 'unpaid', 'failed'])],
            'amount'               => 'required|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        $clientSubscription->update($validated);

        return redirect()->route('client-subscriptions.index')
                         ->with('success', 'Subscription updated successfully.');
    }

    public function destroy(ClientSubscription $clientSubscription): RedirectResponse
    {
        $clientSubscription->delete();

        return redirect()->route('client-subscriptions.index')
                         ->with('success', 'Subscription deleted.');
    }
}
