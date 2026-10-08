<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount('tenants')->with('features')->orderBy('price')->get();
        return view('owner.plans.index', compact('plans'));
    }

    public function create()
    {
        $features = Feature::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
        return view('owner.plans.create', compact('features'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'is_active'     => 'nullable|boolean',
            'max_students'  => 'nullable|integer|min:0',
            'max_lecturers' => 'nullable|integer|min:0',
            'features'      => 'nullable|array',
            'features.*'    => 'exists:features,id',
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $plan = Plan::create($validated);

        if (!empty($validated['features'])) {
            $plan->features()->sync($validated['features']);
        }

        AuditLog::record('plan.created', "Plan '{$plan->name}' created");

        return redirect()->route('owner.plans.index')
            ->with('success', "Plan '{$plan->name}' created.");
    }

    public function edit(Plan $plan)
    {
        $features = Feature::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
        $selectedFeatureIds = $plan->features()->pluck('features.id')->toArray();
        return view('owner.plans.edit', compact('plan', 'features', 'selectedFeatureIds'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'price'         => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,yearly',
            'is_active'     => 'nullable|boolean',
            'max_students'  => 'nullable|integer|min:0',
            'max_lecturers' => 'nullable|integer|min:0',
            'features'      => 'nullable|array',
            'features.*'    => 'exists:features,id',
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $plan->update($validated);
        $plan->features()->sync($validated['features'] ?? []);

        AuditLog::record('plan.updated', "Plan '{$plan->name}' updated");

        return redirect()->route('owner.plans.index')
            ->with('success', "Plan '{$plan->name}' updated.");
    }

    public function destroy(Plan $plan)
    {
        if ($plan->tenants()->count() > 0) {
            return back()->with('error', "Cannot delete plan '{$plan->name}' — {$plan->tenants()->count()} school(s) are on it.");
        }

        $name = $plan->name;
        $plan->delete();

        AuditLog::record('plan.deleted', "Plan '{$name}' deleted");

        return redirect()->route('owner.plans.index')->with('success', "Plan '{$name}' deleted.");
    }
}
