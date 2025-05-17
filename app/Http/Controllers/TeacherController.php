<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function updateProfile(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'users_name_prefix' => 'required|string|max:10',
                'users_first_name' => 'required|string|max:100',
                'users_last_name' => 'required|string|max:100',
                'users_phone_number' => 'nullable|string|max:20',
                'users_birthdate' => 'nullable|date',
                'teachers_position' => 'nullable|string|max:100',
                'teachers_department' => 'nullable|string|max:100',
                'teachers_major' => 'nullable|string|max:100',
                'users_profile_image' => 'nullable|image|max:2048', // เปลี่ยนชื่อฟิลด์ตรงนี้
                'current_password' => 'nullable|string',
                'new_password' => 'nullable|string|min:8|confirmed',
            ]);

            // Get the authenticated user
            $user = Auth::user();

            // Handle password change if provided
            if ($request->filled('current_password') && $request->filled('new_password')) {
                if (!Hash::check($request->current_password, $user->users_password)) { // แก้เป็น users_password
                    return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง']);
                }
                $user->users_password = Hash::make($request->new_password); // แก้เป็น users_password
            }

            // Update user info
            $user->users_name_prefix = $request->users_name_prefix;
            $user->users_first_name = $request->users_first_name;
            $user->users_last_name = $request->users_last_name;
            $user->users_phone_number = $request->users_phone_number;
            $user->users_birthdate = $request->users_birthdate;

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Debug info
                \Log::info('Found profile image file to upload');
                
                // Delete old image if it exists
                if ($user->users_profile_image) {
                    \Log::info('Deleting old profile image: ' . $user->users_profile_image);
                    Storage::delete('public/' . $user->users_profile_image);
                }
                
                // Make sure directory exists
                $directory = 'users_profiles';
                if (!Storage::disk('public')->exists($directory)) {
                    Storage::disk('public')->makeDirectory($directory);
                    \Log::info('Created directory: ' . $directory);
                }
                
                // Get file extension
                $extension = $request->file('profile_image')->getClientOriginalExtension();
                
                // Create custom filename with user ID
                $filename = 'user_' . $user->users_id . '_' . time() . '.' . $extension;
                
                // Store with custom filename in users directory
                try {
                    $path = $request->file('profile_image')->storeAs(
                        $directory,
                        $filename,
                        'public'
                    );
                    \Log::info('Stored file at: ' . $path);
                    
                    // Save the path to the database
                    $user->users_profile_image = $path;
                } catch (\Exception $e) {
                    \Log::error('File upload error: ' . $e->getMessage());
                    return back()->withErrors(['profile_image' => 'ไม่สามารถอัพโหลดรูปภาพได้: ' . $e->getMessage()]);
                }
            }

            $user->save();

            // Update teacher info if available
            if ($user->teacher) {
                $user->teacher->teachers_position = $request->teachers_position;
                $user->teacher->teachers_department = $request->teachers_department;
                $user->teacher->teachers_major = $request->teachers_major;
                $user->teacher->save();
            }

            return back()->with('success', 'โปรไฟล์ของคุณได้รับการอัปเดตเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return back()->withErrors(['general' => 'เกิดข้อผิดพลาดในการอัปเดตโปรไฟล์: ' . $e->getMessage()]);
        }
    }
}
