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
}