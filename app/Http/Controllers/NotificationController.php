<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Notification;
use App\Models\User;
use App\Models\Guardian;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * ส่งการแจ้งเตือนไปยังผู้ปกครอง
     */
    public function sendParentNotification(Request $request)
    {
        Log::info('Notification API called', ['request' => $request->all()]);

        // ตรวจสอบข้อมูล
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|integer',
            'message' => 'required|string|max:1000',
            'channels' => 'required|array',
            'channels.sms' => 'boolean',
            'channels.line' => 'boolean',
            'channels.system' => 'boolean',
            'phone' => 'required|string',
            'score' => 'required|numeric',
            'notification_type' => 'required|string'
        ]);

        if ($validator->fails()) {
            Log::warning('Notification validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // ดึงข้อมูลนักเรียนพร้อมผู้ปกครอง
            $student = Student::where('students_id', $request->student_id)->first();
            
            if (!$student) {
                Log::error('Student not found', ['student_id' => $request->student_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนักเรียน'
                ], 404);
            }

            // ดึงข้อมูล User ของนักเรียน
            $studentUser = User::where('users_id', $student->user_id)->first();
            
            if (!$studentUser) {
                Log::error('Student user not found', ['user_id' => $student->user_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลผู้ใช้ของนักเรียน'
                ], 404);
            }

            // ค้นหาผู้ปกครองของนักเรียน
            $guardian = null;
            $guardianUserId = null;

            // วิธีที่ 1: ค้นหาจากตาราง tb_guardian_student
            $guardianStudent = \DB::table('tb_guardian_student')
                ->where('student_id', $student->students_id)
                ->first();

            if ($guardianStudent) {
                $guardian = \DB::table('tb_guardians')
                    ->where('guardians_id', $guardianStudent->guardian_id)
                    ->first();
                
                if ($guardian) {
                    $guardianUserId = $guardian->user_id;
                    Log::info('Guardian found via guardian_student table', [
                        'guardian_id' => $guardian->guardians_id,
                        'user_id' => $guardianUserId
                    ]);
                }
            }

            // วิธีที่ 2: ถ้าไม่เจอ ให้ใช้ current user (ครูที่ส่งการแจ้งเตือน)
            if (!$guardianUserId) {
                $guardianUserId = auth()->user()->users_id;
                Log::info('Using current user as notification recipient', [
                    'user_id' => $guardianUserId,
                    'reason' => 'No guardian found for student'
                ]);
            }

            // สร้าง Notification
            $notification = new Notification();
            $notification->user_id = $guardianUserId; // ใช้ user_id ที่หาได้
            $notification->type = $request->notification_type;
            $notification->title = "แจ้งเตือนพฤติกรรม: " . ($studentUser->users_first_name ?? 'นักเรียน');
            $notification->message = $request->message;
            $notification->created_at = now();
            
            $saved = $notification->save();
            
            if (!$saved) {
                Log::error('Failed to save notification');
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่สามารถบันทึกการแจ้งเตือนได้'
                ], 500);
            }

            // ส่งการแจ้งเตือนตามช่องทางที่เลือก
            $channels = $request->channels;
            
            if ($channels['sms'] && $request->phone) {
                $this->sendSMS($request->phone, $request->message);
            }
            
            if ($channels['line']) {
                $this->sendLineNotification('line_id_placeholder', $request->message);
            }
            
            Log::info('Notification sent successfully', [
                'notification_id' => $notification->id,
                'student_id' => $request->student_id,
                'recipient_user_id' => $guardianUserId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'ส่งการแจ้งเตือนสำเร็จแล้ว',
                'notification_id' => $notification->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Notification sending failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'student_id' => $request->student_id ?? 'unknown'
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการส่งการแจ้งเตือน: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * ส่ง SMS (ระบบจำลอง)
     */
    private function sendSMS($phone, $message)
    {
        try {
            Log::info("ส่ง SMS ไปยัง: $phone, ข้อความ: $message");
            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * ส่งข้อความผ่าน LINE (ระบบจำลอง)
     */
    private function sendLineNotification($lineId, $message)
    {
        try {
            Log::info("ส่งข้อความผ่าน LINE ไปยัง: $lineId, ข้อความ: $message");
            return true;
        } catch (\Exception $e) {
            Log::error('LINE notification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}