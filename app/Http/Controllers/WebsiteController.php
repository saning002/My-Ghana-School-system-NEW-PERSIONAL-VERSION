<?php

namespace App\Http\Controllers;

use App\Models\Website\SiteSetting;
use App\Models\Website\WebsiteBlogPost;
use App\Models\Website\WebsiteAdmissionApplication;
use App\Models\Website\WebsiteContactMessage;
use App\Models\Website\WebsiteEvent;
use App\Models\Website\WebsiteGalleryCategory;
use App\Models\Website\WebsiteGalleryImage;
use App\Models\Website\WebsiteProgram;
use App\Models\Website\WebsiteStaffMember;
use App\Models\Website\WebsiteTestimonial;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    // ── Shared site data ───────────────────────────────────────────────────
    private function site(): SiteSetting
    {
        try {
            return SiteSetting::instance();
        } catch (\Illuminate\Database\QueryException $e) {
            // Neon pooler "cached plan must not change result type" error
            // Fix: run DISCARD ALL to clear the plan cache, then retry
            if (str_contains($e->getMessage(), 'cached plan must not change result type')) {
                try {
                    \Illuminate\Support\Facades\DB::statement('DISCARD ALL');
                } catch (\Throwable $ignored) {}
                return SiteSetting::instance();
            }
            throw $e;
        }
    }

    // ── HOME ───────────────────────────────────────────────────────────────
    public function home()
    {
        $site         = $this->site();
        $programs     = collect();
        $testimonials = collect();
        $posts        = collect();
        $staff        = collect();
        $events       = collect();

        try { $programs     = WebsiteProgram::active()->take(4)->get(); }     catch (\Throwable $e) {}
        try { $testimonials = WebsiteTestimonial::active()->inRandomOrder()->take(6)->get(); } catch (\Throwable $e) {}
        try { $posts        = WebsiteBlogPost::published()->take(3)->get(); } catch (\Throwable $e) {}
        try { $staff        = WebsiteStaffMember::take(6)->get(); }           catch (\Throwable $e) {}
        try { $events       = WebsiteEvent::published()->upcoming()->take(6)->get(); } catch (\Throwable $e) {}

        return view('website.home', compact('site','programs','testimonials','posts','staff','events'));
    }

    // ── ABOUT ──────────────────────────────────────────────────────────────
    public function about()
    {
        $site  = $this->site();
        $staff = collect();
        try { $staff = WebsiteStaffMember::all(); } catch (\Throwable $e) {}
        return view('website.about', compact('site','staff'));
    }

    // ── ACADEMICS ─────────────────────────────────────────────────────────
    public function academics()
    {
        $site     = $this->site();
        $programs = collect();

        try {
            $programs = WebsiteProgram::active()->get();
        } catch (\Throwable $e) {}

        // Fallback: pull from school programs table if website_programs is empty or errored
        if ($programs->isEmpty()) {
            try {
                $programs = \App\Models\Program::orderBy('sequence')->get()
                    ->map(fn($p) => (object)[
                        'id' => $p->id, 'name' => $p->name,
                        'level' => null, 'age_range' => null,
                        'description' => $p->name . ' program.',
                        'curriculum' => null, 'schedule' => null,
                        'icon' => '🎓', 'color' => 'indigo',
                        'image_path' => null, 'features' => null, 'highlights' => null,
                    ]);
            } catch (\Throwable $e) {}
        }

        return view('website.academics', compact('site', 'programs'));
    }

    // ── ADMISSIONS ────────────────────────────────────────────────────────
    public function admissions()
    {
        $site = $this->site();
        return view('website.admissions', compact('site'));
    }

    // ── APPLY ─────────────────────────────────────────────────────────────
    public function applyForm()
    {
        $site     = $this->site();
        $programs = collect();
        try { $programs = WebsiteProgram::active()->get(); } catch (\Throwable $e) {}
        return view('website.apply', compact('site','programs'));
    }

    public function applySubmit(Request $request)
    {
        $data = $request->validate([
            'child_first_name' => 'required|string|max:100',
            'child_last_name'  => 'required|string|max:100',
            'child_dob'        => 'required|date|before:today',
            'child_gender'     => 'required|in:M,F',
            'program_applying' => 'required|in:daycare,nursery,preschool,kindergarten',
            'parent_name'      => 'required|string|max:120',
            'parent_email'     => 'required|email|max:200',
            'parent_phone'     => 'required|string|max:30',
            'relationship'     => 'required|string|max:60',
            'address'          => 'required|string|max:500',
            'previous_school'  => 'nullable|string|max:200',
            'special_needs'    => 'nullable|string|max:1000',
            'how_did_you_hear' => 'nullable|string|max:200',
        ]);

        WebsiteAdmissionApplication::create($data);

        return redirect(wu('apply'))
            ->with('success', 'Your application has been submitted successfully! We will contact you within 2–3 business days.');
    }

    // ── GALLERY ───────────────────────────────────────────────────────────
    public function gallery(Request $request)
    {
        $site       = $this->site();
        $categories = collect();
        $images     = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 18);
        try {
            $categories = WebsiteGalleryCategory::withCount('images')->get();
            $query      = WebsiteGalleryImage::with('category');
            if ($request->filled('category')) {
                $cat = WebsiteGalleryCategory::where('slug', $request->category)->first();
                if ($cat) $query->where('category_id', $cat->id);
            }
            $images = $query->paginate(18);
        } catch (\Throwable $e) {}
        return view('website.gallery', compact('site','categories','images'));
    }

    // ── EVENTS ────────────────────────────────────────────────────────────
    public function events()
    {
        $site     = $this->site();
        $upcoming = collect();
        $past     = collect();
        try { $upcoming = WebsiteEvent::published()->upcoming()->get(); } catch (\Throwable $e) {}
        try { $past     = WebsiteEvent::published()->past()->take(6)->get(); } catch (\Throwable $e) {}
        return view('website.events', compact('site','upcoming','past'));
    }

    // ── BLOG ──────────────────────────────────────────────────────────────
    public function blog()
    {
        $site  = $this->site();
        $posts = new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 9);
        try { $posts = WebsiteBlogPost::published()->paginate(9); } catch (\Throwable $e) {}
        return view('website.blog', compact('site','posts'));
    }

    public function blogDetail(string $slug)
    {
        $site        = $this->site();
        $post        = null;
        $recentPosts = collect();
        try {
            $post        = WebsiteBlogPost::published()->where('slug', $slug)->firstOrFail();
            $recentPosts = WebsiteBlogPost::published()->where('id','!=',$post->id)->take(4)->get();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404);
        } catch (\Throwable $e) {
            abort(404);
        }
        return view('website.blog-detail', compact('site','post','recentPosts'));
    }

    // ── STAFF ─────────────────────────────────────────────────────────────
    public function staff()
    {
        $site  = $this->site();
        $staff = collect();
        try { $staff = WebsiteStaffMember::all(); } catch (\Throwable $e) {}
        return view('website.staff', compact('site','staff'));
    }

    // ── CONTACT ───────────────────────────────────────────────────────────
    public function contact()
    {
        $site = $this->site();
        return view('website.contact', compact('site'));
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:120',
            'email'   => 'required|email|max:200',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:3000',
        ]);

        WebsiteContactMessage::create($data);

        return redirect(wu('contact'))
            ->with('success', 'Thank you! Your message has been received. We\'ll get back to you shortly.');
    }

    // ── PORTAL ────────────────────────────────────────────────────────────
    public function portal()
    {
        $site = $this->site();
        return view('website.portal', compact('site'));
    }
}
