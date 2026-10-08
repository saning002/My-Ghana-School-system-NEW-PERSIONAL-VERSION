<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:30',
            'password'  => 'nullable|string|min:6|confirmed',
            'photo'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $updates = $request->only(['full_name', 'phone']);

        if ($request->filled('password')) {
            $updates['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $newPath = $this->uploadPhoto($request);
            if ($newPath) {
                $updates['photo'] = $newPath;
            }
        }

        $user->update($updates);

        return back()->with('success', 'Profile updated successfully.');
    }

    private function uploadPhoto(Request $request): ?string
    {
        $file = $request->file('photo');
        try {
            if (CloudinaryService::isConfigured()) {
                $url = CloudinaryService::upload($file, 'admins/photos');
                if ($url) return $url;
            }
        } catch (\Throwable $e) {}

        try {
            return Storage::disk('public')->putFile('admins/photos', $file);
        } catch (\Throwable $e) {}

        return null;
    }
}
