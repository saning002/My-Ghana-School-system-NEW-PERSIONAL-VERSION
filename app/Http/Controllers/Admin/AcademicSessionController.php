<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\AcademicPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::withCount('periods')
            ->with(['periods' => fn($q) => $q->orderBy('sequence')])
            ->orderByDesc('year')
            ->get();

        $activePeriod = AcademicSession::activePeriod();

        return view('admin.academic-sessions.index', compact('sessions', 'activePeriod'));
    }

    /**
     * Create a new academic session with semester or term periods auto-generated.
     */
    public function store(Request $request)
    {
        $request->validate([
            'year' => 'required|string|max:20|unique:academic_sessions,year',
            'mode' => 'required|in:semester,term',
        ]);

        DB::transaction(function () use ($request) {
            $session = AcademicSession::create([
                'year' => $request->year,
                'mode' => $request->mode,
            ]);

            $session->generatePeriods();
        });

        $count = $request->mode === 'term' ? 3 : 2;
        return back()->with('success', "Academic session {$request->year} created with {$count} " . ($request->mode === 'term' ? 'terms' : 'semesters') . ".");
    }

    /**
     * Set a period as the active (current) period.
     * Accessible by super-admins AND branch admins.
     */
    public function setActivePeriod(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:academic_periods,id',
        ]);

        $period = AcademicPeriod::with('session')->findOrFail($request->period_id);
        $period->activate();

        return back()->with('success', "Active period set to: {$period->full_label}");
    }

    /**
     * Deactivate the current active period (closes the period, no active one set).
     */
    public function deactivatePeriod(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:academic_periods,id',
        ]);

        $period = AcademicPeriod::findOrFail($request->period_id);
        $period->deactivate();

        return back()->with('success', "Period \"{$period->full_label}\" deactivated.");
    }

    /**
     * Delete a session and all its periods.
     */
    public function destroy(AcademicSession $session)
    {
        $session->delete();
        return back()->with('success', "Session {$session->year} deleted.");
    }
}
