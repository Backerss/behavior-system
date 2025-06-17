<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\BehaviorReport;
use App\Models\ClassRoom;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ParentController extends Controller
{
    /**
     * แสดงหน้า Dashboard สำหรับผู้ปกครอง
     */
    public function dashboard()
    {
        $user = Auth::user();
        $studentsData = [];
        $notifications = collect();
        
        try {
            // ดึงข้อมูลผู้ปกครอง
            $guardian = Guardian::where('user_id', $user->users_id)->first();
            
            if ($guardian) {
                // ดึงข้อมูลนักเรียนที่ผู้ปกครองดูแล
                $students = Student::with(['user', 'classroom'])
                    ->whereExists(function($query) use ($guardian) {
                        $query->select(DB::raw(1))
                              ->from('tb_guardian_student')
                              ->whereRaw('tb_guardian_student.student_id = tb_students.students_id')
                              ->where('tb_guardian_student.guardian_id', $guardian->guardians_id);
                    })
                    ->orderBy('students_student_code')
                    ->get();
                
                // สร้างข้อมูลสำหรับแต่ละนักเรียน
                foreach ($students as $index => $student) {
                    $studentsData[] = $this->formatStudentData($student);
                }
                
                // เรียงข้อมูลตามคะแนน (คะแนนสูงก่อน)
                usort($studentsData, function($a, $b) {
                    return $b['current_score'] - $a['current_score'];
                });
                
                // ดึงการแจ้งเตือนล่าสุด
                $notifications = $this->getRecentNotifications($students->pluck('students_id')->toArray());
            }
            
        } catch (\Exception $e) {
            \Log::error('Error in parent dashboard: ' . $e->getMessage());
        }
        
        return view('parent.dashboard', compact('user', 'studentsData', 'notifications'));
    }
    
    /**
     * จัดรูปแบบข้อมูลนักเรียน
     */
    private function formatStudentData($student)
    {
        $currentScore = $student->students_current_score ?? 100;
        $weeklyChange = $this->getWeeklyScoreChange($student->students_id);
        $classRank = $this->getClassRank($student->students_id, $student->class_id);
        $homeRoomTeacher = $this->getHomeRoomTeacher($student->class_id);
        $recentActivities = $this->getRecentActivities($student->students_id);
        
        return [
            'id' => $student->students_id,
            'name_prefix' => $student->user->users_name_prefix ?? '',
            'first_name' => $student->user->users_first_name,
            'last_name' => $student->user->users_last_name,
            'student_code' => $student->students_student_code,
            'class_level' => $student->classroom->classes_level ?? 'ไม่ระบุ',
            'class_room' => $student->classroom->classes_room_number ?? 'ไม่ระบุ',
            'current_score' => $currentScore,
            'weekly_change' => $weeklyChange,
            'score_color' => $this->getScoreColor($currentScore),
            'score_status' => $this->getScoreStatus($currentScore),
            'change_direction' => $weeklyChange >= 0 ? 'up' : 'down',
            'change_color' => $weeklyChange >= 0 ? 'success' : 'danger',
            'class_rank' => $classRank['rank'],
            'total_students' => $classRank['total'],
            'homeroom_teacher' => $homeRoomTeacher,
            'recent_activities' => $recentActivities
        ];
    }
    
    /**
     * คำนวณการเปลี่ยนแปลงคะแนนในสัปดาห์นี้
     */
    private function getWeeklyScoreChange($studentId)
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        
        $weeklyChange = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('tb_behavior_reports.student_id', $studentId)
            ->where('tb_behavior_reports.reports_report_date', '>=', $startOfWeek)
            ->sum('tb_violations.violations_points_deducted');
            
        return (int) $weeklyChange;
    }
    
    /**
     * คำนวณอันดับในชั้น
     */
    private function getClassRank($studentId, $classId)
    {
        $classStudents = Student::where('class_id', $classId)
            ->orderBy('students_current_score', 'desc')
            ->get();
            
        $rank = 1;
        $total = $classStudents->count();
        
        foreach ($classStudents as $index => $student) {
            if ($student->students_id == $studentId) {
                $rank = $index + 1;
                break;
            }
        }
        
        return ['rank' => $rank, 'total' => $total];
    }
    
    /**
     * หาครูประจำชั้น
     */
    private function getHomeRoomTeacher($classId)
    {
        $class = ClassRoom::with(['teacher.user'])->find($classId);
        
        if ($class && $class->teacher && $class->teacher->user) {
            return [
                'name' => ($class->teacher->user->users_name_prefix ?? '') . 
                         $class->teacher->user->users_first_name . ' ' . 
                         $class->teacher->user->users_last_name,
                'phone' => $class->teacher->user->users_phone_number ?? '',
                'email' => $class->teacher->user->users_email ?? ''
            ];
        }
        
        return [
            'name' => 'ยังไม่ได้กำหนด',
            'phone' => '',
            'email' => ''
        ];
    }
    
    /**
     * ดึงกิจกรรมล่าสุดของนักเรียน
     */
    private function getRecentActivities($studentId)
    {
        $activities = BehaviorReport::with(['violation', 'teacher.user'])
            ->where('student_id', $studentId)
            ->orderBy('reports_report_date', 'desc')
            ->limit(5)
            ->get();
            
        $formattedActivities = [];
        
        foreach ($activities as $activity) {
            $points = abs($activity->violation->violations_points_deducted); // ใช้ค่าสัมบูรณ์เพื่อแสดงเป็นตัวเลขบวก
            $teacherName = ($activity->teacher->user->users_name_prefix ?? '') . 
                  $activity->teacher->user->users_first_name;
            
            $formattedActivities[] = [
            'type' => 'negative',
            'icon' => 'fas fa-minus',
            'color' => $points >= 5 ? 'danger' : 'warning',
            'message' => "ถูกหักคะแนน {$points} จาก {$activity->violation->violations_name}",
            'teacher' => $teacherName,
            'date' => Carbon::parse($activity->reports_report_date)->locale('th')->format('j M Y')
            ];
        }
        
        return $formattedActivities;
    }
    
    /**
     * กำหนดสีตามคะแนน
     */
    private function getScoreColor($score)
    {
        if ($score >= 90) return 'success';
        if ($score >= 80) return 'primary';
        if ($score >= 60) return 'warning';
        return 'danger';
    }
    
    /**
     * กำหนดสถานะตามคะแนน
     */
    private function getScoreStatus($score)
    {
        if ($score >= 90) return 'ดีเยี่ยม';
        if ($score >= 80) return 'ดีมาก';
        if ($score >= 70) return 'ดี';
        if ($score >= 60) return 'พอใช้';
        return 'ต้องปรับปรุง';
    }
    
    /**
     * ดึงการแจ้งเตือนล่าสุด
     */
    private function getRecentNotifications($studentIds)
    {
        if (empty($studentIds)) {
            return collect();
        }
        
        $recentReports = BehaviorReport::with(['student.user', 'violation', 'teacher.user'])
            ->whereIn('student_id', $studentIds)
            ->orderBy('reports_report_date', 'desc')
            ->limit(5)
            ->get();
            
        $notifications = collect();
        
        foreach ($recentReports as $report) {
            $points = abs($report->violation->violations_points_deducted); // ใช้ค่าสัมบูรณ์เพื่อแสดงเป็นตัวเลขบวก
            $studentName = ($report->student->user->users_name_prefix ?? '') . 
                  $report->student->user->users_first_name;
            
            $type = $points >= 5 ? 'danger' : 'warning';
            $icon = $points >= 5 ? 'fas fa-exclamation-triangle' : 'fas fa-minus-circle';
            $badgeClass = $points >= 5 ? 'bg-danger' : 'bg-warning text-dark';
            $badgeText = $points >= 5 ? 'ด่วน' : 'แจ้งเตือน';
            $message = "{$studentName} ถูกหักคะแนน {$points} คะแนน เนื่องจาก{$report->violation->violations_name}";
            
            $notifications->push([
            'type' => $type,
            'icon' => $icon,
            'message' => $message,
            'date' => Carbon::parse($report->reports_report_date)->locale('th')->diffForHumans(),
            'badge_class' => $badgeClass,
            'badge_text' => $badgeText,
            'student_name' => $studentName
            ]);
        }
        
        return $notifications;
    }
    
    /**
     * API: ดึงข้อมูลกราฟคะแนนของนักเรียน
     */
    public function getStudentScoreChart($studentId)
    {
        try {
            $student = Student::find($studentId);
            
            if (!$student) {
                return response()->json([
                    'error' => 'ไม่พบข้อมูลนักเรียน'
                ], 404);
            }
            
            $months = [];
            $scores = [];
            $currentScore = $student->students_current_score ?? 100;
            
            // สร้างข้อมูลกราฟ 6 เดือนย้อนหลัง
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->locale('th')->format('M');
                
                if ($i == 0) {
                    $scores[] = $currentScore;
                } else {
                    // คำนวณคะแนนจาก behavior reports
                    $monthlyDeduction = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                        ->where('tb_behavior_reports.student_id', $studentId)
                        ->whereYear('tb_behavior_reports.reports_report_date', $date->year)
                        ->whereMonth('tb_behavior_reports.reports_report_date', $date->month)
                        ->sum('tb_violations.violations_points_deducted');
                    
                    $estimatedScore = max(60, min(100, $currentScore + ($i * 2) - $monthlyDeduction));
                    $scores[] = $estimatedScore;
                }
            }
            
            return response()->json([
                'labels' => $months,
                'data' => $scores,
                'success' => true
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error generating student chart: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'เกิดข้อผิดพลาดในการสร้างกราฟ',
                'labels' => ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                'data' => [95, 92, 88, 90, 85, 100]
            ], 500);
        }
    }
}