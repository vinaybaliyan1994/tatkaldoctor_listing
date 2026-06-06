<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MasterQualification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterQualificationController extends Controller
{
    public function index(Request $request): View
    {
        $query = MasterQualification::orderBy('qualification');

        if ($request->status === 'active') {
            $query->where('status', true);
        } elseif ($request->status === 'inactive') {
            $query->where('status', false);
        }

        $qualifications = $query->paginate(20)->withQueryString();

        return view('master-qualifications.index', compact('qualifications'));
    }

    public function create(): View
    {
        return view('master-qualifications.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'qualification' => 'required|string|max:191|unique:master_qualifications,qualification',
            'status'        => 'nullable|boolean',
        ]);

        MasterQualification::create([
            'qualification' => strtoupper(trim($request->input('qualification'))),
            'status'        => $request->boolean('status'),
        ]);

        return redirect()->route('master-qualifications.index')
                         ->with('success', 'Qualification added successfully.');
    }

    public function edit(MasterQualification $masterQualification): View
    {
        return view('master-qualifications.edit', compact('masterQualification'));
    }

    public function update(Request $request, MasterQualification $masterQualification): RedirectResponse
    {
        $request->validate([
            'qualification' => [
                'required', 'string', 'max:191',
                Rule::unique('master_qualifications', 'qualification')->ignore($masterQualification->id),
            ],
            'status' => 'nullable|boolean',
        ]);

        $masterQualification->update([
            'qualification' => strtoupper(trim($request->input('qualification'))),
            'status'        => $request->boolean('status'),
        ]);

        return redirect()->route('master-qualifications.index')
                         ->with('success', 'Qualification updated successfully.');
    }

    public function destroy(MasterQualification $masterQualification): RedirectResponse
    {
        $masterQualification->delete();

        return redirect()->route('master-qualifications.index')
                         ->with('success', 'Qualification deleted successfully.');
    }
}
