<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterCity;
use App\Models\MasterCountry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterCityController extends Controller
{
    // Accessible by all authenticated roles
    public function index(Request $request): View
    {
        $countries = MasterCountry::orderBy('name')->get();

        $query = MasterCity::with('country')->orderBy('name');

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->country_code);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status === 'active');
        }

        $cities = $query->paginate(20)->withQueryString();

        return view('master-cities.index', compact('cities', 'countries'));
    }

    // Below methods: superadmin only (enforced via route middleware)

    public function create(): View
    {
        $countries = MasterCountry::orderBy('name')->get();
        return view('master-cities.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:3', 'exists:master_countries,code'],
            'name'         => ['required', 'string', 'max:191'],
            'status'       => ['required', 'boolean'],
        ]);

        MasterCity::create($validated);

        return redirect()->route('master-cities.index')
            ->with('success', "City \"{$validated['name']}\" added successfully.");
    }

    public function edit(MasterCity $masterCity): View
    {
        $countries = MasterCountry::orderBy('name')->get();
        return view('master-cities.edit', compact('masterCity', 'countries'));
    }

    public function update(Request $request, MasterCity $masterCity): RedirectResponse
    {
        $validated = $request->validate([
            'country_code' => ['required', 'string', 'size:3', 'exists:master_countries,code'],
            'name'         => ['required', 'string', 'max:191'],
            'status'       => ['required', 'boolean'],
        ]);

        $masterCity->update($validated);

        return redirect()->route('master-cities.index')
            ->with('success', "City \"{$masterCity->name}\" updated successfully.");
    }

    public function destroy(MasterCity $masterCity): RedirectResponse
    {
        $name = $masterCity->name;
        $masterCity->delete();

        return redirect()->route('master-cities.index')
            ->with('success', "City \"{$name}\" deleted successfully.");
    }
}
