# Feature Implementation Summary

## 1. ✅ Increased POST File Size Limits for Bulk Uploads

### Changes Made:
- **File**: `Dockerfile`
- **Configuration Added**:
  - `upload_max_filesize = 100M` (increases upload limit from default 2MB to 100MB)
  - `post_max_size = 100M` (matches post data size to upload limit)
  - `max_file_uploads = 100` (allows up to 100 file uploads per request)

### Impact:
- Bulk student imports with ZIP files can now handle much larger uploads
- Photo uploads in bulk operations won't fail due to size restrictions
- Post data and upload file sizes are now in sync

---

## 2. ✅ Customizable Login Backgrounds per Branch Admin

### Database Changes:
- **Migration**: `database/migrations/2026_05_25_000000_add_login_background_to_users.php`
  - Adds `login_background_url` column to `users` table
  - Nullable field to support existing users
  - Used to store URL of custom login background image

### Model Changes:
- **File**: `app/Models/User.php`
  - Added `login_background_url` to `$fillable` array
  - Allows mass assignment of background URL

### New Controller:
- **File**: `app/Http/Controllers/Admin/AdminSettingsController.php`
  - `show()`: Displays admin settings page
  - `updateLoginBackground()`: Handles background image uploads
  - `uploadLoginBackground()`: Tries Cloudinary first, falls back to local storage
  - `deleteOldBackground()`: Cleans up old background files
  - Only branch admins can customize backgrounds (super admins cannot)

### New View:
- **File**: `resources/views/admin/settings/profile.blade.php`
  - Beautiful settings page with account information
  - Image upload interface with live preview
  - Current background preview
  - Option to remove background
  - Info about best practices (group photos, ministry activities)
  - Settings link appears only for branch admins
  - Super admin message explains the feature is for branch admins only

### Authentication Updates:
- **File**: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - `branchLoginForm()` now fetches the branch's admin background URL
  - Passes background to login view
  - Super admin login shows standard background

### Login View Updates:
- **File**: `resources/views/auth/login-branch.blade.php`
  - Dynamic background applied via CSS
  - Uses gradient overlay on custom background for text readability
  - Shows notification when viewing customized branch login
  - Falls back to default gradient if no background set

### Route Updates:
- **File**: `routes/web.php`
  - `GET /admin/settings` → Show settings page
  - `POST /admin/settings/login-background` → Handle background upload

### Sidebar Navigation Update:
- **File**: `resources/views/layouts/app.blade.php`
  - Added "Settings" link to admin sidebar navigation
  - Icon: `fa-sliders-h`
  - Visible for all admins (but settings only available for branch admins)

---

## How It Works

### For Branch Admins:
1. Click **Settings** in the admin sidebar
2. Scroll to "Login Page Background" section
3. Upload a custom image (JPEG, PNG, WebP, GIF - max 10MB)
4. Image is automatically previewed before saving
5. Click **Save Background** to apply
6. Background now appears on the branch's login page
7. Other branch admins in that branch see the same background
8. Suggested use cases:
   - Group photo of students
   - Ministry event photo
   - Branch logo/branding
   - Building/campus photo

### For Super Admins:
- Settings page shows an informational message
- Cannot customize login backgrounds
- See the standard default login page

### For Login Users:
- When accessing `login/branch/{branch}`, if that branch has an admin with a background:
  - The background image is displayed as the login page background
  - A gradient overlay ensures text remains readable
  - A subtle notification shows "This is a customized login page for {Branch Name}"
- If no background is set:
  - Default gradient background is used
  - Maintains current look and feel

---

## File Storage

Images are uploaded using:
1. **Cloudinary** (if configured) - for persistent storage
2. **Local Storage** - fallback to `storage/app/public/admin/login-backgrounds/`
3. Both are automatically deleted when backgrounds are changed or removed

---

## Deployment Notes

For production deployment:
1. Run: `php artisan migrate` to create the `login_background_url` column
2. The Dockerfile changes will apply the PHP configuration to all new deployments
3. Existing deployments need the Dockerfile rebuild
4. No additional dependencies required

---

## Testing the Feature

**Setup Test Data:**
```bash
php artisan tinker

# Create branch with admin
$branch = ChurchBranch::create(['name' => 'Test Branch', 'code' => 'TEST']);
$admin = User::where('church_branch_id', $branch->id)->first();

# Update admin to have a background (URL)
$admin->update(['login_background_url' => 'https://example.com/image.jpg']);
exit
```

**Test Steps:**
1. Visit `/login` → Click "Test Branch"
2. You should see the custom background in the login form
3. Login as that branch admin
4. Click "Settings" → "Login Page Background"
5. Upload a new image
6. Logout and re-login to see the new background

---

## Git Commit Information

Commit: `ea8469f`
Message: "feat: Add customizable login backgrounds and increase PHP file upload limits"

Files Changed:
- `Dockerfile` (PHP configuration)
- `app/Models/User.php` (model update)
- `app/Http/Controllers/Admin/AdminSettingsController.php` (new)
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php` (authentication)
- `database/migrations/2026_05_25_000000_add_login_background_to_users.php` (new migration)
- `resources/views/admin/settings/profile.blade.php` (new view)
- `resources/views/auth/login-branch.blade.php` (login UI update)
- `resources/views/layouts/app.blade.php` (sidebar navigation)
- `routes/web.php` (new routes)

---

## Summary

✅ Both requested features have been successfully implemented:

1. **PHP Post File Size**: Increased to 100MB for bulk uploads
2. **Customizable Login Backgrounds**: Each branch admin can now upload and manage their own login page background, with the ability to personalize it with group photos or ministry branding

The implementation is production-ready and includes proper error handling, fallback storage options, and intuitive UI for managing backgrounds.
