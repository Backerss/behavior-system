<?php

namespace App\Services;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleSheetsImportService
{
    const DEFAULT_PASSWORD = '$2y$12$Yq98CXdMRT3w20RJM2vyYuyhS918XgHt2afpZKqQqrDYXJ5V447w.'; // 123456789
    
    /**
     * บันทึก Log การนำเข้าข้อมูล
     */
    public function logImportActivity($action, $details, $userId = null)
    {
        Log::info('Google Sheets Import Activity', [
            'action' => $action,
            'details' => $details,
            'user_id' => $userId ?? auth()->id(),
            'timestamp' => now()
        ]);
    }

    /**
     * ตรวจสอบความถูกต้องของ URL Google Sheets
     */
    public function validateGoogleSheetsUrl($url)
    {
        $pattern = '/^https:\/\/docs\.google\.com\/spreadsheets\/d\/([a-zA-Z0-9-_]+)/';
        return preg_match($pattern, $url);
    }

    /**
     * สร้างรายงานสรุปการนำเข้า
     */
    public function generateImportSummary($results)
    {
        $summary = [
            'total_processed' => $results['success_count'] + $results['error_count'],
            'successful_imports' => $results['success_count'],
            'failed_imports' => $results['error_count'],
            'success_rate' => 0,
            'errors' => $results['errors']
        ];

        if ($summary['total_processed'] > 0) {
            $summary['success_rate'] = round(($summary['successful_imports'] / $summary['total_processed']) * 100, 2);
        }

        return $summary;
    }

    /**
     * ตรวจสอบและแปลงข้อมูลวันที่
     */
    public function validateAndFormatDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date && $date->format($format) === $dateString) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * ทำความสะอาดข้อมูลโทรศัพท์
     */
    public function cleanPhoneNumber($phone)
    {
        if (empty($phone)) {
            return null;
        }

        // ลบอักขระที่ไม่ใช่ตัวเลข
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // ตรวจสอบความยาว
        if (strlen($cleaned) < 9 || strlen($cleaned) > 12) {
            return null;
        }

        return $cleaned;
    }

    /**
     * สร้าง Username อัตโนมัติถ้าไม่มี
     */
    public function generateUsername($firstName, $lastName, $role)
    {
        $baseUsername = strtolower($firstName . '.' . $lastName);
        $username = $baseUsername;
        $counter = 1;

        while (User::where('users_email', $username . '@school.ac.th')->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username . '@school.ac.th';
    }

    /**
     * ตรวจสอบว่าห้องเรียนมีอยู่จริงหรือไม่
     */
    public function validateClassroom($classroomName)
    {
        if (empty($classroomName)) {
            return null;
        }

        $classroom = ClassRoom::where('class_name', $classroomName)
                             ->orWhere('classes_level', $classroomName)
                             ->first();

        return $classroom ? $classroom->class_id : null;
    }

    /**
     * สร้างรหัสพนักงานอัตโนมัติ
     */
    public function generateEmployeeCode($role)
    {
        $prefix = $role === 'teacher' ? 'T' : 'E';
        $year = date('Y');
        $lastCode = Teacher::where('teachers_employee_code', 'like', $prefix . $year . '%')
                          ->orderBy('teachers_employee_code', 'desc')
                          ->first();

        if ($lastCode) {
            $lastNumber = intval(substr($lastCode->teachers_employee_code, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * ตรวจสอบและทำความสะอาดข้อมูลก่อนนำเข้า
     */
    public function sanitizeData($data)
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // ลบ whitespace ที่ไม่จำเป็น
                $value = trim($value);
                
                // แปลงข้อมูลที่เป็น string ว่างเป็น null
                $value = $value === '' ? null : $value;
            }
            
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * ตรวจสอบข้อมูลที่อาจเป็นอันตราย
     */
    public function validateSecureData($data)
    {
        $dangerousPatterns = [
            '/<script/i',
            '/javascript:/i',
            '/onload=/i',
            '/onclick=/i',
            '/<iframe/i'
        ];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                foreach ($dangerousPatterns as $pattern) {
                    if (preg_match($pattern, $value)) {
                        throw new Exception("พบข้อมูลที่อาจเป็นอันตรายในฟิลด์ {$key}");
                    }
                }
            }
        }

        return true;
    }
}
