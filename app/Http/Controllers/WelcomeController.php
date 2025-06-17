<?php
// filepath: c:\Users\AsanR\OneDrive\Desktop\วิจัยแก้ม\behavior-system\app\Http\Controllers\WelcomeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index()
    {
        // ดึงข้อมูลสถิติจากฐานข้อมูล
        $stats = [
            'total_students' => DB::table('tb_students')
                ->where('students_status', 'active')
                ->count(),
                
            'total_teachers' => DB::table('tb_teachers')->count(),
            
            'total_classes' => DB::table('tb_classes')->count(),
            
            'total_behavior_reports' => DB::table('tb_behavior_reports')->count(),
            
            'total_violations' => DB::table('tb_violations')->count(),
            
            'active_guardians' => DB::table('tb_guardians')->count(),
            
            // สถิติพฤติกรรมแยกตามประเภท
            'light_violations' => DB::table('tb_behavior_reports')
                ->join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                ->where('tb_violations.violations_category', 'light')
                ->count(),
                
            'medium_violations' => DB::table('tb_behavior_reports')
                ->join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                ->where('tb_violations.violations_category', 'medium')
                ->count(),
                
            'severe_violations' => DB::table('tb_behavior_reports')
                ->join('tb_violations', 'tb_behavior_reports.violation_id', '=', 'tb_violations.violations_id')
                ->where('tb_violations.violations_category', 'severe')
                ->count(),
                
            // สถิติเพิ่มเติม
            'reports_this_month' => DB::table('tb_behavior_reports')
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->count(),
                
            'average_score' => DB::table('tb_students')
                ->where('students_status', 'active')
                ->avg('students_current_score') ?? 100,
        ];

        return view('welcome', compact('stats'));
    }
}