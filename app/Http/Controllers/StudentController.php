<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\BehaviorReport;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Violation;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    /**
     * แสดงหน้า Dashboard ของนักเรียน
     */
    public function dashboard()
    {
        try {
            // ดึงข้อมูลผู้ใช้ที่เข้าสู่ระบบ
            $user = Auth::user();
            
            if (!$user) {
                return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบ');
            }
            
            // ดึงข้อมูลนักเรียน
            $student = DB::table('tb_students')
                ->leftJoin('tb_classes', 'tb_students.class_id', '=', 'tb_classes.classes_id')
                ->leftJoin('tb_teachers', 'tb_classes.teachers_id', '=', 'tb_teachers.teachers_id')
                ->leftJoin('tb_users as teacher_users', 'tb_teachers.users_id', '=', 'teacher_users.users_id')
                ->where('tb_students.user_id', $user->users_id)
                ->select(
                    'tb_students.*',
                    'tb_classes.classes_level',
                    'tb_classes.classes_room_number',
                    'teacher_users.users_name_prefix as teacher_prefix',
                    'teacher_users.users_first_name as teacher_first_name',
                    'teacher_users.users_last_name as teacher_last_name'
                )
                ->first();
            
            // ถ้าไม่พบข้อมูลนักเรียน ให้สร้างข้อมูลเริ่มต้น
            if (!$student) {
                Log::warning('Student not found for user_id: ' . $user->users_id);
                $student = (object)[
                    'students_id' => 1,
                    'user_id' => $user->users_id,
                    'class_id' => 1,
                    'students_student_code' => 'STD001',
                    'students_current_score' => 100,
                    'classes_level' => 'ม.',
                    'classes_room_number' => '4/1',
                    'classes_academic_year' => '2567',
                    'teacher_prefix' => 'นาย',
                    'teacher_first_name' => 'ครูทดสอบ',
                    'teacher_last_name' => 'ระบบ'
                ];
            }
            
            Log::info('Student found/created: ' . $student->students_id);
            
            // จัดเตรียมข้อมูลสำหรับ Dashboard
            $data = [
                'user' => $user,
                'student' => $student,
                'stats' => [
                    'current_score' => $student->students_current_score ?? 100,
                    'class_rank' => $this->getStudentRank($student->students_id, $student->class_id),
                    'total_students' => $this->getClassTotalStudents($student->class_id),
                    'rank_status' => $this->getRankStatus($student->students_current_score ?? 100)
                ],
                'recent_activities' => $this->getRecentActivities($student->students_id),
                'chart_data' => $this->getBehaviorChartData($student->students_id),
                'classroom_details' => $this->getClassroomDetails($student->class_id),
                'behavior_summary' => $this->getBehaviorSummary($student->students_id),
                'violation_distribution' => $this->getViolationDistribution($student->students_id),
                'top_students' => $this->getTopStudentsInClass($student->class_id, $student->students_id),
                'notifications' => $this->getStudentNotifications($user->users_id)
            ];
            
            Log::info('Dashboard data prepared', [
                'recent_activities_count' => is_countable($data['recent_activities']) ? count($data['recent_activities']) : 0,
                'student_id' => $student->students_id
            ]);
            
            return view('student.dashboard', $data);
            
        } catch (\Exception $e) {
            Log::error('Error in dashboard: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return view with minimal data to prevent white screen
            return view('student.dashboard', [
                'user' => Auth::user(),
                'student' => null,
                'stats' => [
                    'current_score' => 100,
                    'class_rank' => 1,
                    'total_students' => 1,
                    'rank_status' => ['label' => 'ดี', 'badge' => 'bg-primary', 'group' => 'กลุ่มมาตรฐาน']
                ],
                'recent_activities' => collect([]),
                'chart_data' => [
                    'labels' => [],
                    'datasets' => [
                        [
                            'label' => 'คะแนนสะสม',
                            'data' => [],
                            'borderColor' => '#1020AD',
                            'backgroundColor' => 'rgba(16, 32, 173, 0.1)',
                            'tension' => 0.3
                        ]
                    ]
                ],
                'classroom_details' => null,
                'behavior_summary' => null,
                'violation_distribution' => [
                    'labels' => ['ไม่มีข้อมูล'],
                    'data' => [1],
                    'colors' => ['#EEEEEE']
                ],
                'top_students' => [],
                'notifications' => []
            ]);
        }
    }
    
    /**
     * ดึงประวัติกิจกรรมล่าสุดของนักเรียนจากฐานข้อมูลจริงเท่านั้น
     */
    private function getRecentActivities($studentId)
    {
        if (!$studentId) {
            Log::warning('No studentId provided');
            return collect([]);
        }
        
        try {
            // ตรวจสอบว่ามีตารางหรือไม่
            $tableExists = DB::getSchemaBuilder()->hasTable('tb_behavior_reports');
            if (!$tableExists) {
                Log::error('Table tb_behavior_reports does not exist');
                return collect([]);
            }
            
            $totalReports = DB::table('tb_behavior_reports')
                ->where('student_id', $studentId)
                ->count();
            
            if ($totalReports === 0) {
                return collect([]);
            }
            
            // ดึงข้อมูลรายงานพฤติกรรมจริงจากฐานข้อมูล
            $reports = DB::table('tb_behavior_reports')
                ->leftJoin('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                ->leftJoin('tb_teachers', 'tb_behavior_reports.teacher_id', '=', 'tb_teachers.teachers_id')
                ->leftJoin('tb_users', 'tb_teachers.users_id', '=', 'tb_users.users_id')
                ->where('tb_behavior_reports.student_id', $studentId)
                ->orderBy('tb_behavior_reports.reports_report_date', 'desc')
                ->limit(5)
                ->select(
                    'tb_behavior_reports.reports_id',
                    'tb_behavior_reports.reports_report_date',
                    'tb_behavior_reports.reports_description',
                    'tb_behavior_reports.reports_points_deducted',
                    'tb_violations.violations_name',
                    'tb_violations.violations_category',
                    'tb_users.users_name_prefix',
                    'tb_users.users_first_name',
                    'tb_users.users_last_name'
                )
                ->get();
            
            // แปลงข้อมูลให้อยู่ในรูปแบบที่ต้องการ
            return $reports->map(function ($report) {
                $pointsDeducted = (int)($report->reports_points_deducted ?? 0); // ใช้ snapshot จาก tb_behavior_reports
                $isPositive = false; // ขณะนี้ snapshot เก็บเป็นค่าหัก (บวก) เสมอ
                $scoreChange = abs($pointsDeducted);
                
                // สร้างชื่อครู
                $teacherName = 'ระบบ';
                if ($report->users_first_name) {
                    $teacherName = ($report->users_name_prefix ?? '') . $report->users_first_name . ' ' . ($report->users_last_name ?? '');
                }
                
                // สร้างข้อความกิจกรรม
                $title = '';
                $title = "ถูกหักคะแนน -{$scoreChange} คะแนน";
                
                if ($report->violations_name) {
                    $title .= " จาก " . $report->violations_name;
                }
                
                // กำหนดสี badge ตามประเภท
                $badgeColor = 'bg-secondary';
                if ($isPositive) {
                    $badgeColor = 'bg-success';
                } else {
                    switch ($report->violations_category) {
                        case 'light':
                            $badgeColor = 'bg-warning';
                            break;
                        case 'medium':
                            $badgeColor = 'bg-danger';
                            break;
                        case 'severe':
                            $badgeColor = 'bg-dark';
                            break;
                        default:
                            $badgeColor = 'bg-danger';
                    }
                }
                
                return [
                    'id' => $report->reports_id,
                    'title' => $title,
                    'description' => $report->reports_description ?? '',
                    'date' => Carbon::parse($report->reports_report_date)->locale('th')->format('d M Y'),
                    'teacher' => $teacherName,
                    'is_positive' => $isPositive,
                    'badge_color' => $badgeColor,
                    'score_change' => $scoreChange,
                    'violation_category' => $report->violations_category ?? 'unknown'
                ];
            });
            
        } catch (\Exception $e) {
            Log::error('Error fetching recent activities: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * ดึงข้อมูลสรุปพฤติกรรมของนักเรียนจากฐานข้อมูลจริง
     */
    private function getBehaviorSummary($studentId)
    {
        if (!$studentId) return null;
        
        try {
            // จำนวนรายงานทั้งหมด
            $totalReports = DB::table('tb_behavior_reports')
                ->where('student_id', $studentId)
                ->count();
            
            if ($totalReports == 0) {
                return null;
            }
            
            // รายงานเชิงบวก/ลบ: ใช้ snapshot ใน tb_behavior_reports (ตอนนี้ถือเป็นหักคะแนนทั้งหมด)
            $positiveReports = 0; // ถ้าระบบมีคะแนนบวกในอนาคต ค่อยเพิ่มฟิลด์/ตรรกะ sign
            $negativeReports = $totalReports;
            
            // คะแนนรวมจาก snapshot
            $totalPositivePoints = 0; // ยังไม่รองรับ snapshot คะแนนบวก
            $totalNegativePoints = (int) DB::table('tb_behavior_reports')
                ->where('student_id', $studentId)
                ->sum('reports_points_deducted');
            
            $positiveRatio = 0;
            
            return [
                'total_reports' => $totalReports,
                'positive_reports' => $positiveReports,
                'negative_reports' => $negativeReports,
                'total_positive_points' => $totalPositivePoints ?: 0,
                'total_negative_points' => $totalNegativePoints ?: 0,
                'positive_ratio' => $positiveRatio,
                'last_report_date' => DB::table('tb_behavior_reports')
                    ->where('student_id', $studentId)
                    ->orderBy('reports_report_date', 'desc')
                    ->value('reports_report_date')
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting behavior summary: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ดึงข้อมูลการกระจายประเภทพฤติกรรมจากฐานข้อมูลจริง
     */
    private function getViolationDistribution($studentId)
    {
        if (!$studentId) {
            return [
                'labels' => ['ไม่มีข้อมูล'],
                'data' => [1],
                'colors' => ['#EEEEEE'],
            ];
        }
        
        try {
            $violationCounts = DB::table('tb_behavior_reports')
                ->join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                ->where('tb_behavior_reports.student_id', $studentId)
                ->select('tb_violations.violations_category', DB::raw('count(*) as count'))
                ->groupBy('tb_violations.violations_category')
                ->get();
            
            if ($violationCounts->isEmpty()) {
                return [
                    'labels' => ['ไม่มีข้อมูล'],
                    'data' => [1],
                    'colors' => ['#EEEEEE'],
                ];
            }
            
            $labels = [];
            $data = [];
            $colors = [];
            
            foreach ($violationCounts as $item) {
                switch ($item->violations_category) {
                    case 'light':
                        $labels[] = 'เบา';
                        $colors[] = '#28a745';
                        break;
                    case 'medium':
                        $labels[] = 'ปานกลาง';
                        $colors[] = '#ffc107';
                        break;
                    case 'severe':
                        $labels[] = 'หนัก';
                        $colors[] = '#dc3545';
                        break;
                    default:
                        $labels[] = 'อื่นๆ';
                        $colors[] = '#6c757d';
                }
                $data[] = $item->count;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting violation distribution: ' . $e->getMessage());
            return [
                'labels' => ['ไม่มีข้อมูล'],
                'data' => [1],
                'colors' => ['#EEEEEE'],
            ];
        }
    }
    
    /**
     * ดึงข้อมูลรายละเอียดห้องเรียนจากฐานข้อมูลจริง
     */
    private function getClassroomDetails($classId)
    {
        if (!$classId) return null;
        
        try {
            $classroom = DB::table('tb_classes')
                ->leftJoin('tb_teachers', 'tb_classes.teachers_id', '=', 'tb_teachers.teachers_id')
                ->leftJoin('tb_users', 'tb_teachers.users_id', '=', 'tb_users.users_id')
                ->where('tb_classes.classes_id', $classId)
                ->select(
                    'tb_classes.*',
                    'tb_users.users_name_prefix',
                    'tb_users.users_first_name',
                    'tb_users.users_last_name'
                )
                ->first();
            
            if (!$classroom) return null;
            
            $totalStudents = $this->getClassTotalStudents($classId);
            $highScore = DB::table('tb_students')->where('class_id', $classId)->max('students_current_score') ?? 100;
            $avgScore = DB::table('tb_students')->where('class_id', $classId)->avg('students_current_score') ?? 100;
            
            // ใช้ AcademicYearService เพื่อดึงปีการศึกษาปัจจุบัน
            $academicYear = app(\App\Services\AcademicYearService::class)->getCurrentAcademicYear();

            return [
                'name' => $classroom->classes_level . $classroom->classes_room_number,
                'academic_year' => $academicYear,
                'teacher_name' => ($classroom->users_name_prefix ?? '') . ($classroom->users_first_name ?? '') . ' ' . ($classroom->users_last_name ?? ''),
                'total_students' => $totalStudents,
                'highest_score' => round($highScore),
                'average_score' => round($avgScore)
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting classroom details: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * ดึงข้อมูลอันดับนักเรียนในห้อง
     */
    private function getTopStudentsInClass($classId, $currentStudentId, $limit = 5)
    {
        if (!$classId) return [];
        
        try {
            $topStudents = DB::table('tb_students')
                ->join('tb_users', 'tb_students.user_id', '=', 'tb_users.users_id')
                ->where('tb_students.class_id', $classId)
                ->orderBy('tb_students.students_current_score', 'desc')
                ->limit($limit)
                ->select(
                    'tb_students.students_id',
                    'tb_students.students_current_score',
                    'tb_users.users_name_prefix',
                    'tb_users.users_first_name',
                    'tb_users.users_last_name'
                )
                ->get();
            
            return $topStudents->map(function ($student, $index) use ($currentStudentId) {
                return [
                    'rank' => $index + 1,
                    'name' => ($student->users_name_prefix ?? '') . $student->users_first_name . ' ' . ($student->users_last_name ?? ''),
                    'score' => $student->students_current_score,
                    'is_current' => $student->students_id == $currentStudentId
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            Log::error('Error getting top students: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * ดึงข้อมูลการแจ้งเตือนจากฐานข้อมูลจริง
     */
    private function getStudentNotifications($userId, $limit = 5)
    {
        if (!$userId) return [];
        
        try {
            $notifications = DB::table('tb_notifications')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
            
            return $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title ?? 'การแจ้งเตือน',
                    'message' => $notification->message ?? '',
                    'type' => $notification->type ?? 'info',
                    'created_at' => Carbon::parse($notification->created_at)->locale('th')->format('d M Y'),
                    'is_read' => isset($notification->read_at) && !is_null($notification->read_at)
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            Log::error('Error getting notifications: ' . $e->getMessage());
            return [];
        }
    }
    
    // Helper Methods
    private function getStudentRank($studentId, $classId)
    {
        if (!$studentId || !$classId) return 1;
        
        try {
            $student = DB::table('tb_students')->where('students_id', $studentId)->first();
            if (!$student) return 1;
            
            return DB::table('tb_students')
                ->where('class_id', $classId)
                ->where('students_current_score', '>', $student->students_current_score)
                ->count() + 1;
                
        } catch (\Exception $e) {
            Log::error('Error getting student rank: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function getClassTotalStudents($classId)
    {
        if (!$classId) return 1;
        
        try {
            $count = DB::table('tb_students')->where('class_id', $classId)->count();
            return $count > 0 ? $count : 1;
        } catch (\Exception $e) {
            Log::error('Error getting class total students: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function getRankStatus($score)
    {
        if ($score >= 90) {
            return ['label' => 'ดีเยี่ยม', 'badge' => 'bg-success', 'group' => 'กลุ่มผู้นำ'];
        } elseif ($score >= 75) {
            return ['label' => 'ดี', 'badge' => 'bg-primary', 'group' => 'กลุ่มหัวหน้า'];
        } elseif ($score >= 60) {
            return ['label' => 'พอใช้', 'badge' => 'bg-warning text-dark', 'group' => 'กลุ่มมาตรฐาน'];
        } else {
            return ['label' => 'ต้องปรับปรุง', 'badge' => 'bg-danger', 'group' => 'กลุ่มต้องพัฒนา'];
        }
    }
    
    /**
     * ดึงข้อมูลกราฟพฤติกรรมจากฐานข้อมูลจริง
     */
    private function getBehaviorChartData($studentId)
    {
        if (!$studentId) {
            return [
                'labels' => ['ม.ค. 67', 'ก.พ. 67', 'มี.ค. 67', 'เม.ย. 67', 'พ.ค. 67', 'มิ.ย. 67'],
                'datasets' => [
                    [
                        'label' => 'คะแนนสะสม',
                        'data' => [100, 100, 100, 100, 100, 100],
                        'borderColor' => '#1020AD',
                        'backgroundColor' => 'rgba(16, 32, 173, 0.1)',
                        'tension' => 0.3
                    ]
                ]
            ];
        }
        
        try {
            $months = [];
            $scores = [];
            
            $currentScore = DB::table('tb_students')
                ->where('students_id', $studentId)
                ->value('students_current_score') ?? 100;
            
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->locale('th')->format('M Y');
                $scores[] = $currentScore;
            }
            
            return [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'คะแนนสะสม',
                        'data' => $scores,
                        'borderColor' => '#1020AD',
                        'backgroundColor' => 'rgba(16, 32, 173, 0.1)',
                        'tension' => 0.3
                    ]
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting chart data: ' . $e->getMessage());
            return [
                'labels' => ['ม.ค. 67', 'ก.พ. 67', 'มี.ค. 67', 'เม.ย. 67', 'พ.ค. 67', 'มิ.ย. 67'],
                'datasets' => [
                    [
                        'label' => 'คะแนนสะสม',
                        'data' => [100, 100, 100, 100, 100, 100],
                        'borderColor' => '#1020AD',
                        'backgroundColor' => 'rgba(16, 32, 173, 0.1)',
                        'tension' => 0.3
                    ]
                ]
            ];
        }
    }
}