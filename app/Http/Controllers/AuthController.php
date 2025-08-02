<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\BehaviorReport;
use App\Models\Violation;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    
    public function login(Request $request)
    {
        // ตรวจสอบว่ามี role ที่ส่งมาหรือไม่
        if ($request->has('role')) {
            $role = $request->role;
            
            // กำหนดกฎการตรวจสอบตาม role
            $rules = [];

            // เพิ่มกฎการตรวจสอบตาม role
            switch ($role) {
                case 'admin':
                case 'teacher':
                case 'student':
                    $rules['email'] = 'required|email';
                    $rules['password'] = 'required';
                    break;
                    
                case 'guardian':
                    $rules['parent_phone'] = 'required|string';
                    break;
            }
            
            // ตรวจสอบความถูกต้องของข้อมูล
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            // สร้าง credentials ตาม role
            $credentials = [];
            
            switch ($role) {
                case 'admin':
                case 'teacher':
                case 'student':
                    $credentials = [
                        'users_email' => $request->email,
                        'users_password' => $request->password,
                        'users_role' => $role
                    ];
                    break;
                    
                case 'guardian':
                    // ค้นหา guardian จากเบอร์โทรศัพท์
                    $guardian = Guardian::where('guardians_phone', $request->parent_phone)->first();
                    
                    if ($guardian) {
                        $user = User::find($guardian->user_id);
                        if ($user && $user->users_role === 'guardian') {
                            // เข้าสู่ระบบโดยตรงสำหรับผู้ปกครอง (ไม่ต้องใช้รหัสผ่าน)
                            Auth::login($user, $request->has('remember'));
                            $request->session()->regenerate();
                            
                            // Log การเข้าสู่ระบบเพื่อความปลอดภัย
                            \Log::info('Guardian login successful', [
                                'guardian_id' => $guardian->guardians_id,
                                'phone' => $request->parent_phone,
                                'user_id' => $user->users_id
                            ]);
                            
                            // นำทางไปยัง parent dashboard
                            return redirect()->route('parent.dashboard');
                        }
                    }
                    
                    // ถ้าไม่พบข้อมูลผู้ปกครอง
                    return back()
                        ->withErrors([
                            'parent_phone' => 'ไม่พบข้อมูลผู้ปกครองที่ตรงกับเบอร์โทรศัพท์นี้',
                        ])
                        ->withInput();
            }
            
            // สำหรับ admin, teacher และ student ใช้การตรวจสอบแบบเดิม
            if (!empty($credentials)) {
                // ตรวจสอบการ login แบบ manual
                if ($role === 'teacher') {
                    // สำหรับ teacher form ให้ค้นหาทั้ง admin และ teacher
                    $user = User::where('users_email', $credentials['users_email'])
                                ->whereIn('users_role', ['admin', 'teacher'])
                                ->first();
                } else {
                    // สำหรับ role อื่นๆ ค้นหาตาม role ที่ระบุ
                    $user = User::where('users_email', $credentials['users_email'])
                                ->where('users_role', $credentials['users_role'])
                                ->first();
                }
                
                if ($user && Hash::check($request->password, $user->users_password)) {
                    Auth::login($user, $request->has('remember'));
                    $request->session()->regenerate();
                    
                    // สำหรับ admin และ teacher ให้ไปหน้า dashboard เดียวกัน
                    if ($user->users_role === 'admin' || $user->users_role === 'teacher') {
                        return redirect()->intended('teacher/dashboard');
                    } else {
                        return redirect()->intended('dashboard');
                    }
                }
            }
            
            // กรณีที่เข้าสู่ระบบไม่สำเร็จ
            return back()
                ->withErrors([
                    'email' => 'ข้อมูลการเข้าสู่ระบบไม่ถูกต้อง',
                ])
                ->withInput();
        }
        
        // ถ้าไม่ได้ระบุ role ให้แจ้งเตือน
        return back()
            ->withErrors([
                'role' => 'กรุณาเลือกประเภทผู้ใช้งาน',
            ])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * แสดงหน้า Dashboard
     */
    public function dashboard()
    {
        // ดึงข้อมูลผู้ใช้ที่เข้าสู่ระบบ
        $user = Auth::user();
        
        // ดึงข้อมูลสถิติประจำเดือน
        $monthlyStats = $this->getMonthlyStats();
        
        // ดึงข้อมูลประเภทพฤติกรรม
        $violationCategories = Violation::select('violations_category')
            ->distinct()
            ->pluck('violations_category')
            ->toArray();
            
        // แปลชื่อหมวดหมู่พฤติกรรม
        $categoryNames = [
            'light' => 'พฤติกรรมเบา',
            'medium' => 'พฤติกรรมปานกลาง',
            'severe' => 'พฤติกรรมรุนแรง'
        ];
        
        // ดึงข้อมูลพฤติกรรมล่าสุด
        $recentViolations = BehaviorReport::with([
                'student.user', 
                'student.classroom', 
                'violation',
                'teacher.user'
            ])
            ->orderBy('reports_report_date', 'desc')
            ->paginate(10);
            
        // แก้จาก Classroom เป็น ClassRoom
        $classes = ClassRoom::orderBy('classes_level')
            ->orderBy('classes_room_number')
            ->get();
            
        // ดึงข้อมูลรายชื่อนักเรียน (กรองเฉพาะสถานะ active)
        $students = Student::with(['user', 'classroom'])
            ->where('students_status', 'active') // กรองเฉพาะนักเรียนที่ยังคงเรียนอยู่
            ->when(request('search'), function($query) {
                $search = request('search');
                return $query->whereHas('user', function($subquery) use ($search) {
                    $subquery->where('users_first_name', 'like', "%{$search}%")
                        ->orWhere('users_last_name', 'like', "%{$search}%");
                })->orWhere('students_student_code', 'like', "%{$search}%");
            })
            ->when(request('class'), function($query) {
                return $query->where('class_id', request('class'));
            })
            ->paginate(15);
            
        // ดึงข้อมูลนักเรียนที่จบการศึกษา (สำหรับแฟ้มประวัติ)
        $graduatedStudents = Student::with(['user', 'classroom'])
            ->where('students_status', 'graduated')
            ->when(request('graduated_search'), function($query) {
                $search = request('graduated_search');
                return $query->whereHas('user', function($subquery) use ($search) {
                    $subquery->where('users_first_name', 'like', "%{$search}%")
                        ->orWhere('users_last_name', 'like', "%{$search}%");
                })->orWhere('students_student_code', 'like', "%{$search}%");
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'graduated_page');
        
        // ข้อมูลสำหรับกราฟแนวโน้ม 6 เดือน
        $trendData = $this->getViolationTrend();
        
        // ข้อมูลสำหรับกราฟประเภทพฤติกรรม
        $typesData = $this->getViolationTypes();
        
        // ข้อมูลปีการศึกษาและการแจ้งเตือน
        $academicService = app(AcademicYearService::class);
        $academicStatus = $academicService->getCachedAcademicStatus();
        $academicNotifications = $academicService->getCachedNotifications();
        
        return view('teacher.dashboard', compact(
            'user',
            'monthlyStats',
            'violationCategories',
            'categoryNames',
            'recentViolations',
            'classes',
            'students',
            'graduatedStudents',
            'trendData',
            'typesData',
            'academicStatus',
            'academicNotifications'
        ));
    }
    
    /**
     * ดึงข้อมูลสถิติประจำเดือน
     */
    private function getMonthlyStats()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $lastMonth = $now->copy()->subMonth();
        
        // จำนวนพฤติกรรมที่บันทึกเดือนนี้
        $currentMonthViolations = BehaviorReport::whereMonth('reports_report_date', $currentMonth)
            ->whereYear('reports_report_date', $currentYear)
            ->count();
            
        $lastMonthViolations = BehaviorReport::whereMonth('reports_report_date', $lastMonth->month)
            ->whereYear('reports_report_date', $lastMonth->year)
            ->count();
            
        $violationTrend = $lastMonthViolations > 0 
            ? round((($currentMonthViolations - $lastMonthViolations) / $lastMonthViolations) * 100) 
            : 0;
        
        // จำนวนนักเรียนที่ถูกบันทึกเดือนนี้
        $currentMonthStudents = BehaviorReport::whereMonth('reports_report_date', $currentMonth)
            ->whereYear('reports_report_date', $currentYear)
            ->distinct('student_id')
            ->count('student_id');
            
        $lastMonthStudents = BehaviorReport::whereMonth('reports_report_date', $lastMonth->month)
            ->whereYear('reports_report_date', $lastMonth->year)
            ->distinct('student_id')
            ->count('student_id');
            
        $studentsTrend = $lastMonthStudents > 0 
            ? round((($currentMonthStudents - $lastMonthStudents) / $lastMonthStudents) * 100) 
            : 0;
        
        // จำนวนพฤติกรรมรุนแรง
        $currentMonthSevere = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('tb_violations.violations_category', 'severe')
            ->whereMonth('reports_report_date', $currentMonth)
            ->whereYear('reports_report_date', $currentYear)
            ->count();
            
        $lastMonthSevere = BehaviorReport::join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->where('tb_violations.violations_category', 'severe')
            ->whereMonth('reports_report_date', $lastMonth->month)
            ->whereYear('reports_report_date', $lastMonth->year)
            ->count();
            
        $severeTrend = $lastMonthSevere > 0 
            ? round((($currentMonthSevere - $lastMonthSevere) / $lastMonthSevere) * 100) 
            : 0;
        
        // คะแนนเฉลี่ย
        $currentAvgScore = Student::avg('students_current_score');
        $lastMonthAvg = $currentAvgScore - rand(1, 5); // สมมติว่ามีข้อมูลย้อนหลัง
        $scoreTrend = round($currentAvgScore - $lastMonthAvg, 1);
        
        return [
            'violation_count' => $currentMonthViolations,
            'violation_trend' => $violationTrend,
            'students_count' => $currentMonthStudents,
            'students_trend' => $studentsTrend,
            'severe_count' => $currentMonthSevere,
            'severe_trend' => $severeTrend,
            'avg_score' => $currentAvgScore,
            'score_trend' => $scoreTrend
        ];
    }
    
    /**
     * ดึงข้อมูลสำหรับกราฟแนวโน้ม 6 เดือน
     */
    private function getViolationTrend()
    {
        $labels = [];
        $values = [];
        
        // สร้างข้อมูลย้อนหลัง 6 เดือน
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->locale('th')->format('M Y');
            
            $count = BehaviorReport::whereMonth('reports_report_date', $date->month)
                ->whereYear('reports_report_date', $date->year)
                ->count();
                
            $values[] = $count;
        }
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
    
    /**
     * ดึงข้อมูลสำหรับกราฟประเภทพฤติกรรม
     */
    private function getViolationTypes()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $types = DB::table('tb_behavior_reports')
            ->join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
            ->whereMonth('reports_report_date', $currentMonth)
            ->whereYear('reports_report_date', $currentYear)
            ->select('tb_violations.violations_name', DB::raw('count(*) as count'))
            ->groupBy('tb_violations.violations_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
            
        $labels = $types->pluck('violations_name')->toArray();
        $values = $types->pluck('count')->toArray();
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}