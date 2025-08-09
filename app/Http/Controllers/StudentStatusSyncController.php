<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Services\GoogleSheetsImportService;

class StudentStatusSyncController extends Controller
{
    /**
     * ซิงค์สถานะนักเรียนจาก Google Sheet (gid=0)
     */
    public function sync(Request $request, GoogleSheetsImportService $sheets)
    {
        if (!auth()->check() || !in_array(auth()->user()->users_role, ['teacher','admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่ได้รับอนุญาต'
            ], 403);
        }

        try {
            $rows = $sheets->getSheetData(0);
            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลใน Google Sheet'
                ], 400);
            }

            $allowed = ['active','suspended','expelled','graduated','transferred'];
            $map = [
                'active' => 'active',
                'suspended' => 'suspended', 'พักการเรียน' => 'suspended',
                'expelled' => 'expelled', 'ไล่ออก' => 'expelled',
                'graduated' => 'graduated', 'จบการศึกษา' => 'graduated',
                'transferred' => 'transferred', 'transfer' => 'transferred', 'ย้ายโรงเรียน' => 'transferred',
            ];

            $codeToStatus = [];
            $invalidStatus = [];
            $notFound = [];

            foreach ($rows as $row) {
                $studentCode = trim($row['รหัสนักเรียน'] ?? '');
                $rawStatus = trim($row['สถานะ'] ?? '');
                if ($studentCode === '' || $rawStatus === '') continue;
                $key = mb_strtolower($rawStatus);
                $normalized = $map[$key] ?? null;
                if (!$normalized || !in_array($normalized, $allowed, true)) {
                    $invalidStatus[$studentCode] = $rawStatus;
                    continue;
                }
                $codeToStatus[$studentCode] = $normalized; // last wins
            }

            if (empty($codeToStatus)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่มีข้อมูลสถานะที่ถูกต้องสำหรับซิงค์',
                    'invalid_status' => $invalidStatus,
                ], 422);
            }

            $studentCodes = array_keys($codeToStatus);
            $students = Student::whereIn('students_student_code', $studentCodes)->get();

            $updated = [];
            $updatedDetails = [];
            $unchanged = [];
            $byCode = [];
            foreach ($students as $st) $byCode[$st->students_student_code] = $st;
            foreach ($studentCodes as $code) if (!isset($byCode[$code])) $notFound[] = $code;

            foreach ($byCode as $code => $student) {
                $newStatus = $codeToStatus[$code] ?? null;
                if (!$newStatus) continue;
                if ($student->students_status === $newStatus) { $unchanged[] = $code; continue; }
                $old = $student->students_status;
                Student::where('students_id', $student->students_id)->update(['students_status' => $newStatus, 'updated_at' => now()]);
                $updated[] = $code;
                $updatedDetails[] = [
                    'code' => $code,
                    'old' => $old,
                    'new' => $newStatus,
                    'name' => $student->full_name ?? ($student->user->users_first_name ?? '')
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'ซิงค์สถานะสำเร็จ',
                'summary' => [
                    'total_sheet_rows' => count($rows),
                    'valid_codes' => count($codeToStatus),
                    'updated' => count($updated),
                    'unchanged' => count($unchanged),
                    'not_found' => count($notFound),
                    'invalid_status' => count($invalidStatus),
                ],
                'details' => [
                    'updated' => $updated,
                    'updated_details' => $updatedDetails,
                    'unchanged' => $unchanged,
                    'not_found' => $notFound,
                    'invalid_status' => $invalidStatus,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
            ], 500);
        }
    }
}
