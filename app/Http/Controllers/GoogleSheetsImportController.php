<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use DateTime;
use DateInterval;

class GoogleSheetsImportController extends Controller
{
    // ใช้ config แทน constant
    const GOOGLE_SHEETS_BASE_URL = 'https://docs.google.com/spreadsheets/d/1L3O0f5HdX_7cPw2jrQT4IaPsjw_jFD3O0aeH9ZQ499c';
    
    // รายการ Sheet tabs ที่มีอยู่
    const AVAILABLE_SHEETS = [
        'students' => [
            'name' => 'ข้อมูลนักเรียน',
            'gid' => '0', // Sheet แรกมักจะเป็น gid=0
            'description' => 'ข้อมูลนักเรียนทั้งหมด รวมรหัส ชื่อ อีเมล เบอร์โทร',
            'expected_columns' => ['รหัสนักเรียน', 'ชื่อจริง', 'นามสกุล', 'อีเมลล์', 'เบอร์โทรศัพท์'],
            'role' => 'student'
        ],
        'teachers' => [
            'name' => 'ข้อมูลครู',
            'gid' => '1271058773', // GID สำหรับ sheet ครู (ปรับเป็น 0 เพื่อทดสอบ)
            'description' => 'ข้อมูลครูและบุคลากร',
            'expected_columns' => ['รหัสครู', 'คำนำหน้า', 'ชื่อจริง', 'นามสกุล', 'อีเมล', 'เบอร์โทรศัพท์', 'ตำแหน่ง', 'กลุ่มสาระการเรียนรู้', 'วิชาที่สอน'],
            'role' => 'teacher'
        ],
        'guardians' => [
            'name' => 'ข้อมูลผู้ปกครอง',
            'gid' => '1436095810', // GID สำหรับ sheet ผู้ปกครอง (อาจต้องปรับตาม sheet จริง)
            'description' => 'ข้อมูลผู้ปกครองและความสัมพันธ์กับนักเรียน',
            'expected_columns' => ['รหัสผู้ปกครอง', 'ชื่อ', 'นามสกุล', 'ความสัมพันธ์', 'รหัสนักเรียน'],
            'role' => 'guardian'
        ]
    ];

    /**
     * แสดงหน้า import จาก Google Sheets (เฉพาะ Admin และ Teacher)
     */
    public function index()
    {
        // ตรวจสอบสิทธิ์ Admin และ Teacher
        if (!in_array(auth()->user()->users_role, ['admin', 'teacher'])) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์ในการเข้าถึงฟังก์ชันนี้');
        }

