<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * NotificationService
 * 
 * บริการจัดการการแจ้งเตือน
 */
class NotificationService
{
    /**
     * ส่งการแจ้งเตือนไปยังผู้ปกครอง
     * 
     * @param int $studentId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param array $channels
     * @return bool
     */
    public function sendToParent(
        int $studentId, 
        string $title, 
        string $message, 
        string $type = 'behavior_alert',
        array $channels = ['system']
    ): bool {
        try {
            $student = Student::with(['user', 'guardians.user'])->find($studentId);
            
            if (!$student) {
                throw new Exception("ไม่พบข้อมูลนักเรียน ID: {$studentId}");
            }

            $guardians = $student->guardians;
            
            if ($guardians->isEmpty()) {
                Log::warning("No guardians found for student", ['student_id' => $studentId]);
                return false;
            }

            $sentCount = 0;
            
            foreach ($guardians as $guardian) {
                if ($this->createNotification($guardian->user_id, $title, $message, $type)) {
                    $sentCount++;
                    
                    // ส่งผ่านช่องทางอื่นๆ ถ้าระบุ
                    $this->sendThroughChannels($guardian, $title, $message, $channels);
                }
            }

            return $sentCount > 0;
            
        } catch (Exception $e) {
            Log::error('Error sending notification to parent', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ส่งการแจ้งเตือนไปยังครู
     * 
     * @param int $teacherId
     * @param string $title
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function sendToTeacher(
        int $teacherId, 
        string $title, 
        string $message, 
        string $type = 'system_alert'
    ): bool {
        try {
            $teacher = User::where('users_id', $teacherId)
                          ->where('users_role', 'teacher')
                          ->first();
            
            if (!$teacher) {
                throw new Exception("ไม่พบข้อมูลครู ID: {$teacherId}");
            }

            return $this->createNotification($teacher->users_id, $title, $message, $type);
            
        } catch (Exception $e) {
            Log::error('Error sending notification to teacher', [
                'teacher_id' => $teacherId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ส่งการแจ้งเตือนไปยังนักเรียน
     * 
     * @param int $studentId
     * @param string $title
     * @param string $message
     * @param string $type
     * @return bool
     */
    public function sendToStudent(
        int $studentId, 
        string $title, 
        string $message, 
        string $type = 'behavior_update'
    ): bool {
        try {
            $student = Student::with('user')->find($studentId);
            
            if (!$student || !$student->user) {
                throw new Exception("ไม่พบข้อมูลนักเรียน ID: {$studentId}");
            }

            return $this->createNotification($student->user->users_id, $title, $message, $type);
            
        } catch (Exception $e) {
            Log::error('Error sending notification to student', [
                'student_id' => $studentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ส่งการแจ้งเตือนแบบส่วนรวม
     * 
     * @param array $userIds
     * @param string $title
     * @param string $message
     * @param string $type
     * @return int จำนวนการแจ้งเตือนที่ส่งสำเร็จ
     */
    public function sendBulkNotification(
        array $userIds, 
        string $title, 
        string $message, 
        string $type = 'general'
    ): int {
        $sentCount = 0;
        
        foreach ($userIds as $userId) {
            if ($this->createNotification($userId, $title, $message, $type)) {
                $sentCount++;
            }
        }
        
        return $sentCount;
    }

    /**
     * สร้างการแจ้งเตือนในระบบ
     * 
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @return bool
     */
    private function createNotification(
        int $userId, 
        string $title, 
        string $message, 
        string $type
    ): bool {
        try {
            $notification = new Notification();
            $notification->user_id = $userId;
            $notification->type = $type;
            $notification->title = $title;
            $notification->message = $message;
            $notification->created_at = now();
            
            return $notification->save();
            
        } catch (Exception $e) {
            Log::error('Error creating notification', [
                'user_id' => $userId,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ส่งการแจ้งเตือนผ่านช่องทางต่างๆ
     * 
     * @param Guardian $guardian
     * @param string $title
     * @param string $message
     * @param array $channels
     * @return void
     */
    private function sendThroughChannels(
        Guardian $guardian, 
        string $title, 
        string $message, 
        array $channels
    ): void {
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $this->sendEmailNotification($guardian, $title, $message);
                    break;
                case 'sms':
                    $this->sendSMSNotification($guardian, $message);
                    break;
                case 'line':
                    $this->sendLineNotification($guardian, $title, $message);
                    break;
                // ไม่ต้องจัดการ 'system' เพราะจัดการแล้วใน createNotification
            }
        }
    }

    /**
     * ส่งการแจ้งเตือนทาง Email
     * 
     * @param Guardian $guardian
     * @param string $title
     * @param string $message
     * @return void
     */
    private function sendEmailNotification(Guardian $guardian, string $title, string $message): void
    {
        try {
            if (!$guardian->guardians_email) {
                Log::warning('Guardian email not found', ['guardian_id' => $guardian->guardians_id]);
                return;
            }

            // TODO: Implement actual email sending
            // Mail::to($guardian->guardians_email)->send(new BehaviorNotificationMail($title, $message));
            
            Log::info('Email notification sent', [
                'guardian_id' => $guardian->guardians_id,
                'email' => $guardian->guardians_email,
                'title' => $title
            ]);
            
        } catch (Exception $e) {
            Log::error('Error sending email notification', [
                'guardian_id' => $guardian->guardians_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ส่งการแจ้งเตือนทาง SMS
     * 
     * @param Guardian $guardian
     * @param string $message
     * @return void
     */
    private function sendSMSNotification(Guardian $guardian, string $message): void
    {
        try {
            if (!$guardian->guardians_phone) {
                Log::warning('Guardian phone not found', ['guardian_id' => $guardian->guardians_id]);
                return;
            }

            // TODO: Implement actual SMS sending
            // SMSService::send($guardian->guardians_phone, $message);
            
            Log::info('SMS notification sent', [
                'guardian_id' => $guardian->guardians_id,
                'phone' => $guardian->guardians_phone,
                'message_length' => strlen($message)
            ]);
            
        } catch (Exception $e) {
            Log::error('Error sending SMS notification', [
                'guardian_id' => $guardian->guardians_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ส่งการแจ้งเตือนทาง LINE
     * 
     * @param Guardian $guardian
     * @param string $title
     * @param string $message
     * @return void
     */
    private function sendLineNotification(Guardian $guardian, string $title, string $message): void
    {
        try {
            if (!$guardian->guardians_line_id) {
                Log::warning('Guardian LINE ID not found', ['guardian_id' => $guardian->guardians_id]);
                return;
            }

            // TODO: Implement actual LINE notification sending
            // LineNotifyService::send($guardian->guardians_line_id, $title, $message);
            
            Log::info('LINE notification sent', [
                'guardian_id' => $guardian->guardians_id,
                'line_id' => $guardian->guardians_line_id,
                'title' => $title
            ]);
            
        } catch (Exception $e) {
            Log::error('Error sending LINE notification', [
                'guardian_id' => $guardian->guardians_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ดึงการแจ้งเตือนของผู้ใช้
     * 
     * @param int $userId
     * @param int $limit
     * @param bool $unreadOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserNotifications(
        int $userId, 
        int $limit = 10, 
        bool $unreadOnly = false
    ) {
        $query = Notification::where('user_id', $userId);
        
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }
        
        return $query->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * ทำเครื่องหมายการแจ้งเตือนว่าอ่านแล้ว
     * 
     * @param int $notificationId
     * @param int $userId
     * @return bool
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            $notification = Notification::where('id', $notificationId)
                                      ->where('user_id', $userId)
                                      ->first();
            
            if (!$notification) {
                return false;
            }
            
            $notification->read_at = now();
            return $notification->save();
            
        } catch (Exception $e) {
            Log::error('Error marking notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ทำเครื่องหมายการแจ้งเตือนทั้งหมดว่าอ่านแล้ว
     * 
     * @param int $userId
     * @return int จำนวนการแจ้งเตือนที่อัปเดต
     */
    public function markAllAsRead(int $userId): int
    {
        try {
            return Notification::where('user_id', $userId)
                              ->whereNull('read_at')
                              ->update(['read_at' => now()]);
                              
        } catch (Exception $e) {
            Log::error('Error marking all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * ลบการแจ้งเตือนเก่า
     * 
     * @param int $days จำนวนวันที่เก่ากว่า
     * @return int จำนวนการแจ้งเตือนที่ลบ
     */
    public function cleanupOldNotifications(int $days = 30): int
    {
        try {
            $cutoffDate = now()->subDays($days);
            
            return Notification::where('created_at', '<', $cutoffDate)
                              ->delete();
                              
        } catch (Exception $e) {
            Log::error('Error cleaning up old notifications', [
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * สร้างการแจ้งเตือนอัตโนมัติสำหรับพฤติกรรม
     * 
     * @param int $studentId
     * @param string $violationName
     * @param int $pointsDeducted
     * @param int $currentScore
     * @return bool
     */
    public function createBehaviorNotification(
        int $studentId, 
        string $violationName, 
        int $pointsDeducted, 
        int $currentScore
    ): bool {
        $student = Student::with('user')->find($studentId);
        
        if (!$student) {
            return false;
        }

        $studentName = $student->user->users_first_name ?? 'นักเรียน';
        
        $title = "แจ้งเตือนพฤติกรรม: {$studentName}";
        $message = "{$studentName} ถูกหักคะแนน {$pointsDeducted} คะแนน " .
                  "เนื่องจาก{$violationName} คะแนนปัจจุบัน: {$currentScore}";
        
        // กำหนดประเภทการแจ้งเตือนตามความรุนแรง
        $type = $pointsDeducted >= 10 ? 'severe_behavior' : 
               ($pointsDeducted >= 5 ? 'medium_behavior' : 'light_behavior');
        
        // กำหนดช่องทางการแจ้งเตือนตามคะแนนปัจจุบัน
        $channels = ['system'];
        if ($currentScore <= 40) {
            $channels = array_merge($channels, ['email', 'sms']);
        } elseif ($currentScore <= 60) {
            $channels[] = 'email';
        }
        
        return $this->sendToParent($studentId, $title, $message, $type, $channels);
    }
}
