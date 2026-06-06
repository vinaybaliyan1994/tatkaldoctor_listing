<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterServiceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MasterService::with('parent')->orderBy('service');

        $parentId = $request->input('parent_id');
        if ($parentId !== null && $parentId !== '') {
            $query->where('parent_id', (int) $parentId);
        }

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        $services = $query->paginate(20)->withQueryString();
        $parents  = MasterService::parents()->active()->orderBy('service')->get();

        return view('master-services.index', compact('services', 'parents'));
    }

    public function create(): View
    {
        $parents = MasterService::parents()->active()->orderBy('service')->get();

        return view('master-services.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'parent_id' => 'nullable|integer|min:0',
            'service'   => 'required|string|max:191',
            'status'    => 'nullable|boolean',
        ]);

        MasterService::create([
            'parent_id' => $request->integer('parent_id'),
            'service'   => $request->input('service'),
            'status'    => $request->boolean('status'),
        ]);

        return redirect()->route('master-services.index')
                         ->with('success', 'Service added successfully.');
    }

    public function edit(MasterService $masterService): View
    {
        $parents = MasterService::parents()->active()->orderBy('service')->get();

        return view('master-services.edit', compact('masterService', 'parents'));
    }

    public function update(Request $request, MasterService $masterService): RedirectResponse
    {
        $request->validate([
            'parent_id' => 'nullable|integer|min:0',
            'service'   => 'required|string|max:191',
            'status'    => 'nullable|boolean',
        ]);

        $masterService->update([
            'parent_id' => $request->integer('parent_id'),
            'service'   => $request->input('service'),
            'status'    => $request->boolean('status'),
        ]);

        return redirect()->route('master-services.index')
                         ->with('success', 'Service updated successfully.');
    }

    public function destroy(MasterService $masterService): RedirectResponse
    {
        if ($masterService->children()->count() > 0) {
            return redirect()->back()
                             ->with('error', 'Cannot delete: sub-services exist.');
        }

        $masterService->delete();

        return redirect()->route('master-services.index')
                         ->with('success', 'Service deleted successfully.');
    }
}
