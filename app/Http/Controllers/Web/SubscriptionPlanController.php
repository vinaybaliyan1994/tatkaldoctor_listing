<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): View
    {
        $query = SubscriptionPlan::query()->orderBy('price');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        $plans = $query->paginate(20)->withQueryString();

        return view('subscription-plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('subscription-plans.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:191',
            'slug'             => 'nullable|string|max:191|unique:subscription_plans,slug',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1',
            'max_staff'        => 'nullable|integer|min:1',
            'max_locations'    => 'nullable|integer|min:1',
            'max_appointments' => 'nullable|integer|min:1',
            'features'         => 'nullable|string',
            'status'           => 'nullable|boolean',
        ]);

        $validated['slug']    = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['status']  = $request->boolean('status');
        $validated['features'] = $this->parseFeatures($validated['features'] ?? '');

        SubscriptionPlan::create($validated);

        return redirect()->route('subscription-plans.index')
                         ->with('success', 'Subscription plan created successfully.');
    }

    public function show(SubscriptionPlan $subscriptionPlan): View
    {
        return view('subscription-plans.show', compact('subscriptionPlan'));
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        return view('subscription-plans.edit', compact('subscriptionPlan'));
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:191',
            'slug'             => 'nullable|string|max:191|unique:subscription_plans,slug,'.$subscriptionPlan->id,
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'duration_days'    => 'required|integer|min:1',
            'max_staff'        => 'nullable|integer|min:1',
            'max_locations'    => 'nullable|integer|min:1',
            'max_appointments' => 'nullable|integer|min:1',
            'features'         => 'nullable|string',
            'status'           => 'nullable|boolean',
        ]);

        $validated['slug']     = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['status']   = $request->boolean('status');
        $validated['features'] = $this->parseFeatures($validated['features'] ?? '');

        $subscriptionPlan->update($validated);

        return redirect()->route('subscription-plans.index')
                         ->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        if ($subscriptionPlan->clientSubscriptions()->count() > 0) {
            return redirect()->route('subscription-plans.index')
                             ->with('error', 'Cannot delete plan — it has active client subscriptions.');
        }

        $subscriptionPlan->delete();

        return redirect()->route('subscription-plans.index')
                         ->with('success', 'Subscription plan deleted.');
    }

    private function parseFeatures(string $raw): ?array
    {
        $lines = array_filter(array_map('trim', explode("\n", $raw)));

        return empty($lines) ? null : array_values($lines);
    }
}
