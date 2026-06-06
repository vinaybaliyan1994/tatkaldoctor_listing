<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterCountry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterCountryController extends Controller
{
    public function index(): View
    {
        $countries = MasterCountry::orderBy('name')->paginate(20);
        return view('master-countries.index', compact('countries'));
    }

    public function create(): View
    {
        return view('master-countries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:3', 'alpha', 'unique:master_countries,code'],
            'name' => ['required', 'string', 'max:191'],
        ]);

        $validated['code'] = strtoupper($validated['code']);

        MasterCountry::create($validated);

        return redirect()->route('master-countries.index')
            ->with('success', "Country \"{$validated['name']}\" ({$validated['code']}) added successfully.");
    }

    public function edit(MasterCountry $masterCountry): View
    {
        return view('master-countries.edit', compact('masterCountry'));
    }

    public function update(Request $request, MasterCountry $masterCountry): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
        ]);

        $masterCountry->update($validated);

        return redirect()->route('master-countries.index')
            ->with('success', "Country \"{$masterCountry->name}\" updated successfully.");
    }

    public function destroy(MasterCountry $masterCountry): RedirectResponse
    {
        $label = "{$masterCountry->name} ({$masterCountry->code})";
        $masterCountry->delete();

        return redirect()->route('master-countries.index')
            ->with('success', "Country \"{$label}\" deleted successfully.");
    }
}
