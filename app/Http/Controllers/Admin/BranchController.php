<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\UsesTenantDb;
use App\Models\ChurchBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BranchController extends Controller
{
    use UsesTenantDb;
    private function ensureSuperAdmin(): void
    {
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized Access. Super admin only.');
        }
    }

    /**
     * Force tenant DB connection before any operation.
     * Route model binding resolves models before this runs,
     * so use int $id + manual find instead of ChurchBranch $branch.
     */
    private function ensureTenantDb(): void
    {
        $slug = session('tenant_slug');
        if (!$slug) return;

        $tenant = \App\Models\Tenant::where('slug', $slug)->first();
        if ($tenant) {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
        }
    }

    private function findBranch(int $id): ChurchBranch
    {
        $branch = ChurchBranch::find($id);
        if (!$branch) abort(404, 'Branch not found.');
        return $branch;
    }

    public function index()
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();

        $branches = ChurchBranch::withCount(['students', 'admins'])
            ->orderBy('name')
            ->paginate(15);

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
        $this->ensureTenantDb();

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

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch created successfully.');
    }

    public function edit(int $branch)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();
        $branch = $this->findBranch($branch);
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, int $branch)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();
        $branchModel = $this->findBranch($branch);

        $validated = $request->validate([
            'name'     => 'required|string|max:255|unique:church_branches,name,' . $branchModel->id,
            'code'     => 'nullable|string|max:10|unique:church_branches,code,'  . $branchModel->id,
            'location' => 'nullable|string|max:255',
        ]);

        $branchModel->update([
            'name'     => $validated['name'],
            'code'     => $validated['code'] ? strtoupper(trim($validated['code'])) : null,
            'location' => $validated['location'] ?? null,
        ]);

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(int $branch)
    {
        $this->ensureSuperAdmin();
        $this->ensureTenantDb();
        $branchModel = $this->findBranch($branch);

        if ($branchModel->students()->exists() || $branchModel->admins()->exists()) {
            return back()->with('error', 'Branch cannot be deleted while it has students or assigned admins.');
        }

        $branchModel->delete();

        return redirect()->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
}
