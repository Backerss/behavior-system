<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\NotificationService;

class StudentPasswordController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * แสดงหน้าตั้งค่าของนักเรียน
     */
    public function showSettings()
    {
        $user = Auth::user();
        
        // ตรวจสอบว่าเป็นนักเรียนหรือไม่
        if ($user->users_role !== 'student') {
            return redirect()->route('dashboard')->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
        }

        return view('student.settings', compact('user'));
    }

    /**
     * เปลี่ยนรหัสผ่าน
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        
        // ตรวจสอบว่าเป็นนักเรียนหรือไม่
        if ($user->users_role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ดำเนินการนี้'
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ], [
            'current_password.required' => 'กรุณากรอกรหัสผ่านปัจจุบัน',
            'new_password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'new_password.min' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 8 ตัวอักษร',
            'new_password.confirmed' => 'รหัสผ่านใหม่และการยืนยันไม่ตรงกัน',
            'new_password_confirmation.required' => 'กรุณายืนยันรหัสผ่านใหม่'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $validator->errors()
            ], 422);
        }

        // ตรวจสอบรหัสผ่านปัจจุบัน
        if (!Hash::check($request->current_password, $user->users_password)) {
            return response()->json([
                'success' => false,
                'message' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'
            ], 422);
        }

        // ตรวจสอบว่ารหัสผ่านใหม่ไม่เหมือนเดิม
        if (Hash::check($request->new_password, $user->users_password)) {
            return response()->json([
                'success' => false,
                'message' => 'รหัสผ่านใหม่ต้องแตกต่างจากรหัสผ่านปัจจุบัน'
            ], 422);
        }

        try {
            // อัพเดทรหัสผ่าน
            $user->users_password = Hash::make($request->new_password);
            $user->users_updated_at = now();
            $user->save();

            // ส่งการแจ้งเตือนไปยังผู้ปกครอง
            $this->sendPasswordChangeNotification($user);

            // บันทึก log
            \Log::info('Student password changed', [
                'user_id' => $user->users_id,
                'student_name' => $user->users_first_name . ' ' . $user->users_last_name,
                'timestamp' => now(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'เปลี่ยนรหัสผ่านสำเร็จแล้ว'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error changing student password', [
                'user_id' => $user->users_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน กรุณาลองใหม่อีกครั้ง'
            ], 500);
        }
    }

    /**
     * ส่งการแจ้งเตือนการเปลี่ยนรหัสผ่านไปยังผู้ปกครอง
     */
    private function sendPasswordChangeNotification($user)
    {
        try {
            $student = $user->student;
            
            if ($student) {
                $title = "แจ้งเตือน: มีการเปลี่ยนรหัสผ่าน";
                $message = "นักเรียน {$user->users_first_name} {$user->users_last_name} " .
                          "ได้ทำการเปลี่ยนรหัสผ่านเข้าสู่ระบบเมื่อวันที่ " . 
                          now()->format('d/m/Y เวลา H:i น.') . 
                          " หากไม่ใช่การกระทำของนักเรียนโปรดติดต่อครูประจำชั้นทันที";

                $this->notificationService->sendToParent(
                    $student->students_id,
                    $title,
                    $message,
                    'security_alert',
                    ['system', 'email']
                );
            }
        } catch (\Exception $e) {
            \Log::error('Error sending password change notification', [
                'user_id' => $user->users_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
