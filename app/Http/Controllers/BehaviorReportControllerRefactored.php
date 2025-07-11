<?php

namespace App\Http\Controllers;

use App\Models\BehaviorReport;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Violation;
use App\Services\BehaviorReportService;
use App\Http\Requests\StoreBehaviorReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * BehaviorReportControllerRefactored
 * 
 * จัดการระบบรายงานพฤติกรรมนักเรียน
 */
class BehaviorReportControllerRefactored extends Controller
{
    protected BehaviorReportService $behaviorReportService;

    public function __construct(BehaviorReportService $behaviorReportService)
    {
        $this->behaviorReportService = $behaviorReportService;
    }

    /**
     * แสดงรายการรายงานพฤติกรรม
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $this->buildFilters($request);
            $reports = $this->behaviorReportService->getReports($filters);

            return response()->json([
                'success' => true,
                'data' => $reports,
                'message' => 'ดึงข้อมูลรายงานสำเร็จ'
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching behavior reports', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'
            ], 500);
        }
    }

    /**
     * บันทึกรายงานพฤติกรรมใหม่
     * 
     * @param StoreBehaviorReportRequest $request
     * @return JsonResponse
     */
    public function store(StoreBehaviorReportRequest $request): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $teacher = $this->getCurrentTeacher();
            if (!$teacher) {
                return $this->unauthorizedResponse('ไม่พบข้อมูลครู');
            }

            $data = $this->prepareReportData($request, $teacher);
            $report = $this->behaviorReportService->createReport($data);

            DB::commit();

            Log::info('Behavior report created successfully', [
                'report_id' => $report->reports_id,
                'student_id' => $report->student_id,
                'teacher_id' => $report->teacher_id
            ]);

            return response()->json([
                'success' => true,
                'data' => $report->load(['student.user', 'teacher.user', 'violation']),
                'message' => 'บันทึกรายงานพฤติกรรมสำเร็จ'
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating behavior report', [
                'error' => $e->getMessage(),
                'request_data' => $request->validated(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล'
            ], 500);
        }
    }

    /**
     * แสดงรายละเอียดรายงานพฤติกรรม
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $report = $this->behaviorReportService->getReportDetail($id);
            
            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบรายงานที่ระบุ'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $report,
                'message' => 'ดึงข้อมูลรายงานสำเร็จ'
            ]);

        } catch (Exception $e) {
            Log::error('Error fetching behavior report detail', [
                'report_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล'
            ], 500);
        }
    }

    /**
     * อัปเดตรายงานพฤติกรรม
     * 
     * @param StoreBehaviorReportRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(StoreBehaviorReportRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $teacher = $this->getCurrentTeacher();
            if (!$teacher) {
                return $this->unauthorizedResponse('ไม่พบข้อมูลครู');
            }

            $report = BehaviorReport::findOrFail($id);
            
            // ตรวจสอบสิทธิ์ในการแก้ไข
            if (!$this->canEditReport($report, $teacher)) {
                return response()->json([
                    'success' => false,
                    'message' => 'คุณไม่มีสิทธิ์แก้ไขรายงานนี้'
                ], 403);
            }

            $data = $this->prepareReportData($request, $teacher);
            $updatedReport = $this->behaviorReportService->updateReport($report, $data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $updatedReport->load(['student.user', 'teacher.user', 'violation']),
                'message' => 'อัปเดตรายงานสำเร็จ'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating behavior report', [
                'report_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล'
            ], 500);
        }
    }

    /**
     * ลบรายงานพฤติกรรม
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();
        
        try {
            $teacher = $this->getCurrentTeacher();
            if (!$teacher) {
                return $this->unauthorizedResponse('ไม่พบข้อมูลครู');
            }

            $report = BehaviorReport::findOrFail($id);
            
            // ตรวจสอบสิทธิ์ในการลบ
            if (!$this->canDeleteReport($report, $teacher)) {
                return response()->json([
                    'success' => false,
                    'message' => 'คุณไม่มีสิทธิ์ลบรายงานนี้'
                ], 403);
            }

            $this->behaviorReportService->deleteReport($report);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ลบรายงานสำเร็จ'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Error deleting behavior report', [
                'report_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการลบข้อมูล'
            ], 500);
        }
    }

    /**
     * ดึงข้อมูลครูปัจจุบัน
     * 
     * @return Teacher|null
     */
    private function getCurrentTeacher(): ?Teacher
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        return Teacher::where('users_id', $user->users_id)->first();
    }

    /**
     * สร้างตัวกรองข้อมูล
     * 
     * @param Request $request
     * @return array
     */
    private function buildFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('student_id')) {
            $filters['student_id'] = $request->input('student_id');
        }

        if ($request->filled('teacher_id')) {
            $filters['teacher_id'] = $request->input('teacher_id');
        }

        if ($request->filled('violation_category')) {
            $filters['violation_category'] = $request->input('violation_category');
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        return $filters;
    }

    /**
     * เตรียมข้อมูลสำหรับบันทึกรายงาน
     * 
     * @param StoreBehaviorReportRequest $request
     * @param Teacher $teacher
     * @return array
     */
    private function prepareReportData(StoreBehaviorReportRequest $request, Teacher $teacher): array
    {
        $data = $request->validated();
        $data['teacher_id'] = $teacher->teachers_id;

        // จัดการไฟล์หลักฐาน
        if ($request->hasFile('evidence')) {
            $data['reports_evidence_path'] = $this->behaviorReportService->storeEvidence($request->file('evidence'));
        }

        return $data;
    }

    /**
     * ตรวจสอบสิทธิ์ในการแก้ไขรายงาน
     * 
     * @param BehaviorReport $report
     * @param Teacher $teacher
     * @return bool
     */
    private function canEditReport(BehaviorReport $report, Teacher $teacher): bool
    {
        // ครูสามารถแก้ไขได้เฉพาะรายงานของตนเอง
        // หรือครูใหญ่สามารถแก้ไขได้ทุกรายงาน
        return $report->teacher_id === $teacher->teachers_id || 
               $teacher->teachers_position === 'headmaster';
    }

    /**
     * ตรวจสอบสิทธิ์ในการลบรายงาน
     * 
     * @param BehaviorReport $report
     * @param Teacher $teacher
     * @return bool
     */
    private function canDeleteReport(BehaviorReport $report, Teacher $teacher): bool
    {
        // เหมือนกับการแก้ไข
        return $this->canEditReport($report, $teacher);
    }

    /**
     * Response สำหรับ unauthorized
     * 
     * @param string $message
     * @return JsonResponse
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], 401);
    }
}
