@extends('layouts.app')

@section('title', 'Admin Guide')
@section('subtitle', 'System Reference & Explainer for Administrators')

@push('styles')
<style>
    .guide-hero {
        background: #0B1121;
        border-radius: 1.5rem;
        border: 1px solid rgba(212,160,23,0.15);
        position: relative;
        overflow: hidden;
    }
    .guide-hero::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 220px; height: 220px;
        background: radial-gradient(circle, rgba(212,160,23,0.12) 0%, transparent 70%);
        border-radius: 50%;
    }
    .guide-hero::after {
        content: '';
        position: absolute;
        bottom: -40px; left: 40px;
        width: 150px; height: 150px;
        background: radial-gradient(circle, rgba(212,160,23,0.07) 0%, transparent 70%);
        border-radius: 50%;
    }
    /* Tabs */
    .guide-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .guide-tab {
        display: flex; align-items: center; gap: 0.5rem;
        padding: 0.6rem 1.1rem;
        border-radius: 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(.4,0,.2,1);
        border: 1px solid transparent;
        color: #64748b;
        background: #f8fafc;
        user-select: none;
        white-space: nowrap;
    }
    .guide-tab:hover { background: #f1f5f9; color: #334155; }
    .guide-tab.active {
        background: linear-gradient(135deg, #D4A017, #b8860b);
        color: #fff;
        border-color: rgba(255,255,255,0.2);
        box-shadow: 0 4px 16px -4px rgba(212,160,23,0.5);
        transform: translateY(-1px);
    }
    .guide-tab i { font-size: 0.85rem; }
    /* Panels */
    .guide-panel { animation: fadeUp 0.3s ease; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    /* Feature cards */
    .feature-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px);
        border-radius: 1.25rem;
        border: 1px solid rgba(255,255,255,0.6);
        box-shadow: 0 6px 30px -8px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(.4,0,.2,1);
        padding: 1.4rem;
    }
    .feature-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 40px -10px rgba(0,0,0,0.1);
    }
    /* Icon badges */
    .icon-badge {
        width: 46px; height: 46px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.15rem;
        flex-shrink: 0;
    }
    .badge-gold    { background: linear-gradient(135deg,rgba(212,160,23,.15),rgba(184,134,11,.1)); color: #b8860b; }
    .badge-blue    { background: linear-gradient(135deg,rgba(59,130,246,.15),rgba(37,99,235,.1)); color: #2563eb; }
    .badge-green   { background: linear-gradient(135deg,rgba(34,197,94,.15),rgba(22,163,74,.1)); color: #16a34a; }
    .badge-purple  { background: linear-gradient(135deg,rgba(168,85,247,.15),rgba(126,34,206,.1)); color: #7c3aed; }
    .badge-rose    { background: linear-gradient(135deg,rgba(244,63,94,.15),rgba(225,29,72,.1)); color: #e11d48; }
    .badge-cyan    { background: linear-gradient(135deg,rgba(6,182,212,.15),rgba(8,145,178,.1)); color: #0891b2; }
    .badge-amber   { background: linear-gradient(135deg,rgba(245,158,11,.15),rgba(217,119,6,.1)); color: #d97706; }
    .badge-dark    { background: linear-gradient(135deg,rgba(15,23,42,.12),rgba(30,41,59,.08)); color: #1e293b; }
    /* Section headings */
    .section-heading {
        display: flex; align-items: center; gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .section-heading-bar {
        width: 4px; height: 24px; border-radius: 2px;
        background: linear-gradient(180deg, #D4A017, #b8860b);
    }
    /* Step flow */
    .step-flow { display: flex; flex-direction: column; gap: 0; }
    .step-item {
        display: flex; gap: 1rem; position: relative;
        padding-bottom: 1.5rem;
    }
    .step-item:last-child { padding-bottom: 0; }
    .step-item:last-child .step-line { display: none; }
    .step-num {
        width: 36px; height: 36px; border-radius: 50%;
        background: linear-gradient(135deg, #D4A017, #b8860b);
        color: #fff; font-size: 0.8rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 4px 12px -3px rgba(212,160,23,0.5);
        position: relative; z-index: 1;
    }
    .step-connector {
        position: absolute; left: 17px; top: 36px; bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, rgba(212,160,23,0.4), rgba(212,160,23,0.05));
    }
    /* Grade table */
    .grade-row {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.7rem 1rem;
        border-radius: 0.75rem;
        margin-bottom: 0.4rem;
        transition: background 0.2s;
    }
    .grade-row:hover { background: #f8fafc; }
    .grade-badge {
        width: 52px; height: 32px; border-radius: 8px;
        font-size: 0.8rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .grade-gp {
        width: 50px; text-align: center;
        font-size: 0.85rem; font-weight: 700; color: #0F172A;
        flex-shrink: 0;
    }
    /* Alert box */
    .guide-alert {
        border-radius: 1rem;
        padding: 1rem 1.1rem;
        display: flex; gap: 0.75rem; align-items: flex-start;
        margin-bottom: 1rem;
        font-size: 0.82rem; line-height: 1.55;
    }
    .guide-alert-icon { width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; }
    /* Fee formula card */
    .formula-box {
        background: #0B1121;
        border: 1px solid rgba(212,160,23,0.2);
        border-radius: 1rem;
        padding: 1.25rem 1.5rem;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #e2e8f0;
        line-height: 1.7;
    }
    .formula-box .hl { color: #FDE047; font-weight: 700; }
    .formula-box .comment { color: #64748b; }
    /* Tips list */
    .tip-item {
        display: flex; align-items: flex-start; gap: 0.6rem;
        padding: 0.5rem 0; font-size: 0.83rem; color: #374151; line-height: 1.5;
    }
    .tip-dot { width: 7px; height: 7px; border-radius: 50%; background: #D4A017; margin-top: 6px; flex-shrink: 0; }
    /* Faq */
    .faq-item details { border-radius: 0.75rem; overflow: hidden; margin-bottom: 0.5rem; }
    .faq-item summary {
        padding: 0.85rem 1rem;
        font-size: 0.85rem; font-weight: 600; color: #1e293b;
        cursor: pointer; list-style: none;
        display: flex; align-items: center; justify-content: space-between;
        background: #f8fafc;
        border-radius: 0.75rem;
        user-select: none;
        transition: background 0.2s;
    }
    .faq-item summary:hover { background: #f1f5f9; }
    .faq-item details[open] summary { background: rgba(212,160,23,.1); color: #78520a; border-radius: 0.75rem 0.75rem 0 0; }
    .faq-body { padding: 0.85rem 1rem; font-size: 0.82rem; line-height: 1.65; color: #475569; background: #fafafa; border-radius: 0 0 0.75rem 0.75rem; border-top: 1px solid #e2e8f0; }
    /* Role box */
    .role-card { border-left: 3px solid; padding: 0.85rem 1rem; border-radius: 0 0.75rem 0.75rem 0; margin-bottom: 0.5rem; }
</style>
@endpush

@section('content')
<div x-data="{ activeTab: 'overview' }">
{{-- HERO --}}
<div class="guide-hero p-6 mb-6 relative z-10">
    <div class="flex items-center gap-4 relative z-10">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
             style="background:linear-gradient(135deg,rgba(212,160,23,.25),rgba(184,134,11,.15));border:1px solid rgba(212,160,23,0.3)">
            <i class="fas fa-book-open text-yellow-400 text-2xl"></i>
        </div>
        <div>
            <h1 class="text-white text-xl font-bold tracking-tight">Admin System Guide</h1>
            <p class="text-white/50 text-sm mt-0.5">Your complete reference for managing {{ $schoolName ?? 'the school' }}</p>
        </div>
        <div class="ml-auto hidden md:flex items-center gap-2 px-4 py-2 rounded-xl"
             style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08)">
            <i class="fas fa-shield-alt text-yellow-400 text-sm"></i>
            <span class="text-white/60 text-xs font-medium">Admin Reference</span>
        </div>
    </div>
</div>

{{-- TAB BAR --}}
<div class="feature-card mb-5 p-4">
    <div class="guide-tabs" id="guide-tabs">
        <button class="guide-tab" :class="{'active': activeTab === 'overview'}" @click="activeTab = 'overview'" id="tab-overview">
            <i class="fas fa-home"></i> Overview
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'students'}" @click="activeTab = 'students'" id="tab-students">
            <i class="fas fa-user-graduate"></i> Students
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'academics'}" @click="activeTab = 'academics'" id="tab-academics">
            <i class="fas fa-book-open"></i> Academics
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'grading'}" @click="activeTab = 'grading'" id="tab-grading">
            <i class="fas fa-star"></i> Grading
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'fees'}" @click="activeTab = 'fees'" id="tab-fees">
            <i class="fas fa-coins"></i> Fees
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'reports'}" @click="activeTab = 'reports'" id="tab-reports">
            <i class="fas fa-chart-bar"></i> Reports
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'roles'}" @click="activeTab = 'roles'" id="tab-roles">
            <i class="fas fa-users-cog"></i> Roles
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'exams_new'}" @click="activeTab = 'exams_new'" id="tab-exams_new">
            <i class="fas fa-star text-yellow-400"></i> Exams (New)
        </button>
        <button class="guide-tab" :class="{'active': activeTab === 'faq'}" @click="activeTab = 'faq'" id="tab-faq">
            <i class="fas fa-question-circle"></i> FAQ
        </button>
    </div>
</div>

{{-- ========== PANEL: OVERVIEW ========== --}}
<div class="guide-panel" x-show="activeTab === 'overview'" id="panel-overview">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Dashboard & System Overview</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-gold"><i class="fas fa-chart-pie"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Dashboard</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Live count of total & active students, teachers, programs, courses, attendance rate, and fee collection summary. Branch admins see only their branch's data.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-blue"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Students</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Register new students with personal details, photo, church branch, and initial program. Manage edits, promote to next program, or view full academic profiles.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-green"><i class="fas fa-chalkboard-teacher"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Teachers</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Create and manage teacher accounts. Teachers can be associated with specific branches. Their accounts have separate access credentials.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-purple"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Programs & Courses</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Programs are arranged by sequence number (1 = first year). Each program contains multiple courses with individual credit units used for SGPA calculation.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-cyan"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Attendance</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Record daily attendance per student per course. The dashboard shows an attendance rate (present / total records × 100%). Students can view their own attendance in the portal.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-rose"><i class="fas fa-file-alt"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Exams & Results</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Enter exam scores per student per course. The system auto-grades and generates report cards with SGPA, grade classification, and class position rankings.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-amber"><i class="fas fa-coins"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Fees & Payments</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Set program-level fees (tuition + exam) and record individual student payments. The system automatically calculates balances across all programs a student has been enrolled in.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-dark"><i class="fas fa-bell"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Notifications</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Compose and broadcast messages that appear in the student portal. Students can mark notifications as read. Expired or old notifications can be deleted.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-gold"><i class="fas fa-toggle-on"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Student Portal Toggle</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Open or close the student portal at any time. When closed, students see a custom message you set instead of their data. Useful for exam lockdowns or maintenance.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="guide-alert" style="background:rgba(212,160,23,0.08);border:1px solid rgba(212,160,23,0.2)">
        <div class="guide-alert-icon" style="background:rgba(212,160,23,0.15);color:#b8860b"><i class="fas fa-lightbulb"></i></div>
        <div>
            <p class="font-semibold text-sm text-yellow-800 mb-0.5">Quick Tip — Navigation</p>
            <p class="text-yellow-700">Use the sidebar on desktop or the bottom bar on mobile to navigate between sections. The active section is always highlighted in gold.</p>
        </div>
    </div>
</div>

{{-- ========== PANEL: STUDENTS ========== --}}
<div class="guide-panel" x-show="activeTab === 'students'" id="panel-students">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Managing Students</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-plus-circle text-green-500"></i> Registering a New Student
            </p>
            <div class="step-flow">
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">1</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Go to Students → Add New Student</p>
                        <p class="text-xs text-slate-500 mt-0.5">Fill in full name, date of birth, gender, and contact information.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">2</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Assign Branch & Program</p>
                        <p class="text-xs text-slate-500 mt-0.5">Select the student's church branch and starting program (e.g. First Semester). The student ID is auto-generated.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">3</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Upload Photo (Optional)</p>
                        <p class="text-xs text-slate-500 mt-0.5">A student photo can be uploaded and is displayed on their portal profile and printed report cards.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">4</div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Set Portal Credentials</p>
                        <p class="text-xs text-slate-500 mt-0.5">Students log into the portal using their Student ID and a name-matching password. No separate passwords are needed.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-arrow-up text-blue-500"></i> Student Promotion
            </p>
            <div class="step-flow">
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">1</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Only Active Students Can Be Promoted</p>
                        <p class="text-xs text-slate-500 mt-0.5">A student must have status <span class="font-semibold text-green-600">active</span> to use the Promote button on their profile.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">2</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Next Program by Sequence</p>
                        <p class="text-xs text-slate-500 mt-0.5">The system finds the program with the next highest sequence number and assigns the student to it automatically.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">3</div>
                        <div class="step-connector"></div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Graduation if No Next Program</p>
                        <p class="text-xs text-slate-500 mt-0.5">If the student is already in the last program (highest sequence), their status automatically changes to <span class="font-semibold text-purple-600">graduated</span>.</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="relative">
                        <div class="step-num">4</div>
                    </div>
                    <div class="pt-1">
                        <p class="text-sm font-semibold text-slate-700">Promotion History Recorded</p>
                        <p class="text-xs text-slate-500 mt-0.5">Every promotion creates a history entry (from_program → to_program) used for accurate fee billing and audit trails.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="feature-card lg:col-span-2">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-file-archive text-amber-500"></i> Bulk Import with Photos
            </p>
            <div class="step-flow md:flex md:gap-4 md:flex-row">
                <div class="step-item md:flex-1">
                    <div class="relative"><div class="step-num">1</div><div class="step-connector hidden md:block" style="width:100%;height:2px;top:17px;left:36px;bottom:auto"></div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Download Template</p><p class="text-xs text-slate-500 mt-0.5">Go to Students and download the Excel Template. Enter student data and specify image filenames (e.g. <code>john.jpg</code>) in the Photo columns.</p></div>
                </div>
                <div class="step-item md:flex-1">
                    <div class="relative"><div class="step-num">2</div><div class="step-connector hidden md:block" style="width:100%;height:2px;top:17px;left:36px;bottom:auto"></div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Create a ZIP File</p><p class="text-xs text-slate-500 mt-0.5">Place your filled Excel file and a <code>photos/</code> folder containing all the images into a single ZIP archive.</p></div>
                </div>
                <div class="step-item md:flex-1">
                    <div class="relative"><div class="step-num">3</div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Upload & Import</p><p class="text-xs text-slate-500 mt-0.5">Click <strong>Bulk Add</strong> on the Students page and select your ZIP file. The system will unpack it and automatically link the photos.</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="feature-card">
        <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-blue-400"></i> Student Status Reference
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="role-card" style="border-color:#22c55e;background:rgba(34,197,94,0.06)">
                <p class="font-bold text-sm text-green-700 mb-1"><i class="fas fa-circle text-green-500 mr-1.5" style="font-size:0.55rem"></i>Active</p>
                <p class="text-xs text-green-600">Student is currently enrolled and studying. Can be promoted.</p>
            </div>
            <div class="role-card" style="border-color:#8b5cf6;background:rgba(139,92,246,0.06)">
                <p class="font-bold text-sm text-purple-700 mb-1"><i class="fas fa-circle text-purple-500 mr-1.5" style="font-size:0.55rem"></i>Graduated</p>
                <p class="text-xs text-purple-600">Student has completed all programs. Cannot be promoted further.</p>
            </div>
            <div class="role-card" style="border-color:#f59e0b;background:rgba(245,158,11,0.06)">
                <p class="font-bold text-sm text-amber-700 mb-1"><i class="fas fa-circle text-amber-500 mr-1.5" style="font-size:0.55rem"></i>Inactive</p>
                <p class="text-xs text-amber-600">Student is suspended or has left. Cannot be promoted until reactivated.</p>
            </div>
        </div>
    </div>
</div>

{{-- ========== PANEL: ACADEMICS ========== --}}
<div class="guide-panel" x-show="activeTab === 'academics'" id="panel-academics">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Programs, Courses & Exams</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-graduation-cap text-purple-500"></i> Program Sequencing Rules
            </p>
            <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
                <div class="tip-item"><div class="tip-dot"></div><span>Programs are ordered by their <strong>Sequence</strong> field — e.g. First Semester = 1, Second Semester = 2, etc.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>Always ensure sequence numbers are unique and sequential — gaps are fine but duplicates cause promotion errors.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>A student starts at the lowest-sequenced program they were assigned to at registration.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>When promoted, the system automatically finds the program with the next highest sequence number.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>Each program should have its own set of courses and its own fee configuration.</span></div>
            </div>
        </div>
        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-book-open text-blue-500"></i> Course Credits
            </p>
            <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
                <div class="tip-item"><div class="tip-dot"></div><span>Each course has a <strong>Credit Unit</strong> value (default: 3). This determines its weight in SGPA calculation.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>A course with 4 credits contributes more to SGPA than one with 2 credits.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>The SGPA formula is: <strong>Sum(Credit × Grade Point) ÷ Sum(Credits)</strong>.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>Set credit values accurately when creating courses — they affect every student's calculated GPA.</span></div>
                <div class="tip-item"><div class="tip-dot"></div><span>Courses are shared across the system but are linked to specific programs in the grading module.</span></div>
            </div>
        </div>
    </div>

    <div class="feature-card mb-5">
        <p class="font-bold text-sm text-slate-800 mb-4 flex items-center gap-2">
            <i class="fas fa-file-alt text-rose-500"></i> Entering Exam Scores
        </p>
        <div class="step-flow">
            <div class="step-item">
                <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Go to Exams & Results → Enter Scores</p><p class="text-xs text-slate-500 mt-0.5">Choose a program, then a student. Pick which attempt to enter (e.g. Attempt 1 for the first sitting).</p></div>
            </div>
            <div class="step-item">
                <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Enter Both Quiz and Exam Scores</p><p class="text-xs text-slate-500 mt-0.5">For each course you now have two columns — <strong class="text-blue-700">Quiz Score</strong> and <strong class="text-orange-700">Exam Score</strong>, each out of 100. A live <strong>Aggregate</strong> column shows the weighted total as you type.</p></div>
            </div>
            <div class="step-item">
                <div class="relative"><div class="step-num">3</div><div class="step-connector"></div></div>
                <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Grades Are Auto-Calculated</p><p class="text-xs text-slate-500 mt-0.5">On save, the system applies the quiz/exam percentage weighting to produce the final aggregate, then assigns letter grades, grade points, and remarks automatically.</p></div>
            </div>
            <div class="step-item">
                <div class="relative"><div class="step-num">4</div></div>
                <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Download or Bulk Print</p><p class="text-xs text-slate-500 mt-0.5">Use <strong>Download PDF</strong> / <strong>Export Excel</strong> on a student's report card, or use the <strong>Bulk Download</strong> button on the Exams index to print all students at once. See the <button class="underline text-yellow-700 font-semibold" @click="activeTab='exams_new'">Exams (New)</button> tab for details.</p></div>
            </div>
        </div>
    </div>

    <div class="guide-alert" style="background:rgba(34,197,94,0.07);border:1px solid rgba(34,197,94,0.2)">
        <div class="guide-alert-icon" style="background:rgba(34,197,94,0.15);color:#16a34a"><i class="fas fa-check-circle"></i></div>
        <div>
            <p class="font-semibold text-sm text-green-800 mb-0.5">Quiz + Exam Grading is Now Active</p>
            <p class="text-green-700 text-xs">The system now supports separate quiz and exam scores with configurable percentage weights. The default is <strong>Quiz 30% + Exam 70%</strong>. You can change this any time from the Exam Sheet (Bulk Entry) page. See the <button class="underline text-green-800 font-semibold" @click="activeTab='exams_new'">Exams (New)</button> tab for a full walkthrough.</p>
        </div>
    </div>
</div>

{{-- ========== PANEL: GRADING ========== --}}
<div class="guide-panel" x-show="activeTab === 'grading'" id="panel-grading">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Grading Scale, SGPA & Class Rankings</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-star text-yellow-500"></i> Official Grading Scale
            </p>
            <div class="grade-row" style="background:rgba(212,160,23,0.07)">
                <div class="grade-badge" style="background:#D4A017;color:#fff">A1</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Distinction</p>
                    <p class="text-xs text-slate-500">Score ≥ 80</p>
                </div>
                <div class="grade-gp">4.0</div>
                <div class="text-xs font-bold text-yellow-700 px-2 py-1 rounded-lg" style="background:rgba(212,160,23,.12)">A1</div>
            </div>
            <div class="grade-row">
                <div class="grade-badge" style="background:#3b82f6;color:#fff">A2</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Upper Division</p>
                    <p class="text-xs text-slate-500">Score 70 – 79</p>
                </div>
                <div class="grade-gp">3.7</div>
                <div class="text-xs font-bold text-blue-700 px-2 py-1 rounded-lg" style="background:rgba(59,130,246,.12)">A2</div>
            </div>
            <div class="grade-row">
                <div class="grade-badge" style="background:#06b6d4;color:#fff">A3</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Lower Division</p>
                    <p class="text-xs text-slate-500">Score 60 – 69</p>
                </div>
                <div class="grade-gp">3.3</div>
                <div class="text-xs font-bold text-cyan-700 px-2 py-1 rounded-lg" style="background:rgba(6,182,212,.12)">A3</div>
            </div>
            <div class="grade-row">
                <div class="grade-badge" style="background:#22c55e;color:#fff">B1</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Credit</p>
                    <p class="text-xs text-slate-500">Score 50 – 59</p>
                </div>
                <div class="grade-gp">3.0</div>
                <div class="text-xs font-bold text-green-700 px-2 py-1 rounded-lg" style="background:rgba(34,197,94,.12)">B1</div>
            </div>
            <div class="grade-row">
                <div class="grade-badge" style="background:#f59e0b;color:#fff">B2</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Pass</p>
                    <p class="text-xs text-slate-500">Score 40 – 49</p>
                </div>
                <div class="grade-gp">2.0</div>
                <div class="text-xs font-bold text-amber-700 px-2 py-1 rounded-lg" style="background:rgba(245,158,11,.12)">B2</div>
            </div>
            <div class="grade-row">
                <div class="grade-badge" style="background:#ef4444;color:#fff">F</div>
                <div class="flex-1">
                    <p class="text-xs font-bold text-slate-700">Fail</p>
                    <p class="text-xs text-slate-500">Score below 40</p>
                </div>
                <div class="grade-gp">0.0</div>
                <div class="text-xs font-bold text-red-700 px-2 py-1 rounded-lg" style="background:rgba(239,68,68,.12)">F</div>
            </div>
            <p class="text-xs text-slate-400 text-center mt-3">Overall average ≥ 40 = Promoted | Below 40 = Repeat Program</p>
        </div>

        <div>
            <div class="feature-card mb-4">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-calculator text-green-500"></i> How SGPA is Calculated
                </p>
                <div class="formula-box mb-3">
                    <span class="comment">-- Example: 3 courses</span><br>
                    Course A: <span class="hl">Score 85</span> → Grade A1 → GPoint <span class="hl">4.0</span> × Credits <span class="hl">3</span> = <span class="hl">12.0</span><br>
                    Course B: <span class="hl">Score 72</span> → Grade A2 → GPoint <span class="hl">3.7</span> × Credits <span class="hl">3</span> = <span class="hl">11.1</span><br>
                    Course C: <span class="hl">Score 55</span> → Grade B1 → GPoint <span class="hl">3.0</span> × Credits <span class="hl">3</span> = <span class="hl">9.0</span><br>
                    <br>
                    <span class="comment">-- Total</span><br>
                    Sum Credit Points = <span class="hl">12.0 + 11.1 + 9.0 = 32.1</span><br>
                    Total Credits = <span class="hl">3 + 3 + 3 = 9</span><br>
                    <br>
                    SGPA = <span class="hl">32.1 ÷ 9 = 3.57</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">The SGPA is out of 4.0 and is displayed on the student portal as a circular progress indicator.</p>
            </div>

            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-trophy text-yellow-500"></i> Class Position Rankings
                </p>
                <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
                    <div class="tip-item"><div class="tip-dot"></div><span>Rankings use <strong>Dense Ranking</strong>: students with equal averages share the same position with no gaps.</span></div>
                    <div class="tip-item"><div class="tip-dot"></div><span>Ranking is calculated per program attempt — so all students who sat the same exam are compared.</span></div>
                    <div class="tip-item"><div class="tip-dot"></div><span>Class positions are <strong>100% confidential</strong> — they are never printed on PDF or Excel exports.</span></div>
                    <div class="tip-item"><div class="tip-dot"></div><span>Students can only see their own position, not others', through their personal portal account.</span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="feature-card">
        <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
            <i class="fas fa-clipboard-list text-slate-500"></i> Overall Result Labels
        </p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
            @foreach([
                ['label'=>'EXCELLENT RESULT','range'=>'Avg ≥ 80','color'=>'#D4A017','bg'=>'rgba(212,160,23,.1)','text'=>'#78520a'],
                ['label'=>'VERY GOOD RESULT','range'=>'Avg 70–79','color'=>'#3b82f6','bg'=>'rgba(59,130,246,.1)','text'=>'#1d4ed8'],
                ['label'=>'GOOD RESULT','range'=>'Avg 60–69','color'=>'#06b6d4','bg'=>'rgba(6,182,212,.1)','text'=>'#0e7490'],
                ['label'=>'CREDIT RESULT','range'=>'Avg 50–59','color'=>'#22c55e','bg'=>'rgba(34,197,94,.1)','text'=>'#15803d'],
                ['label'=>'PASS RESULT','range'=>'Avg 40–49','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)','text'=>'#b45309'],
                ['label'=>'FAIL','range'=>'Avg < 40','color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)','text'=>'#b91c1c'],
            ] as $r)
            <div class="text-center p-3 rounded-xl" style="background:{{ $r['bg'] }};border:1px solid {{ $r['color'] }}33">
                <p class="text-xs font-bold" style="color:{{ $r['text'] }}">{{ $r['label'] }}</p>
                <p class="text-xs mt-1" style="color:{{ $r['text'] }};opacity:0.7">{{ $r['range'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ========== PANEL: FEES ========== --}}
<div class="guide-panel" x-show="activeTab === 'fees'" id="panel-fees">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Fee Management & Billing Rules</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-cogs text-amber-500"></i> Fee Configuration
            </p>
            <div class="step-flow">
                <div class="step-item">
                    <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Set Program Fees</p><p class="text-xs text-slate-500 mt-0.5">Go to Fees & Payments → Set Fee. Select a program and enter the <strong>Tuition Fee</strong> and <strong>Exam Fee</strong> separately. Both are billed when a student is in or has passed through that program.</p></div>
                </div>
                <div class="step-item">
                    <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Record Payments</p><p class="text-xs text-slate-500 mt-0.5">Search a student and enter the payment amount and date. Payments are cumulative — enter multiple installments separately.</p></div>
                </div>
                <div class="step-item">
                    <div class="relative"><div class="step-num">3</div></div>
                    <div class="pt-1"><p class="text-sm font-semibold text-slate-700">Balance Auto-Calculated</p><p class="text-xs text-slate-500 mt-0.5">The system totals all fees from every program the student has ever enrolled in (using promotion history), deducts total payments, and shows the outstanding balance.</p></div>
                </div>
            </div>
        </div>

        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-function text-blue-500"></i> Fee Calculation Formula
            </p>
            <div class="formula-box mb-3">
                <span class="comment">-- Programs a student went through:</span><br>
                Program 1 Fee = Tuition <span class="hl">500</span> + Exam <span class="hl">50</span> = <span class="hl">550</span><br>
                Program 2 Fee = Tuition <span class="hl">500</span> + Exam <span class="hl">50</span> = <span class="hl">550</span><br>
                <br>
                <span class="comment">-- Total Billed:</span><br>
                Total = <span class="hl">550 + 550 = 1,100</span><br>
                <br>
                <span class="comment">-- Payments made:</span><br>
                Paid = <span class="hl">800</span><br>
                <br>
                <span class="hl">Balance = 1,100 − 800 = 300</span>
            </div>
            <div class="guide-alert mb-0" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2)">
                <div class="guide-alert-icon" style="background:rgba(245,158,11,0.15);color:#d97706"><i class="fas fa-info-circle"></i></div>
                <div><p class="text-xs text-amber-700">When a student is promoted, the next program's fee is <strong>automatically added</strong> to their total bill — no manual action needed.</p></div>
            </div>
        </div>
    </div>

    <div class="feature-card">
        <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
            <i class="fas fa-chart-pie text-green-500"></i> Dashboard Fee Summary
        </p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-4 rounded-xl text-center" style="background:rgba(34,197,94,0.07);border:1px solid rgba(34,197,94,0.2)">
                <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">Total Billed</p>
                <p class="text-sm text-green-800 font-bold">Sum of all program + exam fees for all enrolled students across all their programs.</p>
            </div>
            <div class="p-4 rounded-xl text-center" style="background:rgba(59,130,246,0.07);border:1px solid rgba(59,130,246,0.2)">
                <p class="text-xs text-blue-600 font-semibold uppercase tracking-wide mb-1">Total Collected</p>
                <p class="text-sm text-blue-800 font-bold">Sum of all payment records across all students.</p>
            </div>
            <div class="p-4 rounded-xl text-center" style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2)">
                <p class="text-xs text-red-600 font-semibold uppercase tracking-wide mb-1">Outstanding</p>
                <p class="text-sm text-red-800 font-bold">Total Billed minus Total Collected. Represents money not yet paid.</p>
            </div>
        </div>
    </div>
</div>

{{-- ========== PANEL: REPORTS ========== --}}
<div class="guide-panel" x-show="activeTab === 'reports'" id="panel-reports">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Reports & Data Exports</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-5">
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-blue"><i class="fas fa-user-graduate"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Student Report</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Lists all students with their program, status, branch, and enrollment date. Filter by branch and program. Export as PDF or Excel for administrative records.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-green"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Attendance Report</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Summary of student attendance per course including present/absent/late counts and percentage. Filter by date range, branch, or course.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-amber"><i class="fas fa-coins"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Financial Report</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Complete fee and payment summary per student. Shows billed amount, total paid, and outstanding balance. Useful for end-of-term financial reconciliation.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-purple"><i class="fas fa-graduation-cap"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Program Report</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Enrollment statistics per program. Shows how many students are in each program level, useful for capacity planning and academic monitoring.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-cyan"><i class="fas fa-book-open"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Course Report</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Overview of courses by program including credit units and enrollment counts. Useful for curriculum audits and workload assessment.</p>
                </div>
            </div>
        </div>
        <div class="feature-card">
            <div class="flex items-start gap-3">
                <div class="icon-badge badge-rose"><i class="fas fa-file-download"></i></div>
                <div>
                    <p class="font-bold text-sm text-slate-800 mb-1">Export Formats</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Every report supports <strong>PDF export</strong> (for printing and official use) and <strong>Excel export</strong> (for further data analysis). Preview is available before downloading.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="guide-alert" style="background:rgba(59,130,246,0.07);border:1px solid rgba(59,130,246,0.2)">
        <div class="guide-alert-icon" style="background:rgba(59,130,246,0.15);color:#2563eb"><i class="fas fa-lightbulb"></i></div>
        <div>
            <p class="font-semibold text-sm text-blue-800 mb-0.5">Report Cards vs. Reports</p>
            <p class="text-blue-700 text-xs">Individual student <strong>report cards</strong> (PDF/Excel) are accessed from the Exams & Results section for each specific student-program combination. The Reports section provides aggregated institutional data.</p>
        </div>
    </div>
</div>

{{-- ========== PANEL: ROLES ========== --}}
<div class="guide-panel" x-show="activeTab === 'roles'" id="panel-roles">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Admin Roles & Access Control</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
        <div class="feature-card" style="border-top:3px solid #D4A017">
            <div class="icon-badge badge-gold mb-3"><i class="fas fa-crown"></i></div>
            <p class="font-bold text-sm text-slate-800 mb-2">Super Admin</p>
            <ul class="space-y-1 text-xs text-slate-600">
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Full access to all data across all branches</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Create and manage branches</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Create Branch Admins and other Super Admins</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> View all students, reports, and fees globally</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Manage all programs, courses, and academic settings</li>
            </ul>
        </div>
        <div class="feature-card" style="border-top:3px solid #3b82f6">
            <div class="icon-badge badge-blue mb-3"><i class="fas fa-user-shield"></i></div>
            <p class="font-bold text-sm text-slate-800 mb-2">Branch Admin</p>
            <ul class="space-y-1 text-xs text-slate-600">
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Manages students within their assigned branch only</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Can enter attendance and exam scores</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Can manage fees for their branch students</li>
                <li class="flex gap-2"><i class="fas fa-times text-red-400 mt-0.5 flex-shrink-0"></i> Cannot see other branches' data</li>
                <li class="flex gap-2"><i class="fas fa-times text-red-400 mt-0.5 flex-shrink-0"></i> Cannot create branches or other admins</li>
            </ul>
        </div>
        <div class="feature-card" style="border-top:3px solid #22c55e">
            <div class="icon-badge badge-green mb-3"><i class="fas fa-user-graduate"></i></div>
            <p class="font-bold text-sm text-slate-800 mb-2">Student (Portal)</p>
            <ul class="space-y-1 text-xs text-slate-600">
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Accesses a separate student portal (not admin)</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Can view their own results and report cards</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Can view attendance and fees</li>
                <li class="flex gap-2"><i class="fas fa-check text-green-500 mt-0.5 flex-shrink-0"></i> Can view notifications from admin</li>
                <li class="flex gap-2"><i class="fas fa-times text-red-400 mt-0.5 flex-shrink-0"></i> Read-only — cannot modify any data</li>
            </ul>
        </div>
    </div>

    <div class="feature-card">
        <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
            <i class="fas fa-key text-amber-500"></i> Managing Admin Accounts
        </p>
        <div class="space-y-2 text-xs text-slate-600 leading-relaxed">
            <div class="tip-item"><div class="tip-dot"></div><span>Super Admins can access <strong>Branches</strong> and <strong>Admins</strong> sections at the bottom of the sidebar (hidden from Branch Admins).</span></div>
            <div class="tip-item"><div class="tip-dot"></div><span>Branch Admins are created through <strong>Branches → View Branch → Add Admin</strong>.</span></div>
            <div class="tip-item"><div class="tip-dot"></div><span>Additional Super Admins can be created from the <strong>Admins</strong> section.</span></div>
            <div class="tip-item"><div class="tip-dot"></div><span>Admins log in using their email and password on the main login page.</span></div>
            <div class="tip-item"><div class="tip-dot"></div><span>Students use a completely separate login at <code class="bg-slate-100 px-1 rounded">/portal/login</code> with their Student ID and name-based credentials.</span></div>
        </div>
    </div>
</div>

{{-- ========== PANEL: EXAMS (NEW) ========== --}}
<div class="guide-panel" x-show="activeTab === 'exams_new'" id="panel-exams_new">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">New Exam Features — Complete Walkthrough</h2>
    </div>

    {{-- What's new callout --}}
    <div class="guide-alert mb-6" style="background:linear-gradient(135deg,rgba(212,160,23,0.1),rgba(184,134,11,0.06));border:1px solid rgba(212,160,23,0.3)">
        <div class="guide-alert-icon" style="background:rgba(212,160,23,0.2);color:#b8860b"><i class="fas fa-sparkles"></i></div>
        <div>
            <p class="font-bold text-sm text-yellow-800 mb-1">What's new in the exam system?</p>
            <p class="text-yellow-700 text-xs leading-relaxed">
                Three major improvements have been added: <strong>(1)</strong> Scores are now split into a separate <em>Quiz</em> score and <em>Exam</em> score per course.
                <strong>(2)</strong> You can freely set the percentage weight each one carries (e.g. Quiz 30% + Exam 70%) and change it any time.
                <strong>(3)</strong> A brand-new <strong>Bulk Download & Print</strong> page lets you generate all students' report cards in a single PDF with one click.
                The report card date now also shows the month/year the exam was <em>taken</em>, not the download date.
            </p>
        </div>
    </div>

    {{-- ── SECTION 1: Quiz + Exam Scores ── --}}
    <div class="mb-6">
        <div class="section-heading">
            <div class="section-heading-bar" style="background:linear-gradient(180deg,#3b82f6,#1d4ed8)"></div>
            <h3 class="text-sm font-bold text-slate-700">1. Quiz Score + Exam Score (Per Course)</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-pen text-blue-500"></i> Single Student Entry
                </p>
                <div class="step-flow">
                    <div class="step-item">
                        <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Go to Exams &amp; Results → Enter Scores</p>
                            <p class="text-xs text-slate-500 mt-0.5">Select the program, then search for and select the student.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">You now see 4 columns per course</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                <span class="inline-block px-1.5 py-0.5 rounded text-blue-700 font-bold" style="background:#dbeafe">Quiz Score</span>
                                &nbsp;
                                <span class="inline-block px-1.5 py-0.5 rounded text-orange-700 font-bold" style="background:#fed7aa">Exam Score</span>
                                &nbsp;
                                <span class="inline-block px-1.5 py-0.5 rounded text-gray-700 font-bold bg-gray-100">Aggregate (auto)</span>
                                <br><br>The percentage weights are shown in the column headers (e.g. Quiz 30% · Exam 70%).
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">3</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Live Aggregate Preview</p>
                            <p class="text-xs text-slate-500 mt-0.5">As you type either score, the <strong>Aggregate</strong> column updates instantly — no need to calculate manually. Formula: <code class="bg-slate-100 px-1 rounded">(Quiz × Quiz%) + (Exam × Exam%)</code></p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">4</div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Click Save Scores</p>
                            <p class="text-xs text-slate-500 mt-0.5">Both scores are saved. The system uses the weighted aggregate to assign letter grades, position, and SGPA.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-table text-green-500"></i> Bulk Grid Entry (All Students at Once)
                </p>
                <div class="step-flow">
                    <div class="step-item">
                        <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Go to Exams Sheet (Bulk Entry)</p>
                            <p class="text-xs text-slate-500 mt-0.5">Found under the Exams section or via the sidebar. Select a program and load the sheet.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Two Columns Per Course in the Grid</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Each course now has a <span class="text-blue-700 font-semibold">Quiz</span> column (blue) and an <span class="text-orange-700 font-semibold">Exam</span> column (orange). Fill them in for every student, then click <strong>Save All Grid Scores</strong>.
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">3</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Or Download the Excel Template</p>
                            <p class="text-xs text-slate-500 mt-0.5">The downloaded Excel file has two columns per course — one labelled <code>[QUIZ 30%]</code> and one labelled <code>[EXAM 70%]</code>. Fill it offline and upload it back.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">4</div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Upload the Filled Template</p>
                            <p class="text-xs text-slate-500 mt-0.5">Use the <strong>Upload Filled Excel Sheet</strong> card on the same page. The system reads both quiz and exam columns automatically.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="guide-alert" style="background:rgba(59,130,246,0.07);border:1px solid rgba(59,130,246,0.2)">
            <div class="guide-alert-icon" style="background:rgba(59,130,246,0.15);color:#2563eb"><i class="fas fa-info-circle"></i></div>
            <div>
                <p class="font-semibold text-sm text-blue-800 mb-0.5">Both scores are optional per entry</p>
                <p class="text-blue-700 text-xs">You can enter only an Exam score and leave Quiz blank (or vice versa). The aggregate is calculated using whatever is entered — a blank score counts as 0 for that component.</p>
            </div>
        </div>
    </div>

    {{-- ── SECTION 2: Adjustable Percentages ── --}}
    <div class="mb-6">
        <div class="section-heading">
            <div class="section-heading-bar" style="background:linear-gradient(180deg,#7c3aed,#6d28d9)"></div>
            <h3 class="text-sm font-bold text-slate-700">2. Adjustable Quiz &amp; Exam Percentages</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-purple-500"></i> How to Change the Percentages
                </p>
                <div class="step-flow">
                    <div class="step-item">
                        <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Open Exam Sheet (Bulk Entry)</p>
                            <p class="text-xs text-slate-500 mt-0.5">Select any program and load the sheet. The percentage settings panel appears near the top of the page.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Set Quiz % and Exam %</p>
                            <p class="text-xs text-slate-500 mt-0.5">Type a value for Quiz %. The Exam % field automatically updates to make the total 100%. For example: set Quiz to <strong>40</strong> and Exam auto-fills <strong>60</strong>.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">3</div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Click Save Percentages</p>
                            <p class="text-xs text-slate-500 mt-0.5">The new percentages apply immediately to <strong>all report cards and aggregates system-wide</strong> — including existing saved scores.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-calculator text-purple-500"></i> How the Aggregate is Calculated
                </p>
                <div class="formula-box mb-3">
                    <span class="comment">-- Example: Quiz 30%, Exam 70%</span><br>
                    Quiz Score: <span class="hl">65</span> out of 100<br>
                    Exam Score: <span class="hl">80</span> out of 100<br><br>
                    Aggregate = <span class="hl">(65 × 30%) + (80 × 70%)</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = <span class="hl">19.5 + 56.0</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = <span class="hl">75.5</span> → Grade <span class="hl">A2</span>
                </div>
                <div class="formula-box">
                    <span class="comment">-- Example: Quiz 40%, Exam 60%</span><br>
                    Quiz Score: <span class="hl">65</span>, Exam Score: <span class="hl">80</span><br><br>
                    Aggregate = <span class="hl">(65 × 40%) + (80 × 60%)</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = <span class="hl">26.0 + 48.0</span><br>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; = <span class="hl">74.0</span> → Grade <span class="hl">A2</span>
                </div>
            </div>
        </div>

        <div class="guide-alert" style="background:rgba(168,85,247,0.07);border:1px solid rgba(168,85,247,0.2)">
            <div class="guide-alert-icon" style="background:rgba(168,85,247,0.15);color:#7c3aed"><i class="fas fa-exclamation-circle"></i></div>
            <div>
                <p class="font-semibold text-sm text-purple-800 mb-0.5">Important — Percentages are global, not per-program</p>
                <p class="text-purple-700 text-xs">Changing the percentage affects every program and every student's aggregate. The raw quiz and exam scores are always preserved — only the display and grading formula changes. You can safely adjust and re-adjust without losing any data.</p>
            </div>
        </div>
    </div>

    {{-- ── SECTION 3: Bulk Download & Print ── --}}
    <div class="mb-6">
        <div class="section-heading">
            <div class="section-heading-bar" style="background:linear-gradient(180deg,#dc2626,#b91c1c)"></div>
            <h3 class="text-sm font-bold text-slate-700">3. Bulk Report Card Download &amp; Print</h3>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="feature-card">
                <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                    <i class="fas fa-file-pdf text-red-500"></i> How to Bulk Download
                </p>
                <div class="step-flow">
                    <div class="step-item">
                        <div class="relative"><div class="step-num">1</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Click "Bulk Download" on the Exams page</p>
                            <p class="text-xs text-slate-500 mt-0.5">The red <strong>Bulk Download</strong> button is at the top-right of the Exams &amp; Results index page, next to "Enter Scores".</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">2</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Step 1 — Pick Program &amp; Attempt</p>
                            <p class="text-xs text-slate-500 mt-0.5">Select the program from the dropdown. The attempt dropdown shows only attempts that already have scores. Pick the one you want (e.g. Attempt 1) and click <strong>Load Students</strong>.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">3</div><div class="step-connector"></div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Step 2 — Select Students</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Only students who have scores for that attempt appear in the list. You can:
                                <br>• Click <strong>Select All</strong> to tick everyone
                                <br>• Use the <strong>filter box</strong> to search by name or student ID
                                <br>• Tick/untick individual students
                                <br>The counter at the top right shows how many are selected.
                            </p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="relative"><div class="step-num">4</div></div>
                        <div class="pt-1">
                            <p class="text-sm font-semibold text-slate-700">Step 3 — Download or Print</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Click <strong>Download PDF</strong> to save a multi-page PDF (one page = one report card) to your device.
                                <br>Click <strong>Print Report Cards</strong> to open the PDF in a new browser tab, then use <kbd class="bg-gray-100 px-1 rounded">Ctrl+P</kbd> (or <kbd class="bg-gray-100 px-1 rounded">⌘P</kbd> on Mac) to print all cards at once.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="feature-card">
                    <p class="font-bold text-sm text-slate-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-500"></i> What the Bulk PDF Contains
                    </p>
                    <div class="space-y-1.5 text-xs text-slate-600">
                        <div class="tip-item"><div class="tip-dot"></div><span>Each student gets their own <strong>full-page report card</strong>, identical to the individual PDF download.</span></div>
                        <div class="tip-item"><div class="tip-dot"></div><span>Cards are separated by page breaks — print them and simply cut or distribute individually.</span></div>
                        <div class="tip-item"><div class="tip-dot"></div><span>The student's <strong>photo</strong> appears if one was uploaded, otherwise their initials show in a placeholder box.</span></div>
                        <div class="tip-item"><div class="tip-dot"></div><span>The date on each card shows the month/year <strong>the exam was taken</strong>, not today's date.</span></div>
                        <div class="tip-item"><div class="tip-dot"></div><span>Class positions are <strong>not printed</strong> on the report cards for student privacy.</span></div>
                        <div class="tip-item"><div class="tip-dot"></div><span>If a student is selected but has no scores for that attempt, they are <strong>silently skipped</strong> — no error.</span></div>
                    </div>
                </div>

                <div class="feature-card">
                    <p class="font-bold text-sm text-slate-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-print text-gray-600"></i> Download vs. Print — What's the Difference?
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-3 rounded-xl text-center" style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2)">
                            <i class="fas fa-file-pdf text-red-600 text-xl mb-1 block"></i>
                            <p class="text-xs font-bold text-red-700">Download PDF</p>
                            <p class="text-xs text-red-600 mt-1">Saves the file to your device. Good for keeping a digital record or sharing via email/WhatsApp.</p>
                        </div>
                        <div class="p-3 rounded-xl text-center" style="background:rgba(55,65,81,0.07);border:1px solid rgba(55,65,81,0.2)">
                            <i class="fas fa-print text-gray-700 text-xl mb-1 block"></i>
                            <p class="text-xs font-bold text-gray-700">Print</p>
                            <p class="text-xs text-gray-600 mt-1">Opens the PDF inline in a new tab. Press Ctrl+P to send directly to your printer. Good for physical copies.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 4: Report Card Date ── --}}
    <div class="mb-4">
        <div class="section-heading">
            <div class="section-heading-bar" style="background:linear-gradient(180deg,#0891b2,#0e7490)"></div>
            <h3 class="text-sm font-bold text-slate-700">4. Correct Date on Report Cards</h3>
        </div>

        <div class="feature-card">
            <p class="font-bold text-sm text-slate-800 mb-3 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-cyan-500"></i> The Date Now Reflects When the Exam Was Taken
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl" style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2)">
                    <p class="text-xs font-bold text-red-700 mb-1"><i class="fas fa-times-circle mr-1"></i> Old Behaviour (Removed)</p>
                    <p class="text-xs text-red-600">Every time a report card was downloaded or printed, it showed <strong>the current month and year</strong> — meaning a report card downloaded in July would show "JULY 2026" even if the exam was taken in February.</p>
                </div>
                <div class="p-4 rounded-xl" style="background:rgba(34,197,94,0.07);border:1px solid rgba(34,197,94,0.2)">
                    <p class="text-xs font-bold text-green-700 mb-1"><i class="fas fa-check-circle mr-1"></i> New Behaviour (Current)</p>
                    <p class="text-xs text-green-600">The date on the report card shows the <strong>month and year the exam scores were originally entered</strong>. Downloading the same report card three months later still shows the original exam date.</p>
                </div>
            </div>
            <div class="guide-alert mt-3 mb-0" style="background:rgba(6,182,212,0.07);border:1px solid rgba(6,182,212,0.2)">
                <div class="guide-alert-icon" style="background:rgba(6,182,212,0.15);color:#0891b2"><i class="fas fa-lightbulb"></i></div>
                <div>
                    <p class="text-cyan-700 text-xs">The date is derived from when the first score for that student/program/attempt was saved to the database. It applies to both individual downloads and bulk PDF downloads.</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========== PANEL: FAQ ========== --}}
<div class="guide-panel" x-show="activeTab === 'faq'" id="panel-faq">
    <div class="section-heading">
        <div class="section-heading-bar"></div>
        <h2 class="text-base font-bold text-slate-800">Frequently Asked Questions</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="space-y-2">
            <div class="faq-item">
                <details>
                    <summary>How do I reset a student's portal password?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Students log in using their <strong>Student ID</strong> and their <strong>full name</strong> (fuzzy-matched). There is no password to reset. If a student can't log in, verify their Student ID and name match exactly what is stored in the system. You can edit the student's name from the Students section.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>Why is a student's score not showing on the portal?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">The student portal only shows results for programs the student is currently assigned to or has been through. Ensure the exam score was entered for the correct program and attempt number. Also verify the Student Portal toggle is set to <strong>Open</strong>.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>Can I edit exam scores after saving?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Exam scores are stored per student per course per attempt. To correct an error, re-enter the scores through the Exams & Results → Add Scores form for the same program and attempt. The system will update the existing record.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>Why does the fee balance seem higher than expected?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">The system bills students for <em>every program they have ever been in</em>, not just the current one. A student who has been promoted twice will be billed the fees for all three programs. Check the student's promotion history to see all programs they've been billed for.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>How do I promote multiple students at once?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Currently, promotion is done individually from each student's profile. Go to Students → click the student → use the Promote button. Each promotion creates a traceable history record. Bulk promotion is not available to ensure accuracy.</div>
                </details>
            </div>
        </div>
        <div class="space-y-2">
            <div class="faq-item">
                <details>
                    <summary>How does class position ranking work exactly?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Class positions are calculated using <strong>Dense Ranking</strong> based on overall average exam score. All students who sat the same program attempt are ranked together. Students with identical averages share the same position. Position 1 = highest average in the class. This is shown only in the student's personal portal — never in any export.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>Can Branch Admins see other branches' students?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">No. Branch Admin accounts are strictly scoped to their assigned branch. All queries for students, attendance, fees, and exam data are automatically filtered by <code>church_branch_id</code>. Only Super Admins can view all branches together.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>What happens if I delete a program that has students?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Deleting a program that has active students is not recommended. The system may restrict deletion if students are currently assigned to it. Always re-assign or graduate students before deleting a program to prevent data integrity issues.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>How do notifications reach students?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Notifications created in the Notifications section are broadcast to <strong>all students</strong> and appear in their portal dashboard. Students see a notification count badge. They can mark all as read from their portal. Notifications are not sent by email — only within the portal system.</div>
                </details>
            </div>
            <div class="faq-item">
                <details>
                    <summary>How do I close the student portal for results season?<i class="fas fa-chevron-down text-xs"></i></summary>
                    <div class="faq-body">Go to <strong>Student Portal</strong> in the sidebar → toggle the portal to <strong>Closed</strong> and write a message students will see. The portal access will be disabled instantly for all students. Re-open it the same way when ready.</div>
                </details>
            </div>
        </div>
    </div>
</div>

</div>
@endsection


