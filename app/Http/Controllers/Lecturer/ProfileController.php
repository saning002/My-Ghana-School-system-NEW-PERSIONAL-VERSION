<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        abort_unless(Setting::lecturerCan('edit_profile'), 403, 'Profile editing has been disabled by the administrator.');
        $lecturer = auth()->user();
        return view('lecturer.profile', compact('lecturer'));
    }

    public function update(Request $request)
    {
        abort_unless(Setting::lecturerCan('edit_profile'), 403, 'Profile editing has been disabled by the administrator.');

        $lecturer = auth()->user();

        $request->validate([
            'full_name'   => 'required|string|max:255',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string|max:500',
            'qualification' => 'nullable|string|max:255',
            'bio'         => 'nullable|string|max:2000',
            'password'    => 'nullable|string|min:6|confirmed',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $updates = $request->only(['full_name', 'phone', 'address', 'qualification', 'bio']);

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $newPath = $this->uploadPhoto($request);
            if ($newPath) {
                $this->deleteOldPhoto($lecturer->photo);
                $updates['photo'] = $newPath;
            }
        }

        $lecturer->update($updates);

        return back()->with('success', 'Profile updated successfully.');
    }

    private function uploadPhoto(Request $request): ?string
    {
        $file = $request->file('photo');

        try {
            if (CloudinaryService::isConfigured()) {
                $url = CloudinaryService::upload($file, 'lecturers/photos');
                if ($url) {
                    return $url;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('Lecturer photo upload failed: ' . $e->getMessage());
        }

        try {
            return Storage::disk('public')->putFile('lecturers/photos', $file);
        } catch (\Throwable $e) {
            \Log::warning('Lecturer photo local upload failed: ' . $e->getMessage());
        }

        return null;
    }

    private function deleteOldPhoto(?string $path): void
    {
        if (! $path) {
            return;
        }

        try {
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                CloudinaryService::delete($path);
                return;
            }

            $clean = ltrim(str_replace('\\', '/', $path), '/');
            if (Storage::disk('public')->exists($clean)) {
                Storage::disk('public')->delete($clean);
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to delete old lecturer photo: ' . $e->getMessage());
        }
    }
}
