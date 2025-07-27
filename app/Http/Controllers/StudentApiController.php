<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\BehaviorReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StudentApiController extends Controller
{
    /**
     * ดึงข้อมูลนักเรียนและข้อมูลที่เกี่ยวข้อง
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            Log::info("Loading student data for ID: {$id}");
            
            // ดึงข้อมูลนักเรียนพร้อมความสัมพันธ์ต่างๆ
            $student = Student::with([
                'user', 
                'classroom', 
                'guardians' => function($query) {
                    $query->with('user');
                },
                'behaviorReports' => function($query) {
                    $query->with(['violation', 'teacher.user'])
                          ->latest('reports_report_date')
                          ->limit(5);
                }
            ])->find($id);
            
            if (!$student) {
                Log::warning("Student not found with ID: {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนักเรียน'
                ], 404);
            }
            
            // หากมีผู้ปกครองหลายคน ให้เลือกคนแรก
            $guardian = $student->guardians->first();
            
            // ปรับโครงสร้างข้อมูลให้ง่ายต่อการใช้งานใน JavaScript
            $formattedStudent = [
                'students_id' => $student->students_id,
                'students_student_code' => $student->students_student_code,
                'students_current_score' => $student->students_current_score,
                'id_number' => $student->id_number ?? null,
                'user' => [
                    'users_id' => $student->user->users_id,
                    'users_name_prefix' => $student->user->users_name_prefix,
                    'users_first_name' => $student->user->users_first_name,
                    'users_last_name' => $student->user->users_last_name,
                    'users_birthdate' => $student->user->users_birthdate,
                    'users_profile_image' => $student->user->users_profile_image,
                ],
                'classroom' => $student->classroom ? [
                    'classes_id' => $student->classroom->classes_id,
                    'classes_level' => $student->classroom->classes_level,
                    'classes_room_number' => $student->classroom->classes_room_number,
                    'classes_academic_year' => $student->classroom->classes_academic_year,
                ] : null,
                'guardian' => $guardian ? [
                    'guardians_id' => $guardian->guardians_id,
                    'guardians_phone' => $guardian->guardians_phone,
                    'guardians_email' => $guardian->guardians_email,
                    'user' => $guardian->user ? [
                        'users_name_prefix' => $guardian->user->users_name_prefix,
                        'users_first_name' => $guardian->user->users_first_name,
                        'users_last_name' => $guardian->user->users_last_name,
                    ] : null
                ] : null,
                'behavior_reports' => $student->behaviorReports->map(function($report) {
                    return [
                        'reports_id' => $report->reports_id,
                        'reports_report_date' => $report->reports_report_date,
                        'reports_description' => $report->reports_description,
                        'violation' => [
                            'violations_id' => $report->violation->violations_id,
                            'violations_name' => $report->violation->violations_name,
                            'violations_category' => $report->violation->violations_category,
                            'violations_points_deducted' => $report->violation->violations_points_deducted,
                        ],
                        'teacher' => [
                            'teachers_id' => $report->teacher->teachers_id,
                            'user' => [
                                'users_first_name' => $report->teacher->user->users_first_name,
                                'users_last_name' => $report->teacher->user->users_last_name,
                            ]
                        ]
                    ];
                })
            ];
            
            Log::info("Student data loaded successfully for ID: {$id}");
            
            return response()->json([
                'success' => true,
                'student' => $formattedStudent
            ]);
        } catch (\Exception $e) {
            Log::error("Error loading student data for ID {$id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลนักเรียน',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ดึงประวัติการเรียนของนักเรียนที่จบการศึกษาแล้ว
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGraduatedHistory($id)
    {
        try {
            Log::info("Loading graduated student history for ID: {$id}");
            
            // ดึงข้อมูลนักเรียนที่มีสถานะ graduated เท่านั้น
            $student = Student::with([
                'user', 
                'classroom',
                'guardians' => function($query) {
                    $query->with('user');
                }
            ])->where('students_id', $id)
              ->where('students_status', 'graduated')
              ->first();
            
            if (!$student) {
                Log::warning("Graduated student not found with ID: {$id}");
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนักเรียนที่จบการศึกษาแล้ว'
                ], 404);
            }
            
            // ดึงประวัติพฤติกรรมทั้งหมดของนักเรียน
            $behaviorHistory = BehaviorReport::with(['violation', 'teacher.user'])
                ->where('reports_student_id', $id)
                ->orderBy('reports_report_date', 'desc')
                ->get();
            
            // คำนวณสถิติ
            $totalViolations = $behaviorHistory->count();
            $totalScoreDeducted = $behaviorHistory->sum(function($report) {
                return $report->violation->violations_points_deducted ?? 0;
            });
            
            // คำนวณจำนวนปีการศึกษาที่เรียน (ประมาณการจากระดับชั้น)
            $gradeLevel = (int) filter_var($student->classroom->classes_level ?? 'ม.1', FILTER_SANITIZE_NUMBER_INT);
            $academicYears = max(1, $gradeLevel); // อย่างน้อย 1 ปี
            $averageScorePerYear = $academicYears > 0 ? round($totalScoreDeducted / $academicYears, 1) : 0;
            
            // หาผู้ปกครองคนแรก
            $guardian = $student->guardians->first();
            
            // จัดรูปแบบข้อมูลนักเรียน
            $formattedStudent = [
                'students_id' => $student->students_id,
                'student_id' => $student->students_student_code,
                'first_name' => $student->user->users_first_name,
                'last_name' => $student->user->users_last_name,
                'id_card_number' => $student->id_number,
                'birth_date' => $student->user->users_birthdate,
                'class_name' => $student->classroom ? 
                    $student->classroom->classes_level . '/' . $student->classroom->classes_room_number : 'ไม่ระบุ',
                'behavior_score' => $student->students_current_score,
                'profile_image' => $student->user->users_profile_image,
                'updated_at' => $student->updated_at,
                'guardian_name' => $guardian && $guardian->user ? 
                    $guardian->user->users_first_name . ' ' . $guardian->user->users_last_name : 'ไม่ระบุ',
                'guardian_phone' => $guardian->guardians_phone ?? 'ไม่ระบุ'
            ];
            
            // จัดรูปแบบประวัติพฤติกรรม
            $formattedHistory = $behaviorHistory->map(function($report) use ($student) {
                return [
                    'reports_id' => $report->reports_id,
                    'created_at' => $report->reports_report_date,
                    'violation_type' => $report->violation->violations_name,
                    'violation_category' => $report->violation->violations_category,
                    'score_deducted' => $report->violation->violations_points_deducted,
                    'description' => $report->reports_description,
                    'teacher_name' => $report->teacher && $report->teacher->user ? 
                        $report->teacher->user->users_first_name . ' ' . $report->teacher->user->users_last_name : 'ไม่ระบุ',
                    'grade_level' => $this->estimateGradeLevelFromDate($report->reports_report_date, $student)
                ];
            });
            
            // สถิติ
            $statistics = [
                'total_violations' => $totalViolations,
                'total_score_deducted' => $totalScoreDeducted,
                'average_score_per_year' => $averageScorePerYear,
                'academic_years' => $academicYears
            ];
            
            Log::info("Graduated student history loaded successfully for ID: {$id}");
            
            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $formattedStudent,
                    'behavior_history' => $formattedHistory,
                    'statistics' => $statistics
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error loading graduated student history for ID {$id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงประวัติข้อมูล',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ประมาณการระดับชั้นในขณะที่เกิดเหตุการณ์
     * 
     * @param string $reportDate
     * @param Student $student
     * @return string
     */
    private function estimateGradeLevelFromDate($reportDate, $student)
    {
        try {
            $currentGrade = (int) filter_var($student->classroom->classes_level ?? 'ม.1', FILTER_SANITIZE_NUMBER_INT);
            $currentYear = date('Y');
            $reportYear = date('Y', strtotime($reportDate));
            
            // คำนวณระดับชั้นโดยประมาณ
            $yearsDiff = $currentYear - $reportYear;
            $estimatedGrade = max(1, $currentGrade - $yearsDiff);
            
            return 'ม.' . $estimatedGrade;
        } catch (\Exception $e) {
            return 'ไม่ระบุ';
        }
    }
}