        return view('admin.google-sheets-import');
    }

    /**
     * แสดงรายการ Sheets ที่มีอยู่
     */
    public function getAvailableSheets()
    {
        try {
            // ตรวจสอบสิทธิ์ Admin และ Teacher
            if (auth()->check() && !in_array(auth()->user()->users_role, ['admin', 'teacher'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'คุณไม่มีสิทธิ์ในการเข้าถึงฟังก์ชันนี้'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'sheets' => self::AVAILABLE_SHEETS
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงข้อมูลจาก Google Sheets และแสดง Preview
     */
    public function preview()
    {
        try {
            // ตรวจสอบสิทธิ์ Admin และ Teacher
            if (auth()->check() && !in_array(auth()->user()->users_role, ['admin', 'teacher'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'คุณไม่มีสิทธิ์ในการเข้าถึงฟังก์ชันนี้'
                ], 403);
            }

            // รับพารามิเตอร์ sheet type
            $sheetType = request('sheet', 'students'); // default เป็น students
            
            // ตรวจสอบว่า sheet type ถูกต้อง
            if (!isset(self::AVAILABLE_SHEETS[$sheetType])) {
                return response()->json([
                    'success' => false,
                    'error' => 'ประเภท Sheet ไม่ถูกต้อง'
                ], 400);
            }

            // ดึงข้อมูลจาก Google Sheets
            $data = $this->fetchGoogleSheetsData($sheetType);
            
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'error' => 'ไม่พบข้อมูลใน Google Sheets หรือไฟล์ว่างเปล่า'
                ], 400);
            }

            if (count($data) < 2) {
                return response()->json([
                    'success' => false,
                    'error' => 'Google Sheets ต้องมีข้อมูลอย่างน้อย 2 แถว (header และข้อมูล)'
                ], 400);
            }

            // แปลงข้อมูลและตรวจสอบ
            $processedData = $this->processAndValidateData($data, $sheetType);

            return response()->json([
                'success' => true,
                'data' => $processedData,
                'total_rows' => count($processedData['valid_data']) + count($processedData['duplicate_data']) + count($processedData['error_data']),
                'message' => 'ดึงข้อมูลสำเร็จ'
            ]);

        } catch (Exception $e) {
            \Log::error('Google Sheets Preview Error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'timestamp' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * นำเข้าข้อมูลลงฐานข้อมูล
     */
    public function import(Request $request)
    {
        // ปิด PHP warnings และ notices ที่อาจรบกวน JSON response
        error_reporting(E_ERROR | E_PARSE);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);
        
        // เริ่มต้น output buffering เพื่อป้องกัน premature output
        if (!ob_get_level()) {
            ob_start();
        }
        
        try {
            // เพิ่ม execution time และ memory limit
            ini_set('max_execution_time', config('import.google_sheets.max_execution_time', 300));
            ini_set('memory_limit', config('import.google_sheets.memory_limit', '512M'));
            
            // แก้ไขปัญหา max_input_vars สำหรับข้อมูลจำนวนมาก
            ini_set('max_input_vars', config('import.google_sheets.max_input_vars', 5000));
            ini_set('max_input_nesting_level', config('import.google_sheets.max_input_nesting_level', 64));
            
            // ตรวจสอบสิทธิ์ Admin และ Teacher
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'กรุณาเข้าสู่ระบบ',
                    'error_code' => 401
                ], 401);
            }
            
            if (!in_array(auth()->user()->users_role, ['admin', 'teacher'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'คุณไม่มีสิทธิ์ในการเข้าถึงฟังก์ชันนี้',
                    'error_code' => 403
                ], 403);
            }

            $selectedData = $request->input('selected_data', []);
            
            if (empty($selectedData)) {
                return response()->json([
                    'success' => false,
                    'error' => 'ไม่มีข้อมูลที่เลือกสำหรับการนำเข้า',
                    'error_code' => 400
                ], 400);
            }
            
            if (!is_array($selectedData)) {
                return response()->json([
                    'success' => false,
                    'error' => 'รูปแบบข้อมูลไม่ถูกต้อง',
                    'error_code' => 422
                ], 422);
            }

            // การนำเข้าข้อมูลจริง

            $importResults = $this->importDataToDatabase($selectedData);

            // ล้าง output buffer ก่อน return JSON
            if (ob_get_level()) {
                ob_clean();
            }

            return response()->json([
                'success' => true,
                'message' => 'นำเข้าข้อมูลสำเร็จ',
                'results' => $importResults,
                'timestamp' => now()->toISOString()
            ], 200);

        } catch (Exception $e) {
            // ล้าง output buffer ในกรณี error
            if (ob_get_level()) {
                ob_clean();
            }
            
            \Log::error('Import Error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);
            
            // ส่งข้อมูล error ที่ละเอียดขึ้น
            $errorMessage = $e->getMessage();
            $statusCode = 500;
            
            // ตรวจสอบประเภทของ error
            if (strpos($errorMessage, 'timeout') !== false) {
                $statusCode = 408; // Request Timeout
                $errorMessage = 'การประมวลผลใช้เวลานานเกินไป กรุณาลองใหม่อีกครั้ง';
            } elseif (strpos($errorMessage, 'memory') !== false) {
                $statusCode = 413; // Payload Too Large
                $errorMessage = 'ข้อมูลมีขนาดใหญ่เกินไป กรุณาลดจำนวนข้อมูลและลองใหม่';
            } elseif (strpos($errorMessage, 'database') !== false || strpos($errorMessage, 'connection') !== false) {
                $statusCode = 503; // Service Unavailable
                $errorMessage = 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้ กรุณาลองใหม่อีกครั้ง';
            }
            
            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'error_code' => $statusCode,
                'timestamp' => now()->toISOString()
            ], $statusCode);
        }
    }

    /**
     * ดึงข้อมูลจาก Google Sheets
     */
    private function fetchGoogleSheetsData($sheetType = 'students')
    {
        try {
            // สร้าง URL สำหรับ sheet ที่เลือก
            $sheetConfig = self::AVAILABLE_SHEETS[$sheetType];
            
            // ลองใช้ GID ที่กำหนดไว้ก่อน
            $url = self::GOOGLE_SHEETS_BASE_URL . '/export?format=csv&gid=' . $sheetConfig['gid'];
            
            $context = stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ]
            ]);
            
            $response = file_get_contents($url, false, $context);
            
            // ถ้าดึงข้อมูลไม่ได้ด้วย GID ที่กำหนด ให้ลอง auto-detect สำหรับ teachers และ guardians
            if ($response === false && ($sheetType === 'teachers' || $sheetType === 'guardians')) {
                \Log::warning('Failed to fetch with configured GID, trying auto-detection', [
                    'sheet_type' => $sheetType,
                    'configured_gid' => $sheetConfig['gid']
                ]);
                
                if ($sheetType === 'teachers') {
                    return $this->tryMultipleGidsForTeachers();
                } elseif ($sheetType === 'guardians') {
                    return $this->tryMultipleGidsForGuardians();
                }
            }
            
            $response = file_get_contents($url, false, $context);
            
            if ($response === false) {
                throw new Exception('ไม่สามารถดึงข้อมูลจาก Google Sheets ได้ กรุณาตรวจสอบ URL และการตั้งค่า Public');
            }

            $rows = array_map("str_getcsv", explode("\n", $response));
            
            // ลบแถวว่างออก
            $rows = array_filter($rows, function($row) {
                return !empty(array_filter($row, function($cell) {
                    return !empty(trim($cell));
                }));
            });

            return array_values($rows);
            
        } catch (Exception $e) {
            throw new Exception('เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * ลองหา GID ที่ถูกต้องสำหรับ sheet ครู
     */
    private function tryMultipleGidsForTeachers()
    {
        // GID ที่เป็นไปได้สำหรับ sheet ครู (รวม GID ที่กำหนดไว้)
        $configuredGid = self::AVAILABLE_SHEETS['teachers']['gid'];
        $possibleGids = array_unique([
            $configuredGid, // GID ที่ผู้ใช้กำหนดไว้
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', // Sheet tabs ธรรมดา
            '2083513845', '1234567890', '987654321', // GID ที่อาจจะใช้
            '1002345678', '2001234567', '3001234567', // GID pattern อื่นๆ
        ]);
        
        foreach ($possibleGids as $gid) {
            try {
                $url = self::GOOGLE_SHEETS_BASE_URL . '/export?format=csv&gid=' . $gid;
                
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                    ]
                ]);
                
                $response = file_get_contents($url, false, $context);
                
                if ($response !== false) {
                    $rows = array_map("str_getcsv", explode("\n", $response));
                    
                    // ตรวจสอบว่าเป็นข้อมูลครูหรือไม่
                    if (!empty($rows) && count($rows) > 1) {
                        $headers = $rows[0];
                        
                        // ตรวจสอบคอลัมน์ที่บ่งชี้ว่าเป็นข้อมูลครู
                        $teacherIndicators = ['รหัสครู', 'T650', 'คำนำหน้า', 'ตำแหน่ง', 'กลุ่มสาระ', 'วิชาที่สอน'];
                        $foundTeacherData = false;
                        
                        // ตรวจสอบ headers ก่อน
                        foreach ($teacherIndicators as $indicator) {
                            foreach ($headers as $header) {
                                if (stripos($header, $indicator) !== false) {
                                    $foundTeacherData = true;
                                    break 2;
                                }
                            }
                        }
                        
                        // ตรวจสอบข้อมูลในแถวแรก
                        if (!$foundTeacherData && count($rows) > 1) {
                            $firstDataRow = $rows[1];
                            foreach ($firstDataRow as $cell) {
                                if (is_string($cell) && strpos($cell, 'T650') === 0) { // รหัสครูเริ่มด้วย T650
                                    $foundTeacherData = true;
                                    break;
                                }
                            }
                        }
                        
                        // ตรวจสอบว่าไม่ใช่ข้อมูลนักเรียน (รหัสนักเรียนเริ่มด้วยตัวเลข)
                        if (!$foundTeacherData) {
                            $firstDataRow = $rows[1] ?? [];
                            if (!empty($firstDataRow)) {
                                $firstCell = $firstDataRow[0] ?? '';
                                // ถ้าไม่เริ่มด้วยตัวเลข 6หรือ7 แสดงว่าอาจเป็นข้อมูลครู
                                if (!preg_match('/^[67]\d+/', $firstCell) && !empty($firstCell)) {
                                    $foundTeacherData = true;
                                }
                            }
                        }
                        
                        if ($foundTeacherData) {
                            // ลบแถวว่างออก
                            $rows = array_filter($rows, function($row) {
                                return !empty(array_filter($row, function($cell) {
                                    return !empty(trim($cell));
                                }));
                            });
                            
                            return array_values($rows);
                        }
                    }
                }
            } catch (Exception $e) {
                \Log::warning('Failed to fetch with GID', [
                    'gid' => $gid,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        throw new Exception('ไม่พบข้อมูลครูใน Google Sheets กรุณาตรวจสอบว่ามี sheet ที่มีข้อมูลครู');
    }

    /**
     * ลองหา GID ที่ถูกต้องสำหรับ sheet ผู้ปกครอง
     */
    private function tryMultipleGidsForGuardians()
    {
        // GID ที่เป็นไปได้สำหรับ sheet ผู้ปกครอง (รวม GID ที่กำหนดไว้)
        $configuredGid = self::AVAILABLE_SHEETS['guardians']['gid'];
        $possibleGids = array_unique([
            $configuredGid, // GID ที่ผู้ใช้กำหนดไว้
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', // Sheet tabs ธรรมดา
            '2083513845', '1234567890', '987654321', // GID ที่อาจจะใช้
            '1002345678', '2001234567', '3001234567', // GID pattern อื่นๆ
        ]);
        
        foreach ($possibleGids as $gid) {
            try {
                $url = self::GOOGLE_SHEETS_BASE_URL . '/export?format=csv&gid=' . $gid;
                
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 10,
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                    ]
                ]);
                
                $response = file_get_contents($url, false, $context);
                
                if ($response !== false) {
                    $rows = array_map("str_getcsv", explode("\n", $response));
                    
                    // ตรวจสอบว่าเป็นข้อมูลผู้ปกครองหรือไม่
                    if (!empty($rows) && count($rows) > 1) {
                        $headers = $rows[0];
                        
                        // ตรวจสอบคอลัมน์ที่บ่งชี้ว่าเป็นข้อมูลผู้ปกครอง
                        $guardianIndicators = ['รหัสผู้ปกครอง', 'ความสัมพันธ์', 'ผู้ปกครอง', 'Guardian', 'Parent'];
                        $foundGuardianData = false;
                        
                        // ตรวจสอบ headers ก่อน
                        foreach ($guardianIndicators as $indicator) {
                            foreach ($headers as $header) {
                                if (stripos($header, $indicator) !== false) {
                                    $foundGuardianData = true;
                                    break 2;
                                }
                            }
                        }
                        
                        // ตรวจสอบข้อมูลในแถวแรก
                        if (!$foundGuardianData && count($rows) > 1) {
                            $firstDataRow = $rows[1];
                            foreach ($firstDataRow as $cell) {
                                // ตรวจสอบคำที่บ่งชี้ความสัมพันธ์
                                if (is_string($cell) && (stripos($cell, 'บิดา') !== false || 
                                    stripos($cell, 'มารดา') !== false || 
                                    stripos($cell, 'ผู้ปกครอง') !== false ||
                                    stripos($cell, 'พ่อ') !== false ||
                                    stripos($cell, 'แม่') !== false)) {
                                    $foundGuardianData = true;
                                    break;
                                }
                            }
                        }
                        
                        // ตรวจสอบว่าไม่ใช่ข้อมูลนักเรียนหรือครู
                        if ($foundGuardianData) {
                            $firstDataRow = $rows[1] ?? [];
                            if (!empty($firstDataRow)) {
                                $firstCell = $firstDataRow[0] ?? '';
                                // ถ้าเริ่มด้วย T650 หรือ 6,7 แสดงว่าเป็นข้อมูลครูหรือนักเรียน
                                if (preg_match('/^(T650|[67]\d+)/', $firstCell)) {
                                    $foundGuardianData = false;
                                }
                            }
                        }
                        
                        if ($foundGuardianData) {
                            \Log::info('Found correct GID for guardians', [
                                'gid' => $gid,
                                'headers' => $headers,
                                'first_data' => $rows[1] ?? []
                            ]);
                            
                            // ลบแถวว่างออก
                            $rows = array_filter($rows, function($row) {
                                return !empty(array_filter($row, function($cell) {
                                    return !empty(trim($cell));
                                }));
                            });
                            
                            return array_values($rows);
                        } else {
                            \Log::info('Not guardian data - skipping GID', [
                                'gid' => $gid,
                                'headers' => $headers,
                                'first_data' => $rows[1] ?? []
                            ]);
                        }
                    }
                }
            } catch (Exception $e) {
                \Log::warning('Failed to fetch guardian data with GID', [
                    'gid' => $gid,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        throw new Exception('ไม่พบข้อมูลผู้ปกครองใน Google Sheets กรุณาตรวจสอบว่ามี sheet ที่มีข้อมูลผู้ปกครอง');
    }

    /**
     * รับ column mapping ตาม sheet type
     */
    private function getColumnMapping($sheetType)
    {
        $baseMapping = [
            'first_name' => ['ชื่อจริง', 'ชื่อ', 'first_name', 'firstname', 'fname', 'f_name', 'name'],
            'last_name' => ['นามสกุล', 'last_name', 'lastname', 'surname', 'lname', 'l_name'],
            'email' => ['อีเมลล์', 'อีเมล', 'email', 'e-mail', 'mail'],
            'phone' => ['โทร', 'เบอร์โทรศัพท์', 'เบอร์โทร', 'เบอร์', 'phone', 'telephone', 'mobile']
        ];

        switch ($sheetType) {
            case 'students':
                return array_merge($baseMapping, [
                    'student_id' => ['รหัสนักเรียน', 'student_id', 'รหัส', 'id', 'รหัสประจำตัว'],
                    'date_of_birth' => ['วันเกิด', 'date_of_birth', 'birthday', 'birth_date', 'dob'],
                    'gender' => ['เพศ', 'gender', 'sex'],
                    'grade_level' => ['ระดับชั้น', 'ระดับ', 'ชั้น', 'grade', 'level', 'class_level'],
                    'classroom' => ['ห้อง', 'ห้องเรียน', 'classroom', 'class', 'room'],
                    'status' => ['สถานะ', 'status']
                ]);

            case 'teachers':
                return array_merge($baseMapping, [
                    'teacher_id' => ['รหัสครู', 'teacher_id', 'รหัส', 'id', 'รหัสประจำตัว'],
                    'title' => ['คำนำหน้า', 'title', 'prefix', 'คำนำหน้าชื่อ'],
                    'position' => ['ตำแหน่ง', 'position', 'job_title', 'title_position'],
                    'subject_group' => ['กลุ่มสาระการเรียนรู้', 'กลุ่มสาระ', 'subject_group', 'learning_area'],
                    'subjects' => ['วิชาที่สอน', 'วิชา', 'subject', 'subjects', 'teaching_subject']
                ]);

            case 'guardians':
                return array_merge($baseMapping, [
                    'title' => ['คำนำหน้า', 'คำนำหน้าชื่อ', 'title', 'prefix', 'นาย', 'นาง', 'นางสาว'],
                    'guardian_id' => ['รหัสผู้ปกครอง', 'guardian_id', 'รหัส', 'id', 'รหัสประจำตัว'],
                    'relationship' => ['ความสัมพันธ์', 'relationship', 'relation'],
                    'line_id' => ['ไอดีไลน์', 'line_id', 'line', 'ไลน์', 'ID Line'],
                    'contact_method' => ['ช่องทางติดต่อที่ใช้บ่อยที่สุด', 'ช่องทางติดต่อที่ง่ายที่สุด', 'contact_method', 'preferred_contact', 'ช่องทางติดต่อ'],
                    'student_codes' => ['รหัสนักเรียนที่ดูแล', 'รหัสนักเรียน', 'student_codes', 'student_id', 'รหัสลูก', 'รหัสบุตร', 'รหัสนักเรียนภายใต้ความดูแล']
                ]);

            default:
                return $baseMapping;
        }
    }

    /**
     * รับ required fields ตาม sheet type
     */
    private function getRequiredFields($sheetType)
    {
        switch ($sheetType) {
            case 'students':
                return ['first_name', 'last_name'];
            case 'teachers':
                return ['first_name', 'last_name'];
            case 'guardians':
                return ['first_name', 'last_name'];
            default:
                return ['first_name', 'last_name'];
        }
    }

    /**
     * เติมข้อมูลที่หายไปตาม sheet type
     */
    private function fillMissingData($rowData, $sheetType, $index)
    {
        switch ($sheetType) {
            case 'students':
                // กำหนด role เป็น student
                if (!isset($rowData['role']) || empty($rowData['role'])) {
                    $rowData['role'] = 'student';
                }

                // กำหนด status เริ่มต้น
                if (!isset($rowData['status']) || empty($rowData['status'])) {
                    $rowData['status'] = 'active';
                }
                
                // คำนวณอายุและกำหนดคำนำหน้าชื่อ
                if (!isset($rowData['title']) || empty($rowData['title'])) {
                    $age = 0;
                    if (!empty($rowData['date_of_birth'])) {
                        try {
                            $dateValue = trim($rowData['date_of_birth']);
                            
                            // ตรวจสอบว่าเป็นข้อมูลที่ไม่ใช่วันที่ก่อน
                            if (!filter_var($dateValue, FILTER_VALIDATE_EMAIL) && 
                                strpos($dateValue, '@') === false && 
                                strlen($dateValue) <= 20 &&
                                !preg_match('/[a-zA-Z]{3,}/', $dateValue)) {
                                
                                $birthDate = new DateTime($dateValue);
                                $today = new DateTime();
                                $age = $today->diff($birthDate)->y;
                            }
                        } catch (Exception $e) {
                            // ถ้าแปลงวันที่ไม่ได้ให้ใช้อายุ 0
                            $age = 0;
                        }
                    }
                    
                    if (isset($rowData['gender'])) {
                        if ($rowData['gender'] === 'male' || $rowData['gender'] === 'ชาย') {
                            $rowData['title'] = ($age <= 15) ? 'เด็กชาย' : 'นาย';
                            $rowData['gender'] = 'male'; // มาตรฐานเป็น male
                        } elseif ($rowData['gender'] === 'female' || $rowData['gender'] === 'หญิง') {
                            $rowData['title'] = ($age <= 15) ? 'เด็กหญิง' : 'นางสาว';
                            $rowData['gender'] = 'female'; // มาตรฐานเป็น female
                        } else {
                            $rowData['title'] = 'เด็กชาย'; // ค่าเริ่มต้น
                        }
                    } else {
                        $rowData['title'] = 'เด็กชาย'; // ค่าเริ่มต้นถ้าไม่มีข้อมูลเพศ
                    }
                }

                // กำหนด students_academic_year จากรหัสนักเรียน
                if (isset($rowData['student_id']) && !empty($rowData['student_id'])) {
                    $studentCode = (string) $rowData['student_id'];
                    if (strlen($studentCode) >= 2) {
                        $yearCode = substr($studentCode, 0, 2);
                        $academicYear = '25' . $yearCode; // เช่น 65 -> 2565
                        $rowData['academic_year'] = $academicYear;
                    } else {
                        $rowData['academic_year'] = date('Y'); // ปีปัจจุบันถ้าไม่สามารถดึงได้
                    }
                } else {
                    $rowData['academic_year'] = date('Y');
                }
                
                // สร้าง email สำหรับนักเรียน
                if (!isset($rowData['email']) || empty($rowData['email'])) {
                    if (isset($rowData['student_id']) && !empty($rowData['student_id'])) {
                        $rowData['email'] = $rowData['student_id'] . '@student.school.ac.th';
                    } else {
                        $firstName = $rowData['first_name'] ?? '';
                        $lastName = $rowData['last_name'] ?? '';
                        if (!empty($firstName) && !empty($lastName)) {
                            $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName . $lastName));
                            $rowData['email'] = $email . ($index + 2) . '@student.school.ac.th';
                        }
                    }
                }

                // กำหนดคำนำหน้าสำหรับนักเรียน
                if (!isset($rowData['title']) || empty($rowData['title'])) {
                    if (isset($rowData['gender'])) {
                        if ($rowData['gender'] === 'male') {
                            $rowData['title'] = 'ด.ช.';
                        } elseif ($rowData['gender'] === 'female') {
                            $rowData['title'] = 'ด.ญ.';
                        } else {
                            $rowData['title'] = '';
                        }
                    } else {
                        $rowData['title'] = '';
                    }
                }
                break;

            case 'teachers':
                // กำหนด status เริ่มต้น
                if (!isset($rowData['status']) || empty($rowData['status'])) {
                    $rowData['status'] = 'active';
                }

                // จัดการ role ตามตำแหน่ง
                if (!isset($rowData['role']) || empty($rowData['role'])) {
                    // ถ้าตำแหน่งเป็น "ผู้ดูแลระบบ" ให้เป็น admin ไม่อย่างนั้นเป็น teacher
                    if (isset($rowData['position']) && $rowData['position'] === 'ผู้ดูแลระบบ') {
                        $rowData['role'] = 'admin';
                    } else {
                        $rowData['role'] = 'teacher';
                    }
                }
                
                // กำหนดคำนำหน้าชื่อสำหรับครู (เฉพาะ นาย นางสาว)
                if (!isset($rowData['title']) || empty($rowData['title'])) {
                    // ถ้าไม่มีข้อมูลคำนำหน้า ให้ใช้ค่าเริ่มต้น
                    $rowData['title'] = 'นาย'; // ค่าเริ่มต้น
                } else {
                    // ตรวจสอบและปรับปรุงคำนำหน้าให้ถูกต้อง
                    $title = trim($rowData['title']);
                    // รองรับรูปแบบต่างๆ เช่น "นาย", "นางสาว", "Mr.", "Ms." เป็นต้น
                    if (in_array($title, ['นาย', 'Mr.', 'Mr', 'มร.'])) {
                        $rowData['title'] = 'นาย';
                    } elseif (in_array($title, ['นางสาว', 'Ms.', 'Ms', 'Miss', 'นส.'])) {
                        $rowData['title'] = 'นางสาว';
                    } else {
                        // ถ้าไม่ตรงกับรูปแบบที่กำหนด ให้ใช้ค่าเริ่มต้น
                        $rowData['title'] = 'นาย';
                    }
                }
                
                // สร้าง email สำหรับครู
                if (!isset($rowData['email']) || empty($rowData['email'])) {
                    if (isset($rowData['teacher_id']) && !empty($rowData['teacher_id'])) {
                        $rowData['email'] = strtolower($rowData['teacher_id']) . '@teacher.school.ac.th';
                    } else {
                        $firstName = $rowData['first_name'] ?? '';
                        $lastName = $rowData['last_name'] ?? '';
                        if (!empty($firstName) && !empty($lastName)) {
                            $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName . $lastName));
                            $rowData['email'] = $email . ($index + 2) . '@teacher.school.ac.th';
                        }
                    }
                }
                
                // จัดการตำแหน่ง - เก็บตำแหน่งตรงตาม Google Sheets
                if (!isset($rowData['position']) || empty($rowData['position'])) {
                    $rowData['position'] = 'ครู'; // ค่าเริ่มต้น
                }
                
                // จัดการกลุ่มสาระการเรียนรู้
                if (!isset($rowData['subject_group']) || empty($rowData['subject_group'])) {
                    $rowData['subject_group'] = null;
                }
                
                // จัดการวิชาที่สอน
                if (!isset($rowData['subjects']) || empty($rowData['subjects'])) {
                    $rowData['subjects'] = null;
                }
                
                break;

            case 'guardians':
                // กำหนด role เป็น guardian
                if (!isset($rowData['role']) || empty($rowData['role'])) {
                    $rowData['role'] = 'guardian';
                }
                
                // ใช้คำนำหน้าที่มีอยู่ในข้อมูล (นาย, นาง, นางสาว)
                // ไม่ต้องแก้ไข title ถ้ามีอยู่แล้ว
                
                // สร้าง email สำหรับผู้ปกครอง (ถ้าไม่มี)
                if (!isset($rowData['email']) || empty($rowData['email'])) {
                    $firstName = $rowData['first_name'] ?? '';
                    $lastName = $rowData['last_name'] ?? '';
                    if (!empty($firstName) && !empty($lastName)) {
                        $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $firstName . $lastName));
                        $rowData['email'] = $email . ($index + 2) . '@guardian.school.ac.th';
                    }
                }
                
                // กำหนด users_birthdate เป็น null
                $rowData['date_of_birth'] = null;
                
                // จัดการเบอร์โทรศัพท์ (ใช้จากข้อมูลที่มี)
                if (!isset($rowData['phone']) || empty($rowData['phone'])) {
                    $rowData['phone'] = null;
                }
                
                // จัดการข้อมูล Line ID
                if (!isset($rowData['line_id']) || empty($rowData['line_id'])) {
                    $rowData['line_id'] = null;
                }
                
                // จัดการช่องทางติดต่อที่ต้องการ (line, email, phone)
                if (!isset($rowData['contact_method']) || empty($rowData['contact_method'])) {
                    $rowData['contact_method'] = 'phone'; // ค่าเริ่มต้น
                } else {
                    // ตรวจสอบว่าค่าที่ได้รับถูกต้องหรือไม่
                    $validMethods = ['line', 'email', 'phone'];
                    $method = strtolower(trim($rowData['contact_method']));
                    if (!in_array($method, $validMethods)) {
                        $rowData['contact_method'] = 'phone';
                    } else {
                        $rowData['contact_method'] = $method;
                    }
                }
                
                // จัดการรหัสนักเรียนที่ดูแล (เก็บไว้สำหรับการเชื่อมโยงภายหลัง)
                if (isset($rowData['student_codes']) && !empty($rowData['student_codes'])) {
                    // เก็บข้อมูลรหัสนักเรียนไว้สำหรับการประมวลผลต่อไป
                    // ข้อมูลอาจเป็น format: "650003, 650004" หรือ "650003,650004"
                    $rowData['student_codes'] = trim($rowData['student_codes']);
                }
                
                break;
        }

        return $rowData;
    }

    /**
     * แปลงและตรวจสอบความถูกต้องของข้อมูล
     */
    private function processAndValidateData($rawData, $sheetType = 'students')
    {
        if (empty($rawData)) {
            return [
                'valid_data' => [],
                'duplicate_data' => [],
                'error_data' => []
            ];
        }

        $headers = $rawData[0];
        $dataRows = array_slice($rawData, 1);

        // แผนที่สำหรับ mapping คอลัมน์ต่างๆ ตาม sheet type
        $columnMapping = $this->getColumnMapping($sheetType);

        // หาคอลัมน์ที่ตรงกัน
        $mappedHeaders = [];
        foreach ($columnMapping as $standardName => $possibleNames) {
            $found = false;
            foreach ($headers as $header) {
                $normalizedHeader = strtolower(trim($header));
                foreach ($possibleNames as $possibleName) {
                    if (strtolower($possibleName) === $normalizedHeader || 
                        strpos($normalizedHeader, strtolower($possibleName)) !== false) {
                        $mappedHeaders[$standardName] = $header;
                        $found = true;
                        break 2;
                    }
                }
            }
            if (!$found) {
                // ถ้าไม่เจอให้ใช้ค่าแรกที่มี
                if (isset($headers[array_search($standardName, array_keys($columnMapping))])) {
                    $mappedHeaders[$standardName] = $headers[array_search($standardName, array_keys($columnMapping))];
                }
            }
        }

        // ตรวจสอบว่ามีคอลัมน์ที่จำเป็นหรือไม่ ตาม sheet type
        $requiredFields = $this->getRequiredFields($sheetType);
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($mappedHeaders[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            // ลองหา auto mapping แบบง่ายๆ
            foreach ($missingFields as $missing) {
                foreach ($headers as $index => $header) {
                    if (!in_array($header, $mappedHeaders)) {
                        $mappedHeaders[$missing] = $header;
                        break;
                    }
                }
            }
        }

        // ถ้ายังขาดให้แสดง error พร้อมคอลัมน์ที่มี
        $finalMissing = [];
        foreach ($requiredFields as $field) {
            if (!isset($mappedHeaders[$field])) {
                $finalMissing[] = $field;
            }
        }

        if (!empty($finalMissing)) {
            throw new Exception('ไม่พบคอลัมน์ที่จำเป็น: ' . implode(', ', $finalMissing) . 
                             '\nคอลัมน์ที่พบ: ' . implode(', ', $headers) . 
                             '\nข้อมูลนี้เป็นข้อมูลนักเรียน จะถูกนำเข้าเป็น role = student โดยอัตโนมัติ');
        }

        $validData = [];
        $duplicateData = [];
        $errorData = [];

        foreach ($dataRows as $index => $row) {
            try {
                // ตรวจสอบความยาวของ row
                if (count($row) !== count($headers)) {
                    // เติมหรือตัดให้เท่ากับ headers
                    $row = array_pad(array_slice($row, 0, count($headers)), count($headers), '');
                }
                
                $originalRowData = array_combine($headers, $row);
                
                // แปลงเป็น standard format
                $rowData = [];
                foreach ($mappedHeaders as $standardName => $originalHeader) {
                    $value = isset($originalRowData[$originalHeader]) ? trim($originalRowData[$originalHeader]) : '';
                    
                    // ตรวจสอบเฉพาะสำหรับ date_of_birth
                    if ($standardName === 'date_of_birth' && !empty($value)) {
                        // ถ้าเป็นอีเมลหรือข้อความที่ไม่ใช่วันที่ ให้ตั้งเป็น null
                        if (filter_var($value, FILTER_VALIDATE_EMAIL) || 
                            strpos($value, '@') !== false || 
                            strlen($value) > 20 ||
                            preg_match('/[a-zA-Z]{3,}/', $value)) {
                            $value = null;
                        }
                    }
                    
                    $rowData[$standardName] = $value === '' ? null : $value;
                }
                
                // เติมข้อมูลที่หายไปตาม sheet type
                $rowData = $this->fillMissingData($rowData, $sheetType, $index);
                
                $validation = $this->validateRowData($rowData, $index + 2); // +2 เพราะ header และ index เริ่มจาก 0

                if (!empty($validation['errors'])) {
                    $errorData[] = [
                        'row_number' => $index + 2,
                        'data' => $rowData,
                        'original_data' => $originalRowData,
                        'errors' => $validation['errors']
                    ];
                } elseif ($validation['is_duplicate']) {
                    $duplicateData[] = [
                        'row_number' => $index + 2,
                        'data' => $rowData,
                        'original_data' => $originalRowData,
                        'duplicate_fields' => $validation['duplicate_fields']
                    ];
                } else {
                    $validData[] = [
                        'row_number' => $index + 2,
                        'data' => $rowData,
                        'original_data' => $originalRowData
                    ];
                }
            } catch (Exception $e) {
                $errorData[] = [
                    'row_number' => $index + 2,
                    'data' => $row,
                    'errors' => ['เกิดข้อผิดพลาดในการประมวลผล: ' . $e->getMessage()]
                ];
            }
        }

        return [
            'valid_data' => $validData,
            'duplicate_data' => $duplicateData,
            'error_data' => $errorData
        ];
    }

    /**
     * ตรวจสอบความถูกต้องของข้อมูลในแต่ละแถว
     */
    private function validateRowData($data, $rowNumber)
    {
        $errors = [];
        $isDuplicate = false;
        $duplicateFields = [];

        // ตรวจสอบฟิลด์ที่จำเป็น
        if (empty($data['first_name'])) {
            $errors[] = 'ชื่อเป็นฟิลด์ที่จำเป็น';
        }

        if (empty($data['last_name'])) {
            $errors[] = 'นามสกุลเป็นฟิลด์ที่จำเป็น';
        }

        // ตรวจสอบอีเมล (ถ้ามี หรือสร้างขึ้นมา)
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'รูปแบบอีเมลไม่ถูกต้อง';
            } else {
                // ตรวจสอบอีเมลซ้ำ
                $existingUser = User::where('users_email', $data['email'])->first();
                if ($existingUser) {
                    $isDuplicate = true;
                    $duplicateFields[] = 'email';
                }
            }
        }

        // แปลงและตรวจสอบ role
        $role = isset($data['role']) ? strtolower(trim($data['role'])) : '';
        $roleMapping = [
            'admin' => 'admin',
            'administrator' => 'admin',
            'ผู้ดูแลระบบ' => 'admin',
            'teacher' => 'teacher',
            'ครู' => 'teacher',
            'อาจารย์' => 'teacher',
            'student' => 'student',
            'นักเรียน' => 'student',
            'ผู้เรียน' => 'student',
            'guardian' => 'guardian',
            'ผู้ปกครอง' => 'guardian',
            'พ่อแม่' => 'guardian',
            'parent' => 'guardian'
        ];

        $mappedRole = null;
        if (!empty($role)) {
            foreach ($roleMapping as $input => $output) {
                if (strtolower($input) === $role || strpos($role, strtolower($input)) !== false) {
                    $mappedRole = $output;
                    break;
                }
            }
        }

        if (!$mappedRole) {
            $errors[] = 'บทบาทไม่ถูกต้อง (ต้องเป็น admin, teacher, student, หรือ guardian)';
        } else {
            $data['role'] = $mappedRole; // อัปเดตค่า role ที่ถูกต้อง
        }

        // ตรวจสอบข้อมูลเพิ่มเติมตาม role
        if ($mappedRole === 'student') {
            // สำหรับนักเรียน - ถ้ามีรหัสนักเรียนให้ตรวจสอบ
            if (isset($data['student_id']) && !empty($data['student_id'])) {
                $existingStudent = Student::where('students_student_code', $data['student_id'])->first();
                if ($existingStudent) {
                    $isDuplicate = true;
                    $duplicateFields[] = 'student_id';
                }
            }

            // ตรวจสอบเพศ (ถ้ามี)
            if (isset($data['gender']) && !empty($data['gender'])) {
                $gender = strtolower(trim($data['gender']));
                $genderMapping = [
                    'male' => 'male',
                    'female' => 'female',
                    'ชาย' => 'male',
                    'หญิง' => 'female',
                    'เพศชาย' => 'male',
                    'เพศหญิง' => 'female',
                    'm' => 'male',
                    'f' => 'female'
                ];
                
                $mappedGender = $genderMapping[$gender] ?? null;
                if (!$mappedGender) {
                    $errors[] = 'เพศไม่ถูกต้อง (ต้องเป็น ชาย/หญิง หรือ male/female)';
                } else {
                    $data['gender'] = $mappedGender;
                }
            }

            // ตรวจสอบวันเกิด (ถ้ามี)
            if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
                $originalDate = trim($data['date_of_birth']);
                
                // ตรวจสอบว่าเป็นข้อมูลที่ไม่ใช่วันที่ (เช่น อีเมล, ชื่อ)
                if (filter_var($originalDate, FILTER_VALIDATE_EMAIL) || 
                    strpos($originalDate, '@') !== false || 
                    strlen($originalDate) > 20 ||
                    preg_match('/[a-zA-Z]{3,}/', $originalDate)) {
                    // ถ้าเป็นอีเมลหรือข้อความที่ไม่ใช่วันที่ ให้ตั้งเป็น null
                    $data['date_of_birth'] = null;
                } else {
                    $dateFormats = ['Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'Y-n-j', 'j/n/Y', 'd/m/y', 'j/n/y'];
                    $validDate = false;
                    
                    foreach ($dateFormats as $format) {
                        try {
                            $dateObj = DateTime::createFromFormat($format, $originalDate);
                            if ($dateObj && $dateObj->format($format) === $originalDate) {
                                $data['date_of_birth'] = $dateObj->format('Y-m-d');
                                $validDate = true;
                                break;
                            }
                        } catch (Exception $e) {
                            // ข้ามถ้าแปลงไม่ได้
                            continue;
                        }
                    }
                    
                    // ลองแปลงรูปแบบพิเศษเพิ่มเติม
                    if (!$validDate) {
                        try {
                            // ลองแปลงจากรูปแบบ Excel date
                            if (is_numeric($originalDate) && $originalDate > 0 && $originalDate < 100000) {
                                // Excel stores dates as number of days since 1900-01-01
                                $excelEpoch = new DateTime('1900-01-01');
                                $excelEpoch->add(new DateInterval('P' . intval($originalDate - 2) . 'D')); // -2 for Excel leap year bug
                                $data['date_of_birth'] = $excelEpoch->format('Y-m-d');
                                $validDate = true;
                            }
                        } catch (Exception $e) {
                            // ถ้าแปลงไม่ได้ให้ผ่านไป
                        }
                    }
                    
                    if (!$validDate) {
                        // ถ้าแปลงไม่ได้ให้ตั้งเป็น null แทนการแสดง error
                        $data['date_of_birth'] = null;
                    }
                }
            }

            // ตรวจสอบสถานะ
            if (isset($data['status']) && !empty($data['status'])) {
                $validStatuses = ['active', 'suspended', 'expelled', 'graduated', 'transferred'];
                if (!in_array($data['status'], $validStatuses)) {
                    $data['status'] = 'active'; // ใช้ค่าเริ่มต้น
                }
            }
        } elseif ($mappedRole === 'teacher') {
            // สำหรับครู - ตรวจสอบรหัสพนักงาน
            if (isset($data['teacher_id']) && !empty($data['teacher_id'])) {
                $existingTeacher = Teacher::where('teachers_employee_code', $data['teacher_id'])->first();
                if ($existingTeacher) {
                    $isDuplicate = true;
                    $duplicateFields[] = 'teacher_id';
                }
            }
        } elseif ($mappedRole === 'guardian') {
            // สำหรับผู้ปกครอง - ตรวจสอบข้อมูลเฉพาะ
            if (isset($data['contact_method']) && !empty($data['contact_method'])) {
                $validMethods = ['phone', 'email', 'line'];
                $method = strtolower(trim($data['contact_method']));
                if (!in_array($method, $validMethods)) {
                    $data['contact_method'] = 'phone'; // ใช้ค่าเริ่มต้น
                }
            }
        }

        // ตรวจสอบเบอร์โทรศัพท์ (ถ้ามี)
        if (isset($data['phone']) && !empty($data['phone'])) {
            $phone = preg_replace('/[^\d]/', '', $data['phone']); // เอาเฉพาะตัวเลข
            if (strlen($phone) < 9 || strlen($phone) > 15) {
                $errors[] = 'เบอร์โทรศัพท์ต้องมี 9-15 หลัก';
            }
        }

        // ตรวจสอบ Line ID (ถ้ามี)
        if (isset($data['line_id']) && !empty($data['line_id']) && strlen($data['line_id']) > 100) {
            $errors[] = 'Line ID ยาวเกินไป (ไม่เกิน 100 ตัวอักษร)';
        }

        // ตรวจสอบความสัมพันธ์สำหรับ guardian (ถ้ามี)
        if ($mappedRole === 'guardian' && isset($data['relationship']) && !empty($data['relationship'])) {
            $validRelationships = ['พ่อ', 'แม่', 'ปู่', 'ย่า', 'ตา', 'ยาย', 'ลุง', 'ป้า', 'น้า', 'อา', 'ผู้ปกครอง', 'father', 'mother', 'parent'];
            $relationship = trim($data['relationship']);
            
        }

        return [
            'errors' => $errors,
            'is_duplicate' => $isDuplicate,
            'duplicate_fields' => $duplicateFields
        ];
    }

    /**
     * นำเข้าข้อมูลลงฐานข้อมูล
     */
    private function importDataToDatabase($selectedData)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        // แบ่งข้อมูลเป็น chunks เพื่อลด memory usage
        $chunkSize = config('import.google_sheets.chunk_size', 50);
        $chunks = array_chunk($selectedData, $chunkSize); // ประมวลผลตาม config
        
        foreach ($chunks as $chunkIndex => $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $item) {
                    try {
                        $userData = $item['data'];
                        
                        // สร้าง User
                        $userBirthdate = null;
                        
                        // สำหรับผู้ปกครอง ให้ users_birthdate = null ตามที่กำหนด
                        if ($userData['role'] !== 'guardian' && isset($userData['date_of_birth']) && !empty($userData['date_of_birth']) && $userData['date_of_birth'] !== null) {
                            // ตรวจสอบว่าเป็นวันที่จริงๆ ไม่ใช่อีเมลหรือข้อความอื่น
                            $dateValue = $userData['date_of_birth'];
                            if (!filter_var($dateValue, FILTER_VALIDATE_EMAIL) && 
                                strpos($dateValue, '@') === false && 
                                strlen($dateValue) <= 20 &&
                                !preg_match('/[a-zA-Z]{3,}/', $dateValue)) {
                                $userBirthdate = $dateValue;
                            }
                        }
                        
                        $user = User::create([
                            'users_name_prefix' => $userData['title'] ?? null,
                            'users_first_name' => $userData['first_name'],
                            'users_last_name' => $userData['last_name'],
                            'users_email' => $userData['email'],
                            'users_phone_number' => $userData['phone'] ?? null,
                            'users_password' => config('import.defaults.password'),
                            'users_role' => $userData['role'],
                            'users_birthdate' => $userBirthdate,
                            'users_created_at' => now(),
                            'users_updated_at' => now(),
                            'users_status' => $userData['status'] ?? config('import.defaults.status', 'active')
                        ]);

                        // สร้างข้อมูลตามบทบาท
                        $this->createRoleSpecificData($user, $userData);

                        $successCount++;

                    } catch (Exception $e) {
                        $errorCount++;
                        $errors[] = "แถว {$item['row_number']}: " . $e->getMessage();
                        \Log::error("Import row error", [
                            'row' => $item['row_number'],
                            'error' => $e->getMessage(),
                            'data' => isset($userData) ? $userData : null
                        ]);
                    }
                }

                DB::commit();

            } catch (Exception $e) {
                DB::rollback();
                \Log::error("Chunk " . ($chunkIndex + 1) . " failed", [
                    'error' => $e->getMessage(),
                    'chunk_size' => count($chunk)
                ]);
                
                // เพิ่ม error สำหรับ chunk นี้
                foreach ($chunk as $item) {
                    $errorCount++;
                    $errors[] = "แถว {$item['row_number']}: ไม่สามารถบันทึกข้อมูลได้เนื่องจากข้อผิดพลาดในระบบ";
                }
            }
            
            // หน่วงเวลาเล็กน้อยระหว่าง chunks เพื่อลดภาระระบบ
            if ($chunkIndex < count($chunks) - 1) {
                usleep(config('import.google_sheets.chunk_delay', 100000)); // ใช้ค่าจาก config
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'errors' => $errors
        ];
    }

    /**
     * สร้างข้อมูลเฉพาะตามบทบาท
     */
    private function createRoleSpecificData($user, $userData)
    {
        switch ($userData['role']) {
            case 'student':
                // หาหรือสร้างห้องเรียน
                $classroom = null;
                if (!empty($userData['grade_level']) && !empty($userData['classroom'])) {
                    // แปลงระดับชั้นให้เป็นรูปแบบ ม.1, ม.2, ม.3, ม.4, ม.5, ม.6
                    $gradeLevel = 'ม.' . $userData['grade_level'];
                    $roomNumber = $userData['classroom'];
                    
                    // หาห้องเรียนที่มีอยู่
                    $classroom = Classroom::where('classes_level', $gradeLevel)
                                          ->where('classes_room_number', $roomNumber)
                                          ->first();
                    
                    // ถ้าไม่มีให้สร้างใหม่
                    if (!$classroom) {
                        $classroom = Classroom::create([
                            'classes_level' => $gradeLevel,
                            'classes_room_number' => $roomNumber,
                            'classes_academic_year' => $userData['academic_year'] ?? date('Y'),
                            'teachers_id' => null
                        ]);
                    }
                }

                Student::create([
                    'user_id' => $user->users_id,
                    'students_student_code' => $userData['student_id'] ?? null,
                    'class_id' => $classroom ? $classroom->classes_id : null,
                    'students_academic_year' => $userData['academic_year'] ?? date('Y'),
                    'students_current_score' => config('import.defaults.student_score', 100),
                    'students_status' => $userData['status'] ?? config('import.defaults.status', 'active'),
                    'students_gender' => $userData['gender'] ?? null,
                    'students_created_at' => now()
                ]);
                break;

            case 'teacher':
            case 'admin':
                // สำหรับทั้ง teacher และ admin ให้สร้างข้อมูลใน tb_teachers เหมือนกัน
                // เพียงแต่ role ใน tb_users จะต่างกัน
                Teacher::create([
                    'users_id' => $user->users_id,
                    'teachers_employee_code' => $userData['teacher_id'] ?? null,
                    'teachers_position' => $userData['position'] ?? 'ครู',
                    'teachers_department' => $userData['subject_group'] ?? null,
                    'teachers_major' => $userData['subjects'] ?? null,
                    'teachers_is_homeroom_teacher' => false,
                    'assigned_class_id' => null
                ]);
                break;

            case 'guardian':
                try {
                    $guardian = Guardian::create([
                        'user_id' => $user->users_id,
                        'guardians_relationship_to_student' => 'ผู้ปกครอง',
                        'guardians_phone' => $userData['phone'] ?? null,
                        'guardians_email' => $userData['email'] ?? null,
                        'guardians_line_id' => $userData['line_id'] ?? null,
                        'guardians_preferred_contact_method' => $userData['contact_method'] ?? config('import.defaults.contact_method', 'phone'),
                        'guardians_created_at' => now(),
                        'updated_at' => now()
                    ]);

                    \Log::info('Guardian created successfully with ID: ' . $guardian->guardians_id);

                    // เชื่อมโยงผู้ปกครองกับนักเรียนผ่านตาราง tb_guardian_student
                    if (!empty($userData['student_codes'])) {
                        \Log::info('Processing student codes: ' . $userData['student_codes']);
                        
                        // แยกรหัสนักเรียนที่คั่นด้วย comma
                        $studentCodesArray = array_map('trim', explode(',', $userData['student_codes']));
                        \Log::info('Student codes array:', $studentCodesArray);
                        
                        foreach ($studentCodesArray as $studentCode) {
                            if (!empty($studentCode)) {
                                // ค้นหานักเรียนในฐานข้อมูล
                                $student = Student::where('students_student_code', $studentCode)->first();
                                
                                if ($student) {
                                    \Log::info('Found student with code ' . $studentCode . ', ID: ' . $student->students_id);
                                    
                                    // ตรวจสอบว่ามีความสัมพันธ์อยู่แล้วหรือไม่
                                    $existingRelation = DB::table('tb_guardian_student')
                                        ->where('guardian_id', $guardian->guardians_id)
                                        ->where('student_id', $student->students_id)
                                        ->exists();
                                    
                                    // ถ้าไม่มีข้อมูลซ้ำให้ insert
                                    if (!$existingRelation) {
                                        DB::table('tb_guardian_student')->insert([
                                            'guardian_id' => $guardian->guardians_id,
                                            'student_id' => $student->students_id,
                                            'guardian_student_created_at' => now()
                                        ]);
                                        \Log::info('Guardian-Student relationship created: Guardian ' . $guardian->guardians_id . ' -> Student ' . $student->students_id);
                                    } else {
                                        \Log::info('Guardian-Student relationship already exists');
                                    }
                                } else {
                                    \Log::warning('Student not found with code: ' . $studentCode);
                                }
                                // หากรหัสนักเรียนไม่พบในฐานข้อมูล ไม่ต้องทำอะไร (ตามที่ระบุไว้)
                            }
                        }
                    } else {
                        \Log::info('No student codes provided for guardian');
                    }
                } catch (Exception $e) {
                    \Log::error('Error creating guardian: ' . $e->getMessage());
                    throw new Exception("ไม่สามารถสร้างข้อมูลผู้ปกครองได้: " . $e->getMessage());
                }
                break;
        }
    }
}
