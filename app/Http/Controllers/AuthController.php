<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\BehaviorReport;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }
    
    public function register(Request $request)
    {
        // กำหนดกฎพื้นฐานที่ต้องตรวจสอบทุก role
        $rules = [
            'role' => 'required|in:teacher,student,guardian',
            'name_prefix' => 'required|string|max:20',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:tb_users,users_email',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'birthdate' => 'required|date',
        ];
        
        // เพิ่มกฎเฉพาะตาม role
        switch ($request->role) {
            case 'teacher':
                $rules['employee_code'] = 'required|string|max:20|unique:tb_teachers,teachers_employee_code';
                $rules['position'] = 'nullable|string|max:50';
                $rules['department'] = 'nullable|string|max:100';
                $rules['major'] = 'nullable|string|max:100';
                break;
                
            case 'student':
                $rules['student_code'] = 'required|string|max:20|unique:tb_students,students_student_code';
                $rules['class_id'] = 'required|exists:tb_classes,classes_id';
                $rules['academic_year'] = 'required|string|max:10';
                $rules['gender'] = 'required|in:male,female,other';
                break;
                
            case 'guardian':
                $rules['relationship_to_student'] = 'required|string|max:50';
                $rules['student_code'] = 'required|string|max:20|exists:tb_students,students_student_code';
                $rules['line_id'] = 'nullable|string|max:100';
                $rules['preferred_contact_method'] = 'nullable|in:phone,email,line';
                break;
        }

        // ตรวจสอบข้อมูล
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            \Log::info('ข้อผิดพลาดในการลงทะเบียน:', $validator->errors()->toArray());
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // สร้างบัญชีผู้ใช้
            $user = new User();
            $user->users_name_prefix = $request->name_prefix;
            $user->users_first_name = $request->first_name;
            $user->users_last_name = $request->last_name;
            $user->users_email = $request->email;
            $user->users_phone_number = $request->phone_number;
            $user->users_password = Hash::make($request->password);
            $user->users_role = $request->role;
            $user->users_birthdate = $request->birthdate;
            $user->save();

            // สร้างข้อมูลตามประเภทของผู้ใช้
            switch ($request->role) {
                case 'teacher':
                    $teacher = new Teacher();
                    $teacher->users_id = $user->users_id; // เปลี่ยนจาก user_id เป็น users_id
                    $teacher->teachers_employee_code = $request->employee_code;
                    $teacher->teachers_position = $request->position ?? 'ครู';
                    $teacher->teachers_department = $request->department;
                    $teacher->teachers_major = $request->major;
                    $teacher->teachers_is_homeroom_teacher = false;
                    $teacher->save();
                    break;
                    
                case 'student':
                    $student = new Student();
                    $student->user_id = $user->users_id;
                    $student->students_student_code = $request->student_code;
                    $student->class_id = $request->class_id;
                    $student->students_academic_year = $request->academic_year;
                    $student->students_current_score = 100; // เริ่มต้นที่ 100 คะแนน
                    $student->students_status = 'active';
                    $student->students_gender = $request->gender;
                    $student->save();
                    break;
                    
                case 'guardian':
                    $guardian = new Guardian();
                    $guardian->user_id = $user->users_id;
                    $guardian->guardians_relationship_to_student = $request->relationship_to_student;
                    $guardian->guardians_phone = $request->phone_number;
                    $guardian->guardians_email = $request->email;
                    $guardian->guardians_line_id = $request->line_id;
                    $guardian->guardians_preferred_contact_method = $request->preferred_contact_method ?? 'phone';
                    $guardian->save();
                    
                    // เชื่อมโยงกับนักเรียน
                    $student = Student::where('students_student_code', $request->student_code)->first();
                    if ($student) {
                        \DB::table('tb_guardian_student')->insert([
                            'guardian_id' => $guardian->guardians_id,
                            'student_id' => $student->students_id,
                            'guardian_student_created_at' => now()
                        ]);
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('ข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['system_error' => 'เกิดข้อผิดพลาดในการสร้างบัญชี: ' . $e->getMessage()])
                ->withInput();
        }

        // เข้าสู่ระบบโดยอัตโนมัติ
        Auth::login($user);
        $request->session()->regenerate();
        
        // เปลี่ยนเส้นทางตามบทบาท (role)
        switch ($request->role) {
            case 'student':
                return redirect()->route('student.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'guardian':
                return redirect()->route('guardian.dashboard');
            default:
                return redirect()->intended('dashboard');
        }
    }

    public function login(Request $request)
    {
        // ตรวจสอบว่ามี role ที่ส่งมาหรือไม่
        if ($request->has('role')) {
            $role = $request->role;
            
            // กำหนดกฎการตรวจสอบตาม role
            $rules = [
                'password' => 'required',
            ];

            // เพิ่มกฎการตรวจสอบตาม role
            switch ($role) {
                case 'teacher':
                case 'student':
                    $rules['email'] = 'required|email';
                    break;
                    
                case 'guardian':
                    $rules['parent_phone'] = 'required';
                    $rules['student_code'] = 'required';
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
                case 'teacher':
                case 'student':
                    $credentials = [
                        'users_email' => $request->email, // แก้จาก email เป็น users_email
                        'users_password' => $request->password, // แก้จาก password เป็น users_password
                        'users_role' => $role // แก้จาก role เป็น users_role
                    ];
                    break;
                    
                case 'guardian':
                    // ค้นหา user ที่เป็น guardian จากหมายเลขโทรศัพท์และรหัสนักเรียน
                    $guardian = Guardian::where('guardians_phone', $request->parent_phone)->first(); // แก้จาก phone เป็น guardians_phone
                    if ($guardian) {
                        $user = User::find($guardian->user_id);
                        if ($user) {
                            // ตรวจสอบว่า guardian คนนี้เป็นผู้ปกครองของนักเรียนรหัสนี้จริงหรือไม่
                            $student = Student::where('students_student_code', $request->student_code)->first(); // แก้จาก student_code เป็น students_student_code
                            if ($student) {
                                $isGuardianOfStudent = \DB::table('tb_guardian_student')
                                    ->where('guardian_id', $guardian->guardians_id) // แก้จาก id เป็น guardians_id
                                    ->where('student_id', $student->students_id) // แก้จาก id เป็น students_id
                                    ->exists();
                                    
                                if ($isGuardianOfStudent) {
                                    // ใส่ข้อมูลให้ตรงกับ guardian
                                    $credentials = [
                                        'users_email' => $user->users_email, // แก้จาก email เป็น users_email
                                        'users_password' => $request->password, // แก้จาก password เป็น users_password
                                        'users_role' => 'guardian' // แก้จาก role เป็น users_role
                                    ];
                                }
                            }
                        }
                    }
                    break;
            }
            
            // เพิ่ม code สำหรับ debug ข้อมูล login
            \Log::info('Login attempt with credentials:', [
                'credentials' => $credentials,
                'request_data' => $request->all()
            ]);

            // พยายามเข้าสู่ระบบ แต่ไม่ใช้ remember me ด้วยชื่อฟิลด์ที่ถูกต้อง
            if (!empty($credentials)) {
                // ตรวจสอบการ login แบบ manual
                $user = User::where('users_email', $credentials['users_email'])
                            ->where('users_role', $credentials['users_role'])
                            ->first();
                
                if ($user && Hash::check($request->password, $user->users_password)) {
                    Auth::login($user, false);
                    $request->session()->regenerate();
                    return redirect()->intended('dashboard');
                }
            }
            
            // กรณีที่เข้าสู่ระบบไม่สำเร็จ
            if ($role === 'guardian') {
                return back()
                    ->withErrors([
                        'parent_phone' => 'ข้อมูลการเข้าสู่ระบบไม่ถูกต้อง หรือไม่พบความสัมพันธ์ระหว่างผู้ปกครองและนักเรียน',
                    ])
                    ->withInput();
            } else {
                return back()
                    ->withErrors([
                        'email' => 'ข้อมูลการเข้าสู่ระบบไม่ถูกต้อง',
                    ])
                    ->withInput();
            }
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
            
        // ดึงข้อมูลรายชื่อนักเรียน
        $students = Student::with(['user', 'classroom'])
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
        
        // ข้อมูลสำหรับกราฟแนวโน้ม 6 เดือน
        $trendData = $this->getViolationTrend();
        
        // ข้อมูลสำหรับกราฟประเภทพฤติกรรม
        $typesData = $this->getViolationTypes();
        
        return view('teacher.dashboard', compact(
            'user',
            'monthlyStats',
            'violationCategories',
            'categoryNames',
            'recentViolations',
            'classes',
            'students',
            'trendData',
            'typesData'
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