<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\AttendanceAlertSetting;
use Illuminate\Http\Request;

class NotificationTemplateController extends Controller
{
    // ── Template CRUD ──────────────────────────────────────────────────────

    public function index()
    {
        $templates    = collect();
        $alertSettings = null;
        try {
            $templates = NotificationTemplate::latest()->get();
        } catch (\Throwable $e) {}
        try {
            $alertSettings = AttendanceAlertSetting::current();
        } catch (\Throwable $e) {}

        $placeholders = NotificationTemplate::placeholders();

        return view('admin.notifications.templates', compact(
            'templates', 'placeholders', 'alertSettings'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:120',
            'category' => 'required|in:general,fee,attendance,results,custom',
            'title'    => 'required|string|max:255',
            'body'     => 'required|string|max:3000',
        ]);

        NotificationTemplate::create([
            'name'       => $request->name,
            'category'   => $request->category,
            'title'      => $request->title,
            'body'       => $request->body,
            'created_by' => auth()->id(),
            'is_active'  => true,
        ]);

        return back()->with('success', 'Template "' . $request->name . '" saved successfully.');
    }

    public function update(Request $request, NotificationTemplate $template)
    {
        $request->validate([
            'name'     => 'required|string|max:120',
            'category' => 'required|in:general,fee,attendance,results,custom',
            'title'    => 'required|string|max:255',
            'body'     => 'required|string|max:3000',
        ]);

        $template->update($request->only('name', 'category', 'title', 'body'));

        return back()->with('success', 'Template updated.');
    }

    public function destroy(NotificationTemplate $template)
    {
        $template->delete();
        return back()->with('success', 'Template deleted.');
    }

    /**
     * JSON endpoint — return a single template's title + body
     * so the Send form can pre-fill them without a page reload.
     * GET /admin/notifications/templates/{template}/load
     */
    public function load(NotificationTemplate $template)
    {
        return response()->json([
            'title' => $template->title,
            'body'  => $template->body,
        ]);
    }

    /**
     * JSON endpoint — return all active templates for the picker dropdown.
     * GET /admin/notifications/templates/list
     */
    public function list()
    {
        $templates = NotificationTemplate::active()
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'title', 'body']);

        return response()->json($templates);
    }

    // ── Attendance Alert Settings ──────────────────────────────────────────

    public function saveAlertSettings(Request $request)
    {
        $request->validate([
            'present_message'    => 'nullable|string|max:3000',
            'absent_message'     => 'nullable|string|max:3000',
            'recipient_field'    => 'required|in:guardian_phone,mother_phone,father_phone,student_phone',
            'send_delay_minutes' => 'required|integer|min:0|max:120',
        ]);

        $settings = AttendanceAlertSetting::current();

        $settings->update([
            'enabled'             => $request->boolean('enabled'),
            'present_message'     => $request->present_message,
            'absent_message'      => $request->absent_message,
            'recipient_field'     => $request->recipient_field,
            'send_delay_minutes'  => (int) $request->send_delay_minutes,
        ]);

        return back()->with('success', 'Attendance alert settings saved.');
    }
}
