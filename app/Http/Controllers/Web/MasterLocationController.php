<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterCountry;
use App\Models\MasterLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterLocationController extends Controller
{
    public function index(Request $request): View
    {
        $query = MasterLocation::with(['city', 'city.country'])
            ->orderBy('location');

        if ($request->filled('master_city_id')) {
            $query->where('master_city_id', $request->integer('master_city_id'));
        }

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        $locations = $query->paginate(20)->withQueryString();

        $countries = MasterCountry::with(['cities' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('master-locations.index', compact('locations', 'countries'));
    }

    public function create(): View
    {
        $countries = MasterCountry::with(['cities' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('master-locations.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'master_city_id' => 'required|exists:master_cities,id',
            'location'       => 'required|string|max:191',
            'status'         => 'nullable|boolean',
        ]);

        MasterLocation::create([
            'master_city_id' => $request->integer('master_city_id'),
            'location'       => $request->input('location'),
            'status'         => $request->boolean('status'),
        ]);

        return redirect()->route('master-locations.index')
                         ->with('success', 'Location added successfully.');
    }

    public function edit(MasterLocation $masterLocation): View
    {
        $masterLocation->load('city.country');

        return view('master-locations.edit', compact('masterLocation'));
    }

    public function update(Request $request, MasterLocation $masterLocation): RedirectResponse
    {
        $request->validate([
            'master_city_id' => 'required|exists:master_cities,id',
            'location'       => 'required|string|max:191',
            'status'         => 'nullable|boolean',
        ]);

        $masterLocation->update([
            'master_city_id' => $request->integer('master_city_id'),
            'location'       => $request->input('location'),
            'status'         => $request->boolean('status'),
        ]);

        return redirect()->route('master-locations.index')
                         ->with('success', 'Location updated successfully.');
    }

    public function destroy(MasterLocation $masterLocation): RedirectResponse
    {
        $masterLocation->delete();

        return redirect()->route('master-locations.index')
                         ->with('success', 'Location deleted successfully.');
    }
}
