<?php

namespace App\Services;

use App\Models\BehaviorReport;
use App\Models\Student;
use App\Models\BehaviorLog;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

/**
 * BehaviorReportService
 * 
 * บริการจัดการรายงานพฤติกรรมนักเรียน
 */
class BehaviorReportService
{
    /**
     * ดึงรายการรายงานพฤติกรรมตามเงื่อนไข
     * 
     * @param array $filters
     * @return Collection
     */
    public function getReports(array $filters = []): Collection
    {
        $query = BehaviorReport::with([
            'student.user',
            'student.classroom',
            'teacher.user',
            'violation'
        ]);

        // Apply filters
        if (isset($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        if (isset($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }

        if (isset($filters['violation_category'])) {
            $query->whereHas('violation', function ($q) use ($filters) {
                $q->where('violations_category', $filters['violation_category']);
            });
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        }

        return $query->orderBy('reports_report_date', 'desc')->get();
    }

    /**
     * ดึงรายละเอียดรายงานพฤติกรรม
     * 
     * @param int $id
     * @return BehaviorReport|null
     */
    public function getReportDetail(int $id): ?BehaviorReport
    {
        return BehaviorReport::with([
            'student.user',
            'student.classroom',
            'student.guardians.user',
            'teacher.user',
            'violation'
        ])->find($id);
    }

    /**
     * สร้างรายงานพฤติกรรมใหม่
     * 
     * @param array $data
     * @return BehaviorReport
     * @throws Exception
     */
    public function createReport(array $data): BehaviorReport
    {
        // Validate student exists
        $student = Student::find($data['student_id']);
        if (!$student) {
            throw new Exception('ไม่พบข้อมูลนักเรียน');
        }

        // Validate violation exists
        $violation = Violation::find($data['violation_id']);
        if (!$violation) {
            throw new Exception('ไม่พบข้อมูลประเภทการกระทำผิด');
        }

        // Create behavior report with snapshot points (abs to ensure positive deduction value)
        $report = BehaviorReport::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $data['teacher_id'],
            'violation_id' => $data['violation_id'],
            'reports_points_deducted' => abs($violation->violations_points_deducted ?? 0),
            'reports_description' => $data['description'] ?? '',
            'reports_evidence_path' => $data['reports_evidence_path'] ?? null,
            'reports_report_date' => $data['violation_datetime'] ?? now(),
        ]);

        // Update student score (deduct by snapshot)
        $this->deductStudentScoreBy($student, $report->reports_points_deducted);

        // Log creation
        $this->logBehavior($report->reports_id, 'create', $data['performed_by'] ?? null, null, [
            'student_id' => $report->student_id,
            'violation_id' => $report->violation_id,
            'reports_points_deducted' => $report->reports_points_deducted,
            'reports_report_date' => (string)$report->reports_report_date,
        ]);

        return $report;
    }

    /**
     * อัปเดตรายงานพฤติกรรม
     * 
     * @param BehaviorReport $report
     * @param array $data
     * @return BehaviorReport
     * @throws Exception
     */
    public function updateReport(BehaviorReport $report, array $data): BehaviorReport
    {
        // Load actual models from relationships
        $oldViolation = $report->violation()->first();
        $student = $report->student()->first();
        $before = [
            'violation_id' => $report->violation_id,
            'reports_points_deducted' => $report->reports_points_deducted,
            'reports_report_date' => (string)$report->reports_report_date,
            'reports_description' => $report->reports_description,
        ];

        // Validate new violation if changed
        if (isset($data['violation_id']) && $data['violation_id'] != $report->violation_id) {
            $newViolation = Violation::find($data['violation_id']);
            if (!$newViolation) {
                throw new Exception('ไม่พบข้อมูลประเภทการกระทำผิดใหม่');
            }

            // Update snapshot to new violation points
            $report->reports_points_deducted = abs($newViolation->violations_points_deducted ?? 0);
        }

        // Update report data
        $updateData = [];
        
        if (isset($data['violation_id'])) {
            $updateData['violation_id'] = $data['violation_id'];
        }
        
        if (isset($data['description'])) {
            $updateData['reports_description'] = $data['description'];
        }
        
        if (isset($data['reports_evidence_path'])) {
            // Delete old evidence if exists
            if ($report->reports_evidence_path) {
                Storage::disk('public')->delete($report->reports_evidence_path);
            }
            $updateData['reports_evidence_path'] = $data['reports_evidence_path'];
        }
        
        if (isset($data['violation_datetime'])) {
            $updateData['reports_report_date'] = $data['violation_datetime'];
        }

        $report->update($updateData);

        // Recalculate student's score from all of their reports using snapshots
        if ($student) {
            $this->recalcStudentScoreFromReports($student);
        }

        // Log update
        $after = [
            'violation_id' => $report->violation_id,
            'reports_points_deducted' => $report->reports_points_deducted,
            'reports_report_date' => (string)$report->reports_report_date,
            'reports_description' => $report->reports_description,
        ];
        $this->logBehavior($report->reports_id, 'update', $data['performed_by'] ?? null, $before, $after);

        return $report->fresh();
    }

    /**
     * ลบรายงานพฤติกรรม
     * 
     * @param BehaviorReport $report
     * @return bool
     */
    public function deleteReport(BehaviorReport $report): bool
    {
        // Load actual models from relationships
        $student = $report->student()->first();
        $violation = $report->violation()->first();

        // Log before delete
        $this->logBehavior($report->reports_id, 'delete', null, [
            'violation_id' => $report->violation_id,
            'reports_points_deducted' => $report->reports_points_deducted,
        ], null);

        // Delete evidence file if exists
        if ($report->reports_evidence_path) {
            Storage::disk('public')->delete($report->reports_evidence_path);
        }

        $deleted = $report->delete();

        // Recalculate score after deletion
        if ($deleted && $student) {
            $this->recalcStudentScoreFromReports($student);
        }

        return $deleted;
    }

    /**
     * จัดเก็บไฟล์หลักฐาน
     * 
     * @param UploadedFile $file
     * @return string
     */
    public function storeEvidence(UploadedFile $file): string
    {
        // Sanitize filename - แปลงอักขระพิเศษเป็น underscore
        $originalName = $file->getClientOriginalName();
        $sanitizedName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $filename = time() . '_' . $sanitizedName;
        
        // Store in storage/app/public/behavior_evidences
        $path = $file->storeAs('behavior_evidences', $filename, 'public');
        
        \Log::info('Evidence stored via BehaviorReportService', [
            'original_name' => $originalName,
            'sanitized_name' => $sanitizedName,
            'stored_path' => $path,
            'full_path' => storage_path('app/public/' . $path),
            'public_url' => asset('storage/' . $path)
        ]);
        
        return $path;
    }

    /**
     * อัปเดตคะแนนนักเรียนหลังจากบันทึกพฤติกรรม
     * 
     * @param Student $student
     * @param Violation $violation
     * @return void
     */
    private function deductStudentScoreBy(Student $student, int $points): void
    {
        $currentScore = (int)($student->students_current_score ?? 100);
        $newScore = max(0, $currentScore - abs($points));
        $student->update(['students_current_score' => $newScore]);

        // Auto-notify when crossing below threshold
        try {
            app(\App\Services\NotificationService::class)
                ->sendLowScoreAlertIfCrossed((int)$student->students_id, $currentScore, (int)$newScore);
        } catch (\Throwable $e) {
            // Do not block main flow
        }
    }

    /**
     * คืนคะแนนนักเรียนเมื่อลบรายงานพฤติกรรม
     * 
     * @param Student $student
     * @param Violation $violation
     * @return void
     */
    private function recalcStudentScoreFromReports(Student $student): void
    {
        // Sum all snapshot deductions for the student
        $totalDeducted = BehaviorReport::where('student_id', $student->students_id)
            ->sum('reports_points_deducted');
        $oldScore = (int)($student->students_current_score ?? 100);
        $newScore = max(0, 100 - (int) $totalDeducted);
        $student->update(['students_current_score' => $newScore]);

        // Auto-notify when crossing below threshold
        try {
            app(\App\Services\NotificationService::class)
                ->sendLowScoreAlertIfCrossed((int)$student->students_id, $oldScore, (int)$newScore);
        } catch (\Throwable $e) {
            // Do not block main flow
        }
    }

    private function logBehavior(int $reportId, string $action, ?int $performedBy, $before, $after): void
    {
        try {
            BehaviorLog::create([
                'behavior_report_id' => $reportId,
                'action_type' => $action,
                'performed_by' => $performedBy ?? 0,
                'before_change' => $before,
                'after_change' => $after,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Don't block main flow if logging fails
        }
    }

    /**
     * ดึงสถิติรายงานพฤติกรรม
     * 
     * @param array $filters
     * @return array
     */
    public function getReportStatistics(array $filters = []): array
    {
        $query = BehaviorReport::query();

        // Apply filters
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->dateRange($filters['date_from'], $filters['date_to']);
        } else {
            $query->currentMonth();
        }

        $totalReports = $query->count();
        
        $severityStats = $query->with('violation')
            ->get()
            ->groupBy('violation.violations_category')
            ->map(function ($group) {
                return $group->count();
            });

        $topViolations = $query->with('violation')
            ->get()
            ->groupBy('violation_id')
            ->map(function ($group) {
                return [
                    'violation' => $group->first()->violation,
                    'count' => $group->count()
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->values();

        return [
            'total_reports' => $totalReports,
            'severity_breakdown' => $severityStats,
            'top_violations' => $topViolations,
            'period' => $this->getStatisticsPeriod($filters)
        ];
    }

    /**
     * กำหนดช่วงเวลาสำหรับสถิติ
     * 
     * @param array $filters
     * @return array
     */
    private function getStatisticsPeriod(array $filters): array
    {
        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            return [
                'from' => Carbon::parse($filters['date_from'])->format('Y-m-d'),
                'to' => Carbon::parse($filters['date_to'])->format('Y-m-d'),
                'type' => 'custom'
            ];
        }

        $now = Carbon::now();
        return [
            'from' => $now->startOfMonth()->format('Y-m-d'),
            'to' => $now->endOfMonth()->format('Y-m-d'),
            'type' => 'current_month'
        ];
    }
}
