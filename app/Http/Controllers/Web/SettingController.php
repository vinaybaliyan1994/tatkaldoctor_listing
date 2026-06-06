<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const TYPES = ['string', 'text', 'boolean', 'integer', 'float', 'json'];

    public function index(Request $request): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $query = Setting::query()->orderBy('group')->orderBy('key');

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }
        if ($request->is_public === '1') {
            $query->where('is_public', true);
        } elseif ($request->is_public === '0') {
            $query->where('is_public', false);
        }
        if ($request->filled('search')) {
            $query->where('key', 'like', '%'.$request->search.'%');
        }

        $settings = $query->paginate(30)->withQueryString();
        $groups   = Setting::select('group')->distinct()->whereNotNull('group')->orderBy('group')->pluck('group');

        return view('settings.index', compact('settings', 'groups'));
    }

    public function create(): View
    {
        return view('settings.create', ['types' => self::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'key'       => 'required|string|max:191|unique:settings,key',
            'value'     => 'nullable|string',
            'type'      => ['required', Rule::in(self::TYPES)],
            'group'     => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        Setting::create($validated);

        return redirect()->route('settings.index')
                         ->with('success', 'Setting created successfully.');
    }

    public function show(Request $request, Setting $setting): View
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return view('settings.show', compact('setting'));
    }

    public function edit(Setting $setting): View
    {
        return view('settings.edit', ['setting' => $setting, 'types' => self::TYPES]);
    }

    public function update(Request $request, Setting $setting): RedirectResponse
    {
        $validated = $request->validate([
            'key'       => 'required|string|max:191|unique:settings,key,'.$setting->id,
            'value'     => 'nullable|string',
            'type'      => ['required', Rule::in(self::TYPES)],
            'group'     => 'nullable|string|max:100',
            'is_public' => 'nullable|boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        $setting->update($validated);

        return redirect()->route('settings.index')
                         ->with('success', 'Setting updated successfully.');
    }

    public function destroy(Setting $setting): RedirectResponse
    {
        $setting->delete();

        return redirect()->route('settings.index')
                         ->with('success', 'Setting deleted.');
    }
}
