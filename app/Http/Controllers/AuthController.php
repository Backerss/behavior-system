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
            'email' => 'required|email|max:150|unique:tb_users,email',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|min:8|confirmed',
            'birthdate' => 'required|date',
        ];
        
        // เพิ่มกฎเฉพาะตาม role
        switch ($request->role) {
            case 'teacher':
                $rules['employee_code'] = 'required|string|max:20|unique:tb_teachers,employee_code';
                $rules['position'] = 'nullable|string|max:50';
                $rules['department'] = 'nullable|string|max:100';
                $rules['major'] = 'nullable|string|max:100';
                break;
                
            case 'student':
                $rules['student_code'] = 'required|string|max:20|unique:tb_students,student_code';
                $rules['class_id'] = 'required|exists:tb_classes,id';
                $rules['academic_year'] = 'required|string|max:10';
                $rules['gender'] = 'required|in:male,female,other';
                break;
                
            case 'guardian':
                $rules['relationship_to_student'] = 'required|string|max:50';
                $rules['student_code'] = 'required|string|max:20|exists:tb_students,student_code';
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
            $user->name_prefix = $request->name_prefix;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone_number = $request->phone_number;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->birthdate = $request->birthdate;
            $user->save();

            // สร้างข้อมูลตามประเภทของผู้ใช้
            switch ($request->role) {
                case 'teacher':
                    $teacher = new Teacher();
                    $teacher->user_id = $user->id;
                    $teacher->employee_code = $request->employee_code;
                    $teacher->position = $request->position ?? 'ครู';
                    $teacher->department = $request->department;
                    $teacher->major = $request->major;
                    $teacher->is_homeroom_teacher = false;
                    $teacher->save();
                    break;
                    
                case 'student':
                    $student = new Student();
                    $student->user_id = $user->id;
                    $student->student_code = $request->student_code;
                    $student->class_id = $request->class_id;
                    $student->academic_year = $request->academic_year;
                    $student->current_score = 100; // เริ่มต้นที่ 100 คะแนน
                    $student->status = 'active';
                    $student->gender = $request->gender;
                    $student->save();
                    break;
                    
                case 'guardian':
                    $guardian = new Guardian();
                    $guardian->user_id = $user->id;
                    $guardian->relationship_to_student = $request->relationship_to_student;
                    $guardian->phone = $request->phone_number;
                    $guardian->email = $request->email;
                    $guardian->line_id = $request->line_id;
                    $guardian->preferred_contact_method = $request->preferred_contact_method ?? 'phone';
                    $guardian->save();
                    
                    // เชื่อมโยงกับนักเรียน
                    $student = Student::where('student_code', $request->student_code)->first();
                    if ($student) {
                        \DB::table('tb_guardian_student')->insert([
                            'guardian_id' => $guardian->id,
                            'student_id' => $student->id,
                            'created_at' => now()
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

        // เข้าสู่ระบบโดยอัตโนมัติและเปลี่ยนเส้นทาง
        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('dashboard');
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
                        'email' => $request->email,
                        'password' => $request->password,
                        'role' => $role
                    ];
                    break;
                    
                case 'guardian':
                    // ค้นหา user ที่เป็น guardian จากหมายเลขโทรศัพท์และรหัสนักเรียน
                    $guardian = Guardian::where('phone', $request->parent_phone)->first();
                    if ($guardian) {
                        $user = User::find($guardian->user_id);
                        if ($user) {
                            // ตรวจสอบว่า guardian คนนี้เป็นผู้ปกครองของนักเรียนรหัสนี้จริงหรือไม่
                            $student = Student::where('student_code', $request->student_code)->first();
                            if ($student) {
                                $isGuardianOfStudent = \DB::table('tb_guardian_student')
                                    ->where('guardian_id', $guardian->id)
                                    ->where('student_id', $student->id)
                                    ->exists();
                                    
                                if ($isGuardianOfStudent) {
                                    // ใส่ข้อมูลให้ตรงกับ guardian
                                    $credentials = [
                                        'email' => $user->email,
                                        'password' => $request->password,
                                        'role' => 'guardian'
                                    ];
                                }
                            }
                        }
                    }
                    break;
            }
            
            // พยายามเข้าสู่ระบบ แต่ไม่ใช้ remember me
            if (!empty($credentials) && Auth::attempt($credentials, false)) {
                $request->session()->regenerate();
                return redirect()->intended('dashboard');
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

    public function dashboard()
    {
        // ตรวจสอบประเภทผู้ใช้และเรียกใช้ dashboard ที่เหมาะสม
        $user = Auth::user();
        
        switch ($user->role) {
            case 'admin':
                return view('admin.dashboard');
                break;
                
            case 'teacher':
                return view('teacher.dashboard', ['user' => $user]);
                break;
                
            case 'student':
                return view('student.dashboard', ['user' => $user]);
                break;
                
            case 'guardian':
                return view('parent.dashboard', ['user' => $user]);
                break;
                
            default:
                return redirect('/');
                break;
        }
    }
}