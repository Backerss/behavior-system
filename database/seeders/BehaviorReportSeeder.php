<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BehaviorReport;
use Illuminate\Support\Facades\DB;

class BehaviorReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reports = [
            // รายงานสำหรับนักเรียน ID 1
            ['reports_id' => 1, 'student_id' => 1, 'teacher_id' => 1, 'violation_id' => 1, 'reports_description' => 'มาสายในชั่วโมงเรียนแรกโดยไม่มีเหตุผลสมควร', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-01 08:30:00'],
            ['reports_id' => 2, 'student_id' => 1, 'teacher_id' => 2, 'violation_id' => 2, 'reports_description' => 'ไม่ส่งการบ้านวิชาคณิตศาสตร์', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-02 10:15:00'],
            
            // รายงานสำหรับนักเรียน ID 2
            ['reports_id' => 3, 'student_id' => 2, 'teacher_id' => 1, 'violation_id' => 3, 'reports_description' => 'แต่งกายไม่ถูกระเบียบ ไม่ใส่ใต้เสื้อ', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-01 07:45:00'],
            
            // รายงานสำหรับนักเรียน ID 3
            ['reports_id' => 4, 'student_id' => 3, 'teacher_id' => 3, 'violation_id' => 4, 'reports_description' => 'ใช้โทรศัพท์มือถือในเวลาเรียน', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-03 14:20:00'],
            ['reports_id' => 5, 'student_id' => 3, 'teacher_id' => 2, 'violation_id' => 1, 'reports_description' => 'มาสายติดต่อกัน 2 วัน', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-04 08:35:00'],
            
            // รายงานสำหรับนักเรียน ID 4
            ['reports_id' => 6, 'student_id' => 4, 'teacher_id' => 1, 'violation_id' => 5, 'reports_description' => 'พูดจาหยาบคายกับเพื่อน', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-02 11:30:00'],
            
            // รายงานสำหรับนักเรียน ID 5
            ['reports_id' => 7, 'student_id' => 5, 'teacher_id' => 2, 'violation_id' => 2, 'reports_description' => 'ไม่ส่งการบ้านวิชาภาษาไทย', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-05 09:15:00'],
            
            // รายงานล่าสุดสำหรับ test
            ['reports_id' => 8, 'student_id' => 1, 'teacher_id' => 3, 'violation_id' => 3, 'reports_description' => 'ไม่ใส่รองเท้านักเรียน', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-06 07:50:00'],
            ['reports_id' => 9, 'student_id' => 2, 'teacher_id' => 1, 'violation_id' => 1, 'reports_description' => 'มาสายอีกครั้ง', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-06 08:20:00'],
            ['reports_id' => 10, 'student_id' => 3, 'teacher_id' => 2, 'violation_id' => 2, 'reports_description' => 'ไม่ทำการบ้านวิชาวิทยาศาสตร์', 'reports_evidence_path' => null, 'reports_report_date' => '2025-07-07 10:00:00'],
        ];

        foreach ($reports as $report) {
            DB::table('tb_behavior_reports')->insert($report);
        }
    }
}
