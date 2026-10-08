<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function index()
    {
        $features = Feature::withCount('tenants', 'plans')
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return view('owner.features.index', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'         => 'required|string|unique:features,key|regex:/^[a-z0-9_]+$/',
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'group'       => 'required|string|max:100',
            'sort_order'  => 'nullable|integer',
        ]);

        $feature = Feature::create($validated);

        AuditLog::record('feature.created', "Feature '{$feature->key}' created");

        return back()->with('success', "Feature '{$feature->label}' added.");
    }

    public function update(Request $request, Feature $feature)
    {
        $validated = $request->validate([
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'group'       => 'required|string|max:100',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $feature->update($validated);

        AuditLog::record('feature.updated', "Feature '{$feature->key}' updated");

        return back()->with('success', "Feature '{$feature->label}' updated.");
    }

    public function destroy(Feature $feature)
    {
        $label = $feature->label;
        $feature->delete();

        AuditLog::record('feature.deleted', "Feature '{$feature->key}' deleted");

        return back()->with('success', "Feature '{$label}' deleted.");
    }
}
