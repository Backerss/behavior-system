<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\StudentController;

// หน้าหลัก
Route::get('/', function () {
    return view('welcome');
});

// เส้นทางสำหรับผู้ที่ไม่ได้เข้าสู่ระบบ
Route::middleware('guest')->group(function () {
    // หน้าลงทะเบียน
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    
    // หน้าเข้าสู่ระบบ
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// เส้นทางสำหรับผู้ที่เข้าสู่ระบบแล้ว
Route::middleware('auth')->group(function () {
    // ออกจากระบบ
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // หน้าแดชบอร์ดหลัก - จะเปลี่ยนเส้นทางตามบทบาท
    Route::get('/dashboard', function () {
        $user = Auth::user();
        switch ($user->users_role) {
            case 'student':
                return redirect()->route('student.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'guardian':
                return redirect()->route('guardian.dashboard');
            default:
                return redirect('/');
        }
    })->name('dashboard');
    
    // หน้าแดชบอร์ดของนักเรียน
    Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    
    // หน้าแดชบอร์ดของครู
    Route::get('/teacher/dashboard', [AuthController::class, 'dashboard'])->name('teacher.dashboard');
    
    // หน้าแดชบอร์ดของผู้ปกครอง
    Route::get('/guardian/dashboard', function () {
        return view('guardian.dashboard');
    })->name('guardian.dashboard');
});

// เพิ่ม API Routes สำหรับจัดการประเภทพฤติกรรม
Route::prefix('api/violations')->group(function () {
    Route::get('/', [ViolationController::class, 'index']);
    Route::get('/all', [ViolationController::class, 'getAll']);
    Route::post('/', [ViolationController::class, 'store']);
    Route::get('/{id}', [ViolationController::class, 'show']);
    Route::put('/{id}', [ViolationController::class, 'update']);
    Route::delete('/{id}', [ViolationController::class, 'destroy']);
});

// เพิ่ม API Routes สำหรับจัดการห้องเรียน
Route::prefix('api/classes')->group(function () {
    // สำคัญมาก - ย้าย route นี้ขึ้นมาก่อน เพื่อไม่ให้ขัดแย้งกับ route อื่น
    Route::get('/registration', [ClassroomController::class, 'getClassesForRegistration']);
    
    Route::get('/', [ClassroomController::class, 'index']);
    Route::post('/', [ClassroomController::class, 'store']);
    Route::get('/{id}', [ClassroomController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/{id}', [ClassroomController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/{id}', [ClassroomController::class, 'destroy'])->where('id', '[0-9]+');
    Route::get('/{id}/students', [ClassroomController::class, 'getStudents'])->where('id', '[0-9]+');
    
    // เพิ่ม routes ใหม่
    Route::get('/teachers/all', [ClassroomController::class, 'getAllTeachers']);
    Route::get('/filters/all', [ClassroomController::class, 'getFilters']);
    Route::get('/{id}/violations/stats', [ClassroomController::class, 'getViolationStatistics'])->where('id', '[0-9]+');
    Route::get('/{id}/export', [ClassroomController::class, 'exportClassReport'])->where('id', '[0-9]+');
});

// เพิ่ม route สำหรับการอัพเดตโปรไฟล์ผู้ใช้
Route::put('/teacher/profile/update', [App\Http\Controllers\TeacherController::class, 'updateProfile'])
     ->name('teacher.profile.update')
     ->middleware('auth');

// เพิ่ม API Routes สำหรับบันทึกพฤติกรรม
Route::prefix('api/behavior-reports')->middleware('auth')->group(function () {
    Route::post('/', [App\Http\Controllers\BehaviorReportController::class, 'store']);
    Route::get('/students/search', [App\Http\Controllers\BehaviorReportController::class, 'searchStudents']);
    Route::get('/recent', [App\Http\Controllers\BehaviorReportController::class, 'getRecentReports']);
    Route::get('/{id}', [App\Http\Controllers\BehaviorReportController::class, 'show']);
});
