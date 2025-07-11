<?php

namespace App\Services;

use App\Models\BehaviorReport;
use App\Models\Student;
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

        // Create behavior report
        $report = BehaviorReport::create([
            'student_id' => $data['student_id'],
            'teacher_id' => $data['teacher_id'],
            'violation_id' => $data['violation_id'],
            'reports_description' => $data['description'] ?? '',
            'reports_evidence_path' => $data['reports_evidence_path'] ?? null,
            'reports_report_date' => $data['violation_datetime'] ?? now(),
        ]);

        // Update student score
        $this->updateStudentScore($student, $violation);

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

        // Validate new violation if changed
        if (isset($data['violation_id']) && $data['violation_id'] != $report->violation_id) {
            $newViolation = Violation::find($data['violation_id']);
            if (!$newViolation) {
                throw new Exception('ไม่พบข้อมูลประเภทการกระทำผิดใหม่');
            }

            // Restore old score and apply new violation
            if ($oldViolation && $student) {
                $this->restoreStudentScore($student, $oldViolation);
                $this->updateStudentScore($student, $newViolation);
            }
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

        // Restore student score
        if ($student && $violation) {
            $this->restoreStudentScore($student, $violation);
        }

        // Delete evidence file if exists
        if ($report->reports_evidence_path) {
            Storage::disk('public')->delete($report->reports_evidence_path);
        }

        return $report->delete();
    }

    /**
     * จัดเก็บไฟล์หลักฐาน
     * 
     * @param UploadedFile $file
     * @return string
     */
    public function storeEvidence(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        return $file->storeAs('behavior_evidences', $filename, 'public');
    }

    /**
     * อัปเดตคะแนนนักเรียนหลังจากบันทึกพฤติกรรม
     * 
     * @param Student $student
     * @param Violation $violation
     * @return void
     */
    private function updateStudentScore(Student $student, Violation $violation): void
    {
        $currentScore = $student->students_current_score ?? 100;
        $pointsDeducted = abs($violation->violations_points_deducted);
        $newScore = max(0, $currentScore - $pointsDeducted);

        $student->update([
            'students_current_score' => $newScore
        ]);
    }

    /**
     * คืนคะแนนนักเรียนเมื่อลบรายงานพฤติกรรม
     * 
     * @param Student $student
     * @param Violation $violation
     * @return void
     */
    private function restoreStudentScore(Student $student, Violation $violation): void
    {
        $currentScore = $student->students_current_score ?? 0;
        $pointsToRestore = abs($violation->violations_points_deducted);
        $newScore = min(100, $currentScore + $pointsToRestore);

        $student->update([
            'students_current_score' => $newScore
        ]);
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
