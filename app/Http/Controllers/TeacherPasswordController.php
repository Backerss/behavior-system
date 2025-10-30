<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\NotificationService;

class TeacherPasswordController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * ตรวจสอบสิทธิ์ครูประจำชั้น
     */
    public function checkPermission($studentId)
    {
        $user = Auth::user();
        
        // ตรวจสอบว่าเป็นครูหรือไม่
        if ($user->users_role !== 'teacher') {
            return response()->json([
                'success' => false,
                'hasPermission' => false,
                'message' => 'คุณไม่ใช่ครู'
            ]);
        }

        $teacher = $user->teacher;
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'hasPermission' => false,
                'message' => 'ไม่พบข้อมูลครู'
            ]);
        }

        // ดึงข้อมูลนักเรียนพร้อมกับห้องเรียน
        $student = Student::with('classroom')->where('students_id', $studentId)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'hasPermission' => false,
                'message' => 'ไม่พบข้อมูลนักเรียน'
            ]);
        }

        // ตรวจสอบสิทธิ์
        $hasPermission = $this->canResetPassword($teacher, $student);

        return response()->json([
            'success' => true,
            'hasPermission' => $hasPermission,
            'message' => $hasPermission ? 'มีสิทธิ์รีเซ็ตรหัสผ่าน' : 'ไม่มีสิทธิ์รีเซ็ตรหัสผ่าน'
        ]);
    }

    /**
     * รีเซ็ตรหัสผ่านนักเรียน
     */
    public function resetPassword(Request $request, $studentId)
    {
        $user = Auth::user();
        
        // ตรวจสอบว่าเป็นครูหรือไม่
        if ($user->users_role !== 'teacher') {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์ดำเนินการนี้'
            ], 403);
        }

        $teacher = $user->teacher;
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลครู'
            ], 404);
        }

        // ดึงข้อมูลนักเรียนพร้อมกับห้องเรียน
        $student = Student::with(['user', 'classroom'])->where('students_id', $studentId)->first();
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลนักเรียน'
            ], 404);
        }

        // ตรวจสอบสิทธิ์
        if (!$this->canResetPassword($teacher, $student)) {
            return response()->json([
                'success' => false,
                'message' => 'คุณไม่มีสิทธิ์รีเซ็ตรหัสผ่านของนักเรียนคนนี้ เฉพาะครูประจำชั้นเท่านั้น'
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ], [
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

        try {
            // อัพเดทรหัสผ่าน
            $student->user->users_password = Hash::make($request->new_password);
            $student->user->users_updated_at = now();
            $student->user->save();

            // ส่งการแจ้งเตือนไปยังผู้ปกครอง
            $this->sendPasswordResetNotification($teacher, $student);

            // บันทึก log
            \Log::info('Teacher reset student password', [
                'teacher_id' => $teacher->teachers_id,
                'teacher_name' => $teacher->user->users_first_name . ' ' . $teacher->user->users_last_name,
                'student_id' => $student->students_id,
                'student_name' => $student->user->users_first_name . ' ' . $student->user->users_last_name,
                'class_id' => $student->class_id,
                'timestamp' => now(),
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'รีเซ็ตรหัสผ่านสำเร็จแล้ว ระบบได้แจ้งเตือนผู้ปกครองแล้ว'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error resetting student password by teacher', [
                'teacher_id' => $teacher->teachers_id,
                'student_id' => $student->students_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน กรุณาลองใหม่อีกครั้ง'
            ], 500);
        }
    }

    /**
     * ตรวจสอบสิทธิ์ในการรีเซ็ตรหัสผ่าน
     */
    private function canResetPassword($teacher, $student)
    {
        // ตรวจสอบว่านักเรียนมีห้องเรียนหรือไม่
        if (!$student->classroom) {
            return false;
        }

        // ตรวจสอบว่าครูคนนี้เป็นครูประจำชั้นของนักเรียนหรือไม่
        return $student->classroom->teachers_id == $teacher->teachers_id;
    }

    /**
     * ส่งการแจ้งเตือนการรีเซ็ตรหัสผ่านไปยังผู้ปกครอง
     */
    private function sendPasswordResetNotification($teacher, $student)
    {
        try {
            $teacherName = $teacher->user->users_first_name . ' ' . $teacher->user->users_last_name;
            $studentName = $student->user->users_first_name . ' ' . $student->user->users_last_name;
            
            $title = "แจ้งเตือน: มีการรีเซ็ตรหัสผ่าน";
            $message = "ครูประจำชั้น {$teacherName} ได้ทำการรีเซ็ตรหัสผ่านให้กับนักเรียน {$studentName} " .
                      "เมื่อวันที่ " . now()->format('d/m/Y เวลา H:i น.') . " " .
                      "นักเรียนสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที " .
                      "หากมีข้อสงสัยกรุณาติดต่อครูประจำชั้น";

            $this->notificationService->sendToParent(
                $student->students_id,
                $title,
                $message,
                'password_reset',
                ['system']
            );

        } catch (\Exception $e) {
            \Log::error('Error sending password reset notification', [
                'teacher_id' => $teacher->teachers_id,
                'student_id' => $student->students_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
