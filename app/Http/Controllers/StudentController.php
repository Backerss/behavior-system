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

class StudentController extends Controller
{
    /**
     * แสดงหน้า Dashboard ของนักเรียน
     */
    public function dashboard()
    {
        // ดึงข้อมูลผู้ใช้ที่เข้าสู่ระบบ
        $user = Auth::user();
        
        // ดึงข้อมูลนักเรียน พร้อมความสัมพันธ์ห้องเรียน
        $student = Student::with(['classroom', 'classroom.teacher.user'])
            ->where('user_id', $user->users_id)
            ->first();
        
        // จัดเตรียมข้อมูลเพิ่มเติมสำหรับนักเรียน
        $data = [
            'user' => $user,
            'student' => $student,
            'stats' => [
                'current_score' => $student->students_current_score ?? 100,
                'class_rank' => $this->getStudentRank($student->students_id ?? 0),
                'total_students' => $this->getClassTotalStudents($student->class_id ?? 0),
                'rank_status' => $this->getRankStatus($student->students_current_score ?? 100)
            ],
            'recent_activities' => $this->getRecentActivities($student->students_id ?? 0),
            'chart_data' => $this->getBehaviorChartData($student->students_id ?? 0),
            // เพิ่มข้อมูลใหม่เกี่ยวกับนักเรียนจากฐานข้อมูลจริง
            'classroom_details' => $this->getClassroomDetails($student->class_id ?? 0),
            'behavior_summary' => $this->getBehaviorSummary($student->students_id ?? 0),
            'violation_distribution' => $this->getViolationDistribution($student->students_id ?? 0),
            'top_students' => $this->getTopStudentsInClass($student->class_id ?? 0, $student->students_id ?? 0),
            'notifications' => $this->getStudentNotifications($user->users_id ?? 0)
        ];
        
        return view('student.dashboard', $data);
    }
    
    /**
     * ดึงข้อมูลรายละเอียดของห้องเรียน
     */
    private function getClassroomDetails($classId)
    {
        if (!$classId) return null;
        
        $classroom = ClassRoom::with(['teacher.user'])
            ->find($classId);
        
        if (!$classroom) return null;
        
        $totalStudents = $this->getClassTotalStudents($classId);
        
        $highScore = Student::where('class_id', $classId)
            ->max('students_current_score') ?? 0;
        
        $avgScore = Student::where('class_id', $classId)
            ->avg('students_current_score') ?? 0;
        
        return [
            'name' => $classroom->classes_level . $classroom->classes_room_number,
            'academic_year' => $classroom->classes_academic_year,
            'teacher_name' => $classroom->teacher && $classroom->teacher->user ? 
                $classroom->teacher->user->users_name_prefix . $classroom->teacher->user->users_first_name . ' ' . $classroom->teacher->user->users_last_name
                : 'ไม่ระบุครูประจำชั้น',
            'total_students' => $totalStudents,
            'highest_score' => round($highScore),
            'average_score' => round($avgScore)
        ];
    }
    
    /**
     * ดึงข้อมูลสรุปพฤติกรรมของนักเรียน
     */
    private function getBehaviorSummary($studentId)
    {
        if (!$studentId) return null;
        
        // จำนวนรายงานทั้งหมด
        $totalReports = BehaviorReport::where('student_id', $studentId)->count();
        
        // จำนวนรายงานเชิงบวก (คะแนนเพิ่ม)
        $positiveReports = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('student_id', $studentId)
            ->where('violations_points_deducted', '<', 0) // คะแนนเชิงบวกคือค่าลบของ points_deducted
            ->count();
            
        // จำนวนรายงานเชิงลบ (คะแนนลด)
        $negativeReports = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('student_id', $studentId)
            ->where('violations_points_deducted', '>', 0) // คะแนนเชิงลบคือค่าบวกของ points_deducted
            ->count();
        
        // คะแนนสะสมที่ได้รับ (เฉพาะเชิงบวก)
        $totalPositivePoints = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('student_id', $studentId)
            ->where('violations_points_deducted', '<', 0)
            ->sum(DB::raw('ABS(violations_points_deducted)')); // ใช้ค่า absolute เพราะเป็นจำนวนลบ
            
        // คะแนนสะสมที่ถูกหัก (เฉพาะเชิงลบ)
        $totalNegativePoints = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('student_id', $studentId)
            ->where('violations_points_deducted', '>', 0)
            ->sum('violations_points_deducted');
        
        // คำนวณอัตราส่วนเชิงบวกต่อทั้งหมด (เปอร์เซ็นต์)
        $positiveRatio = $totalReports > 0 ? round(($positiveReports / $totalReports) * 100) : 0;
        
        return [
            'total_reports' => $totalReports,
            'positive_reports' => $positiveReports,
            'negative_reports' => $negativeReports,
            'total_positive_points' => $totalPositivePoints ?: 0,
            'total_negative_points' => $totalNegativePoints ?: 0,
            'positive_ratio' => $positiveRatio,
            'last_report_date' => BehaviorReport::where('student_id', $studentId)
                ->orderBy('reports_report_date', 'desc')
                ->value('reports_report_date')
        ];
    }
    
