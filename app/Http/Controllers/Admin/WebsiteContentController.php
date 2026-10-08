<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website\SiteSetting;
use App\Models\Website\WebsiteAdmissionApplication;
use App\Models\Website\WebsiteBlogPost;
use App\Models\Website\WebsiteContactMessage;
use App\Models\Website\WebsiteEvent;
use App\Models\Website\WebsiteGalleryCategory;
use App\Models\Website\WebsiteGalleryImage;
use App\Models\Website\WebsiteProgram;
use App\Models\Website\WebsiteStaffMember;
use App\Models\Website\WebsiteTestimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebsiteContentController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────
    public function dashboard()
    {
        return view('admin.website.dashboard', [
            'postsCount'     => WebsiteBlogPost::count(),
            'publishedCount' => WebsiteBlogPost::published()->count(),
            'eventsCount'    => WebsiteEvent::published()->upcoming()->count(),
            'galleryCount'   => WebsiteGalleryImage::count(),
            'staffCount'     => WebsiteStaffMember::count(),
            'unreadMessages' => WebsiteContactMessage::unread()->count(),
            'pendingApps'    => WebsiteAdmissionApplication::where('status','pending')->count(),
            'recentMessages' => WebsiteContactMessage::take(5)->get(),
            'recentApps'     => WebsiteAdmissionApplication::take(5)->get(),
        ]);
    }

    // ─── Site Settings ────────────────────────────────────────────────────
    public function siteSettings()
    {
        return view('admin.website.site-settings', ['setting' => SiteSetting::instance()]);
    }

    public function siteSettingsSave(Request $request)
    {
        $data = $request->validate([
            'site_name'            => 'required|string|max:120',
            'tagline'              => 'nullable|string|max:300',
            'hero_title'           => 'nullable|string|max:200',
            'hero_subtitle'        => 'nullable|string|max:400',
            'about_text'           => 'nullable|string',
            'mission'              => 'nullable|string',
            'vision'               => 'nullable|string',
            'history'              => 'nullable|string',
            'admissions_info'      => 'nullable|string',
            'contact_email'        => 'nullable|email|max:200',
            'contact_phone'        => 'nullable|string|max:30',
            'address'              => 'nullable|string|max:255',
            'map_embed_url'        => 'nullable|string|max:1000',
            'facebook'             => 'nullable|url|max:300',
            'twitter'              => 'nullable|url|max:300',
            'instagram'            => 'nullable|url|max:300',
            'youtube'              => 'nullable|url|max:300',
            'logo'                 => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            // Hero bg options
            'hero_bg_type'         => 'nullable|in:default,image,video,solid,gradient,glassmorphism',
            'hero_bg_video_url'    => 'nullable|url|max:500',
            'hero_bg_solid_color'  => 'nullable|string|max:20',
            'hero_gradient_from'   => 'nullable|string|max:20',
            'hero_gradient_to'     => 'nullable|string|max:20',
            'hero_gradient_via'    => 'nullable|string|max:20',
            'hero_gradient_angle'  => 'nullable|integer|between:0,360',
            'hero_overlay_opacity' => 'nullable|integer|between:0,100',
            'hero_layout'          => 'nullable|in:split,centered,fullscreen,minimal',
            'hero_bg_image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            // Navbar
            'nav_brand_sub'        => 'nullable|string|max:100',
            'nav_enroll_text'      => 'nullable|string|max:60',
            'nav_portal_text'      => 'nullable|string|max:60',
            // Hero panel
            'hero_panel_title'     => 'nullable|string|max:150',
            'hero_panel_subtitle'  => 'nullable|string|max:200',
            'hero_eyebrow'         => 'nullable|string|max:100',
            'hero_cta_primary_text'   => 'nullable|string|max:60',
            'hero_cta_secondary_text' => 'nullable|string|max:60',
            // Why section
            'why_eyebrow'    => 'nullable|string|max:80',
            'why_title'      => 'nullable|string|max:150',
            'why_subtitle'   => 'nullable|string|max:300',
            // Section labels (programs, testimonials, blog, events)
            'programs_eyebrow'      => 'nullable|string|max:80',
            'programs_title'        => 'nullable|string|max:150',
            'programs_subtitle'     => 'nullable|string|max:300',
            'testimonials_eyebrow'  => 'nullable|string|max:80',
            'testimonials_title'    => 'nullable|string|max:150',
            'testimonials_subtitle' => 'nullable|string|max:300',
            'blog_eyebrow'    => 'nullable|string|max:80',
            'blog_title'      => 'nullable|string|max:150',
            'blog_subtitle'   => 'nullable|string|max:300',
            'events_eyebrow'  => 'nullable|string|max:80',
            'events_title'    => 'nullable|string|max:150',
            'events_subtitle' => 'nullable|string|max:300',
            // CTA banner
            'cta_title'          => 'nullable|string|max:150',
            'cta_subtitle'       => 'nullable|string|max:300',
            'cta_primary_text'   => 'nullable|string|max:60',
            'cta_secondary_text' => 'nullable|string|max:60',
            // About page
            'about_eyebrow'      => 'nullable|string|max:80',
            'about_mv_title'     => 'nullable|string|max:150',
            'about_story_title'  => 'nullable|string|max:150',
            'about_story_text'   => 'nullable|string',
            'about_cta_title'    => 'nullable|string|max:150',
            'about_cta_subtitle' => 'nullable|string|max:200',
            // Admissions
            'admissions_eyebrow'       => 'nullable|string|max:80',
            'admissions_title'         => 'nullable|string|max:150',
            'admissions_subtitle'      => 'nullable|string|max:300',
            'admissions_steps_eyebrow' => 'nullable|string|max:80',
            'admissions_steps'         => 'nullable|array',
            'admissions_steps.*.num'   => 'nullable|string|max:10',
            'admissions_steps.*.title' => 'nullable|string|max:100',
            'admissions_steps.*.desc'  => 'nullable|string|max:300',
            // Contact
            'office_hours' => 'nullable|string|max:100',
            // Footer
            'footer_site_name'    => 'nullable|string|max:150',
            'footer_tagline'      => 'nullable|string|max:300',
            'footer_nav_title'    => 'nullable|string|max:80',
            'footer_programs_title'=> 'nullable|string|max:80',
            'footer_apply_text'   => 'nullable|string|max:60',
            'footer_contact_title'=> 'nullable|string|max:80',
            'footer_address'      => 'nullable|string|max:300',
            'footer_phone'        => 'nullable|string|max:80',
            'footer_email'        => 'nullable|string|max:150',
            'footer_office_hours' => 'nullable|string|max:150',
            'footer_portal_text'  => 'nullable|string|max:60',
            'footer_copyright'    => 'nullable|string|max:200',
            'footer_privacy_text' => 'nullable|string|max:80',
            'footer_privacy_url'  => 'nullable|string|max:300',
            'footer_terms_text'   => 'nullable|string|max:80',
            'footer_terms_url'    => 'nullable|string|max:300',
            'footer_support_text' => 'nullable|string|max:80',
            'footer_support_url'  => 'nullable|string|max:300',
            // Staff section
            'staff_eyebrow'  => 'nullable|string|max:80',
            'staff_title'    => 'nullable|string|max:150',
            'staff_subtitle' => 'nullable|string|max:300',
            // Academics page
            'academics_hero_title'       => 'nullable|string|max:200',
            'academics_hero_subtitle'    => 'nullable|string|max:300',
            'academics_eyebrow'          => 'nullable|string|max:80',
            'academics_section_title'    => 'nullable|string|max:150',
            'academics_section_subtitle' => 'nullable|string|max:300',
            'academics_empty_text'       => 'nullable|string|max:300',
            'academics_apply_btn'        => 'nullable|string|max:60',
            'academics_contact_btn'      => 'nullable|string|max:60',
            'academics_curriculum_label' => 'nullable|string|max:100',
            'academics_highlights_label' => 'nullable|string|max:100',
            'academics_schedule_label'   => 'nullable|string|max:100',
            'academics_cta_text'         => 'nullable|string|max:300',
            'academics_cta_btn1_text'    => 'nullable|string|max:60',
            'academics_cta_btn2_text'    => 'nullable|string|max:60',
            'academics_cta_btn3_text'    => 'nullable|string|max:60',
            // Report card orientation
            'report_card_orientation'    => 'nullable|in:portrait,landscape',
            // Website theme colours (wt_ prefix = website theme)
            'wt_primary'   => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'wt_secondary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'wt_accent'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'wt_text'      => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'wt_bg'        => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'wt_navBg'     => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            // Admin theme colours
            'theme_primary'      => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_secondary'    => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_accent'       => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_text'         => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_bg'           => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_navBg'        => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_adminPrimary' => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme_adminNav'     => 'nullable|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $setting = SiteSetting::instance();

        // Logo upload
        if ($request->hasFile('logo')) {
            if ($setting->logo_path) (str_contains($setting->logo_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($setting->logo_path) : Storage::disk('public')->delete($setting->logo_path));
            $data['logo_path'] = \App\Http\Controllers\Admin\StudentController::storePhotoFile($request->file('logo'), 'website/logos');
        }

        // Hero bg image upload
        if ($request->hasFile('hero_bg_image')) {
            if ($setting->hero_bg_image) (str_contains($setting->hero_bg_image, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($setting->hero_bg_image) : Storage::disk('public')->delete($setting->hero_bg_image));
            $data['hero_bg_image'] = \App\Http\Controllers\Admin\StudentController::storePhotoFile($request->file('hero_bg_image'), 'website/hero');
        }

        // JSON fields from textarea (one item per line)
        foreach ([
            'admissions_requirements' => fn($v) => array_values(array_filter(array_map('trim', explode("\n", $v)))),
            'contact_subjects'        => fn($v) => array_values(array_filter(array_map('trim', explode("\n", $v)))),
        ] as $field => $parser) {
            if ($request->filled($field)) {
                $data[$field] = $parser($request->$field);
            }
        }

        // JSON fields from structured repeater inputs
        foreach (['why_features','hero_minis','about_timeline','core_values','admissions_steps','footer_programs'] as $jField) {
            if ($request->has($jField)) {
                $data[$jField] = $request->input($jField);
            }
        }

        // Stats JSON
        if ($request->has('stats')) {
            $data['stats'] = $request->input('stats');
        }

        // Theme colours — admin panel (stored in theme_colors JSON)
        $themeFields = ['theme_primary','theme_secondary','theme_accent','theme_text','theme_bg','theme_navBg','theme_adminPrimary','theme_adminNav'];
        $themeData = [];
        foreach ($themeFields as $field) {
            if ($request->filled($field)) {
                $val = $request->input($field);
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) {
                    $themeData[str_replace('theme_', '', $field)] = $val;
                }
            }
        }
        if (!empty($themeData)) {
            $existing = $setting->theme_colors ?? [];
            $data['theme_colors'] = array_merge(
                \App\Models\Website\SiteSetting::defaultTheme(),
                is_array($existing) ? $existing : [],
                $themeData
            );
        }

        // Website-only theme colours (stored in website_theme JSON)
        $websiteThemeFields = ['wt_primary','wt_secondary','wt_accent','wt_text','wt_bg','wt_navBg'];
        $websiteThemeData = [];
        foreach ($websiteThemeFields as $field) {
            if ($request->filled($field)) {
                $val = $request->input($field);
                if (preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) {
                    $websiteThemeData[str_replace('wt_', '', $field)] = $val;
                }
            }
        }
        if (!empty($websiteThemeData)) {
            $existing = $setting->website_theme ?? [];
            $data['website_theme'] = array_merge(
                \App\Models\Website\SiteSetting::defaultWebsiteTheme(),
                is_array($existing) ? $existing : [],
                $websiteThemeData
            );
        }

        // IMPORTANT: Remove uploaded-file keys — they are UploadedFile objects,
        // not strings. Keeping them in $data causes a DB column type error.
        unset($data['logo'], $data['hero_bg_image']);

        // Only update columns that actually exist in the DB to prevent 500
        // errors when new migrations haven't run on production yet.
        try {
            $existingColumns = \Illuminate\Support\Facades\Schema::getColumnListing('site_settings');
            $safeData = array_filter($data, fn($v, $k) => in_array($k, $existingColumns), ARRAY_FILTER_USE_BOTH);

            // website_theme and theme_colors are JSON columns — save them directly
            // even if the column check misses them (e.g. fresh migration not yet run).
            // We use updateOrInsert-safe approach: update existing row directly.
            if (isset($data['website_theme'])) {
                $safeData['website_theme'] = $data['website_theme'];
            }
            if (isset($data['theme_colors'])) {
                $safeData['theme_colors'] = $data['theme_colors'];
            }

            $setting->update($safeData);
        } catch (\Throwable $e) {
            \Log::error('SiteSettings save failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Settings could not be saved: ' . $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Website settings saved successfully.');
    }

    // ─── Blog ─────────────────────────────────────────────────────────────
    public function blogIndex()
    {
        return view('admin.website.blog.index', ['posts' => WebsiteBlogPost::paginate(20)]);
    }
    public function blogCreate()
    {
        return view('admin.website.blog.form', ['post' => null]);
    }
    public function blogStore(Request $request)
    {
        $data = $this->handlePostSave($request);
        WebsiteBlogPost::create($data);
        return redirect()->route('admin.website.blog.index')->with('success', 'Post created.');
    }
    public function blogEdit(WebsiteBlogPost $post)
    {
        return view('admin.website.blog.form', compact('post'));
    }
    public function blogUpdate(Request $request, WebsiteBlogPost $post)
    {
        $data = $this->handlePostSave($request, $post);
        $post->update($data);
        return redirect()->route('admin.website.blog.index')->with('success', 'Post updated.');
    }
    public function blogDestroy(WebsiteBlogPost $post)
    {
        if ($post->featured_image_path) (str_contains($post->featured_image_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($post->featured_image_path) : Storage::disk('public')->delete($post->featured_image_path));
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
    private function handlePostSave(Request $r, ?WebsiteBlogPost $post = null): array
    {
        $data = $r->validate([
            'title'          => 'required|string|max:200',
            'slug'           => 'nullable|string|max:200',
            'excerpt'        => 'nullable|string|max:400',
            'content'        => 'required|string',
            'published'      => 'nullable|boolean',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);
        if ($r->hasFile('featured_image')) {
            if ($post?->featured_image_path) (str_contains($post->featured_image_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($post->featured_image_path) : Storage::disk('public')->delete($post->featured_image_path));
            $data['featured_image_path'] = \App\Http\Controllers\Admin\StudentController::storePhotoFile($r->file('featured_image'), 'website/blog');
        }
        if (empty($data['slug'])) $data['slug'] = Str::slug($data['title']);
        $data['published'] = $r->boolean('published');
        unset($data['featured_image']);
        return $data;
    }

    // ─── Events ───────────────────────────────────────────────────────────
    public function eventsIndex()
    {
        return view('admin.website.events.index', ['events' => WebsiteEvent::paginate(20)]);
    }
    public function eventsCreate()
    {
        return view('admin.website.events.form', ['event' => null]);
    }
    public function eventsStore(Request $request)
    {
        $data = $this->handleEventSave($request);
        WebsiteEvent::create($data);
        return redirect()->route('admin.website.events.index')->with('success', 'Event created.');
    }
    public function eventsEdit(WebsiteEvent $event)
    {
        return view('admin.website.events.form', compact('event'));
    }
    public function eventsUpdate(Request $request, WebsiteEvent $event)
    {
        $data = $this->handleEventSave($request, $event);
        $event->update($data);
        return redirect()->route('admin.website.events.index')->with('success', 'Event updated.');
    }
    public function eventsDestroy(WebsiteEvent $event)
    {
        if ($event->image_path) (str_contains($event->image_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($event->image_path) : Storage::disk('public')->delete($event->image_path));
        $event->delete();
        return back()->with('success', 'Event deleted.');
    }
    private function handleEventSave(Request $r, ?WebsiteEvent $event = null): array
    {
        $data = $r->validate([
            'title'        => 'required|string|max:200',
            'description'  => 'required|string',
            'date'         => 'required|date',
            'time'         => 'nullable',
            'location'     => 'nullable|string|max:200',
            'is_published' => 'nullable|boolean',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);
        if ($r->hasFile('image')) {
            if ($event?->image_path) (str_contains($event->image_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($event->image_path) : Storage::disk('public')->delete($event->image_path));
            $data['image_path'] = \App\Http\Controllers\Admin\StudentController::storePhotoFile($r->file('image'), 'website/events');
        }
        $data['is_published'] = $r->boolean('is_published');
        unset($data['image']);
        return $data;
    }

    // ─── Gallery ──────────────────────────────────────────────────────────
    public function galleryIndex()
    {
        return view('admin.website.gallery.index', [
            'images'     => WebsiteGalleryImage::with('category')->paginate(24),
            'categories' => WebsiteGalleryCategory::withCount('images')->get(),
        ]);
    }
    public function galleryUpload(Request $request)
    {
        $request->validate([
            'images'      => 'required|array|min:1',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'category_id' => 'nullable|exists:website_gallery_categories,id',
            'title'       => 'nullable|string|max:120',
        ]);

        $uploaded = 0;
        $errors   = [];

        $userTitle = trim((string) $request->input('title', ''));

        foreach ($request->file('images') as $file) {
            try {
                $path = \App\Http\Controllers\Admin\StudentController::storePhotoFile($file, 'website/gallery');
                if (!$path) {
                    $errors[] = 'Failed to save file: '.$file->getClientOriginalName();
                    continue;
                }

                $imageTitle = !empty($userTitle)
                    ? $userTitle
                    : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

                WebsiteGalleryImage::create([
                    'title'       => substr($imageTitle, 0, 120),
                    'image_path'  => $path,
                    'category_id' => $request->category_id,
                ]);
                $uploaded++;
            } catch (\Throwable $e) {
                \Log::error('Gallery upload failed: '.$e->getMessage());
                $errors[] = $file->getClientOriginalName().': '.$e->getMessage();
            }
        }

        if ($uploaded > 0 && empty($errors)) {
            return back()->with('success', $uploaded.' image(s) uploaded successfully.');
        } elseif ($uploaded > 0) {
            return back()->with('success', $uploaded.' image(s) uploaded. Some failed: '.implode(', ', $errors));
        }

        return back()->with('error', 'Upload failed: '.implode(', ', $errors));
    }
    public function galleryDestroy(WebsiteGalleryImage $image)
    {
        (str_contains($image->image_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($image->image_path) : Storage::disk('public')->delete($image->image_path));
        $image->delete();
        return back()->with('success', 'Image deleted.');
    }
    public function galleryCategoryStore(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        WebsiteGalleryCategory::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);
        return back()->with('success', 'Category added.');
    }

    // ─── Staff ────────────────────────────────────────────────────────────
    public function staffIndex()
    {
        return view('admin.website.staff.index', ['staff' => WebsiteStaffMember::all()]);
    }
    public function staffCreate()
    {
        return view('admin.website.staff.form', ['member' => null]);
    }
    public function staffStore(Request $request)
    {
        $data = $this->handleStaffSave($request);
        WebsiteStaffMember::create($data);
        return redirect()->route('admin.website.staff.index')->with('success', 'Staff member added.');
    }
    public function staffEdit(WebsiteStaffMember $member)
    {
        return view('admin.website.staff.form', compact('member'));
    }
    public function staffUpdate(Request $request, WebsiteStaffMember $member)
    {
        $data = $this->handleStaffSave($request, $member);
        $member->update($data);
        return redirect()->route('admin.website.staff.index')->with('success', 'Updated.');
    }
    public function staffDestroy(WebsiteStaffMember $member)
    {
        if ($member->photo_path) (str_contains($member->photo_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($member->photo_path) : Storage::disk('public')->delete($member->photo_path));
        $member->delete();
        return back()->with('success', 'Removed.');
    }
    private function handleStaffSave(Request $r, ?WebsiteStaffMember $member = null): array
    {
        $data = $r->validate([
            'name'     => 'required|string|max:120',
            'position' => 'required|string|max:120',
            'bio'      => 'nullable|string|max:2000',
            'email'    => 'nullable|email|max:200',
            'order'    => 'nullable|integer|min:0',
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);
        if ($r->hasFile('photo')) {
            if ($member?->photo_path) (str_contains($member->photo_path, 'cloudinary.com') ? \App\Services\CloudinaryService::delete($member->photo_path) : Storage::disk('public')->delete($member->photo_path));
            $data['photo_path'] = \App\Http\Controllers\Admin\StudentController::storePhotoFile($r->file('photo'), 'website/staff');
        }
        unset($data['photo']);
        return $data;
    }

    // ─── Testimonials ─────────────────────────────────────────────────────
    public function testimonialsIndex()
    {
        return view('admin.website.testimonials.index', [
            'testimonials' => WebsiteTestimonial::orderByDesc('created_at')->paginate(20),
        ]);
    }
    public function testimonialsStore(Request $request)
    {
        $data = $request->validate([
            'parent_name' => 'required|string|max:120',
            'child_name'  => 'nullable|string|max:120',
            'message'     => 'required|string|max:2000',
            'rating'      => 'required|integer|min:1|max:5',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        WebsiteTestimonial::create($data);
        return back()->with('success', 'Testimonial added.');
    }
    public function testimonialsToggle(WebsiteTestimonial $testimonial)
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);
        return back()->with('success', ($testimonial->is_active ? 'Enabled' : 'Disabled').'.');
    }
    public function testimonialsDestroy(WebsiteTestimonial $testimonial)
    {
        $testimonial->delete();
        return back()->with('success', 'Testimonial deleted.');
    }

    // ─── Programs ─────────────────────────────────────────────────────────
    public function programsIndex()
    {
        $programs = WebsiteProgram::orderBy('order')->orderBy('name')->get();

        // Load the school's actual academic programs so admin can sync them
        $systemPrograms = collect();
        try {
            $existingNames = $programs->pluck('name')->map('strtolower');
            $systemPrograms = \App\Models\Program::orderBy('sequence')
                ->get()
                ->filter(fn($p) => ! $existingNames->contains(strtolower($p->name)));
        } catch (\Throwable $e) {
            // programs table may not exist — silently ignore
        }

        return view('admin.website.programs.index', compact('programs', 'systemPrograms'));
    }

    /**
     * Import all system programs (from the `programs` table) that are not
     * yet in `website_programs`, creating a website card for each one.
     */
    public function programsSync(Request $request)
    {
        $imported = 0;
        try {
            $existing = WebsiteProgram::pluck('name')->map('strtolower');
            $systemPrograms = \App\Models\Program::orderBy('sequence')->get();

            foreach ($systemPrograms as $idx => $p) {
                if ($existing->contains(strtolower($p->name))) continue;

                WebsiteProgram::create([
                    'name'        => $p->name,
                    'level'       => strtolower(str_replace(' ', '_', $p->name)),
                    'age_range'   => null,
                    'description' => $p->requirements
                        ? $p->requirements
                        : $p->name . ' — a comprehensive program rooted in academic excellence and Kingdom values.',
                    'curriculum'  => null,
                    'highlights'  => null,
                    'schedule'    => null,
                    'icon'        => '🎓',
                    'color'       => 'indigo',
                    'image_path'  => null,
                    'features'    => [],
                    'order'       => $idx,
                    'is_active'   => true,
                ]);
                $imported++;
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Sync failed: ' . $e->getMessage()]);
        }

        return redirect()->route('admin.website.programs.index')
            ->with('success', $imported > 0
                ? "$imported program(s) imported from the system. You can now add full descriptions, images and details to each one."
                : 'All system programs are already in the website programs list.');
    }

    public function programsCreate()
    {
        return view('admin.website.programs.form', ['program' => null]);
    }
    public function programsStore(Request $request)
    {
        WebsiteProgram::create($this->handleProgramSave($request));
        return redirect()->route('admin.website.programs.index')->with('success', 'Program added.');
    }
    public function programsEdit(WebsiteProgram $program)
    {
        return view('admin.website.programs.form', compact('program'));
    }
    public function programsUpdate(Request $request, WebsiteProgram $program)
    {
        $program->update($this->handleProgramSave($request));
        return redirect()->route('admin.website.programs.index')->with('success', 'Program updated.');
    }
    public function programsDestroy(WebsiteProgram $program)
    {
        if ($program->image_path && ! filter_var($program->image_path, FILTER_VALIDATE_URL)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($program->image_path);
        }
        $program->delete();
        return back()->with('success', 'Program deleted.');
    }

    private function handleProgramSave(Request $r): array
    {
        $data = $r->validate([
            'name'          => 'required|string|max:100',
            'level'         => 'nullable|string|max:50',   // free-form, not restricted
            'age_range'     => 'nullable|string|max:50',
            'description'   => 'required|string',
            'curriculum'    => 'nullable|string',
            'highlights'    => 'nullable|string',
            'schedule'      => 'nullable|string',
            'icon'          => 'nullable|string|max:80',
            'color'         => 'nullable|string|max:40',
            'order'         => 'nullable|integer|min:0',
            'is_active'     => 'nullable|boolean',
            'features_text' => 'nullable|string',   // textarea → array
            'program_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_image'  => 'nullable|boolean',
        ]);

        $data['is_active'] = $r->boolean('is_active');

        // Parse feature tags (one per line)
        if ($r->filled('features_text')) {
            $data['features'] = array_values(
                array_filter(array_map('trim', explode("\n", $r->features_text)))
            );
        } else {
            $data['features'] = [];
        }
        unset($data['features_text']);

        // Handle image upload
        if ($r->boolean('remove_image')) {
            $data['image_path'] = null;
        } elseif ($r->hasFile('program_image')) {
            $path = \App\Http\Controllers\Admin\StudentController::storePhotoFile(
                $r->file('program_image'), 'website/programs'
            );
            if ($path) $data['image_path'] = $path;
        }
        unset($data['program_image'], $data['remove_image']);

        return $data;
    }

    // ─── Contact Messages ─────────────────────────────────────────────────
    public function messagesIndex()
    {
        return view('admin.website.messages.index', [
            'messages' => WebsiteContactMessage::paginate(25),
        ]);
    }
    public function messagesShow(WebsiteContactMessage $message)
    {
        $message->update(['is_read' => true]);
        return view('admin.website.messages.show', compact('message'));
    }
    public function messagesDestroy(WebsiteContactMessage $message)
    {
        $message->delete();
        return redirect()->route('admin.website.messages.index')->with('success', 'Message deleted.');
    }

    // ─── Admission Applications ───────────────────────────────────────────
    public function applicationsIndex(Request $request)
    {
        $query = WebsiteAdmissionApplication::query();
        if ($request->filled('status')) $query->where('status', $request->status);
        return view('admin.website.applications.index', [
            'applications' => $query->paginate(25)->withQueryString(),
        ]);
    }
    public function applicationsShow(WebsiteAdmissionApplication $application)
    {
        return view('admin.website.applications.show', compact('application'));
    }
    public function applicationsUpdateStatus(Request $request, WebsiteAdmissionApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewing,accepted,rejected',
            'notes'  => 'nullable|string|max:2000',
        ]);
        $application->update(['status' => $request->status, 'notes' => $request->notes]);
        return back()->with('success', 'Status updated.');
    }
    public function applicationsDestroy(WebsiteAdmissionApplication $application)
    {
        $application->delete();
        return redirect()->route('admin.website.applications.index')->with('success', 'Deleted.');
    }
}
