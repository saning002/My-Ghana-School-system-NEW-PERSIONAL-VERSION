<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChurchBranch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    private function ensureSuperAdmin()
    {
        if (! auth()->check() || ! auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized Access. Super admin only.');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        $branches = ChurchBranch::withCount(['students', 'admins'])->orderBy('name')->paginate(15);
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:church_branches,name',
            'code'     => 'nullable|string|max:10|unique:church_branches,code',
            'location' => 'nullable|string|max:255',
        ]);

        ChurchBranch::create([
            'name'     => $validated['name'],
            'code'     => $validated['code'] ? strtoupper(trim($validated['code'])) : null,
            'location' => $validated['location'] ?? null,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:church_branches,name,' . $branch->id,
            'code'     => 'nullable|string|max:10|unique:church_branches,code,' . $branch->id,
            'location' => 'nullable|string|max:255',
        ]);

        $branch->update([
            'name'     => $validated['name'],
            'code'     => $validated['code'] ? strtoupper(trim($validated['code'])) : null,
            'location' => $validated['location'] ?? null,
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(ChurchBranch $branch)
    {
        $this->ensureSuperAdmin();

        if ($branch->students()->exists() || $branch->admins()->exists()) {
            return back()->with('error', 'Branch cannot be deleted while it has students or assigned admins.');
        }

        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully.');
    }
}