    /**
     * ดึงข้อมูลการกระจายของประเภทพฤติกรรม/ความผิด
     */
    private function getViolationDistribution($studentId)
    {
        if (!$studentId) return [
            'labels' => ['ไม่มีข้อมูล'],
            'data' => [1],
            'colors' => ['#EEEEEE'],
        ];
        
        // ดึงข้อมูลการกระจายประเภทความผิด
        $violationCounts = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('student_id', $studentId)
            ->select('violations_category', DB::raw('count(*) as count'))
            ->groupBy('violations_category')
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
            $label = '';
            $color = '';
            
            switch ($item->violations_category) {
                case 'light':
                    $label = 'เบา';
                    $color = '#75D701';
                    break;
                case 'medium':
                    $label = 'ปานกลาง';
                    $color = '#FFAA2B';
                    break;
                case 'severe':
                    $label = 'หนัก';
                    $color = '#FF5353';
                    break;
                default:
                    $label = 'อื่นๆ';
                    $color = '#AAAAAA';
            }
            
            $labels[] = $label;
            $data[] = $item->count;
            $colors[] = $color;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }
    
    /**
     * ดึงข้อมูลนักเรียนที่มีคะแนนสูงสุดในห้องเรียน
     */
    private function getTopStudentsInClass($classId, $currentStudentId, $limit = 5)
    {
        if (!$classId) return [];
        
        // ดึงนักเรียนที่มีคะแนนสูงสุด 5 คนแรก
        return Student::with(['user'])
            ->where('class_id', $classId)
            ->orderBy('students_current_score', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($student, $index) use ($currentStudentId) {
                return [
                    'rank' => $index + 1,
                    'name' => optional($student->user)->users_name_prefix .
                             optional($student->user)->users_first_name . ' ' . 
                             optional($student->user)->users_last_name,
                    'score' => $student->students_current_score,
                    'is_current' => $student->students_id == $currentStudentId
                ];
            });
    }
    
    /**
     * ดึงข้อมูลการแจ้งเตือนของนักเรียน
     */
    private function getStudentNotifications($userId, $limit = 5)
    {
        if (!$userId) return [];
        
        // ดึงข้อมูลการแจ้งเตือนจากตาราง tb_notifications
        return DB::table('tb_notifications')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'created_at' => Carbon::parse($notification->created_at)->locale('th')->format('d M Y'),
                    'is_read' => !is_null($notification->read_at)
                ];
            });
    }
    
    // เมธอดที่มีอยู่แล้วคงเดิม
    private function getStudentRank($studentId)
    {
        if (!$studentId) return 0;
        
        $student = Student::find($studentId);
        if (!$student) return 0;
        
        // นับจำนวนนักเรียนในห้องเรียนที่มีคะแนนมากกว่าหรือเท่ากับนักเรียนคนนี้
        $rank = Student::where('class_id', $student->class_id)
            ->where('students_current_score', '>=', $student->students_current_score)
            ->count();
            
        return $rank;
    }
    
    private function getClassTotalStudents($classId)
    {
        if (!$classId) return 0;
        
        return Student::where('class_id', $classId)->count();
    }
    
    private function getRankStatus($score)
    {
        if ($score >= 90) {
            return [
                'label' => 'ดีเยี่ยม',
                'badge' => 'bg-success',
                'group' => 'กลุ่มผู้นำ'
            ];
        } elseif ($score >= 75) {
            return [
                'label' => 'ดี',
                'badge' => 'bg-primary-app',
                'group' => 'กลุ่มหัวหน้า'
            ];
        } elseif ($score >= 60) {
            return [
                'label' => 'พอใช้',
                'badge' => 'bg-warning text-dark',
                'group' => 'กลุ่มมาตรฐาน'
            ];
        } else {
            return [
                'label' => 'ต้องปรับปรุง',
                'badge' => 'bg-danger',
                'group' => 'กลุ่มต้องพัฒนา'
            ];
        }
    }
    
    /**
     * ดึงประวัติกิจกรรมล่าสุดของนักเรียน
     */
    private function getRecentActivities($studentId)
    {
        if (!$studentId) return collect([]);
        
        return BehaviorReport::with(['violation', 'teacher.user'])
            ->where('student_id', $studentId)
            ->orderBy('reports_report_date', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($report) {
                // เปลี่ยนจาก violations_score เป็น violations_points_deducted
                $score = -1 * $report->violation->violations_points_deducted; // กลับเครื่องหมายเพราะ deducted เป็นค่าลบสำหรับคะแนนบวก
                $isPositive = $score > 0;
                
                return [
                    'id' => $report->reports_id,
                    'title' => $isPositive 
                        ? "ได้รับคะแนน +{$score} จาก{$report->violation->violations_name}" 
                        : "ถูกหักคะแนน " . abs($score) . " จาก{$report->violation->violations_name}",
                    'date' => Carbon::parse($report->reports_report_date)->locale('th')->format('d M Y'),
                    'teacher' => optional($report->teacher)->user 
                        ? "อ." . optional($report->teacher->user)->users_first_name
                        : "ระบบอัตโนมัติ",
                    'is_positive' => $isPositive,
                    'badge_color' => $isPositive ? 'bg-success' : 'bg-danger'
                ];
            });
    }
    
    /**
     * ดึงข้อมูลกราฟพฤติกรรม
     */
    private function getBehaviorChartData($studentId)
    {
        if (!$studentId) {
            return [
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
            ];
        }
        
        // ดึงข้อมูลคะแนนย้อนหลัง 6 เดือน
        $months = [];
        $scores = [];
        
        // สร้างข้อมูลจำลองสำหรับกราฟ (ข้อมูลย้อนหลัง 6 เดือน)
        $current = Carbon::now();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->locale('th')->format('M Y');
            
            // สมมติข้อมูลคะแนนเป็นค่าระหว่าง 70-100
            $score = 100 - rand(0, 30);
            $scores[] = $score;
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
    }
}