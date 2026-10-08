<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class GuideController extends Controller
{
    /**
     * Display the administrator guide and system explainer page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.guide');
    }
}
