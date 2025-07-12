<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SeedSampleData extends Command
{
    protected $signature = 'db:seed-sample';
    protected $description = 'Seed sample data for testing purposes';

    public function handle()
    {
        try {
            $this->info('กำลังเติมข้อมูลตัวอย่างในฐานข้อมูล...');
            
            // ตรวจสอบข้อมูลปัจจุบัน
            $userCount = DB::table('tb_users')->count();
            $studentCount = DB::table('tb_students')->count();
            $guardianCount = DB::table('tb_guardians')->count();
            $reportCount = DB::table('tb_behavior_reports')->count();
            
            $this->info("ข้อมูลปัจจุบัน: Users: {$userCount}, Students: {$studentCount}, Guardians: {$guardianCount}, Reports: {$reportCount}");
            
            // เติมข้อมูล Students ถ้าไม่มี
            if ($studentCount == 0) {
                $this->info('กำลังเพิ่มข้อมูลนักเรียน...');
                $studentUsers = DB::table('tb_users')->where('users_role', 'student')->get();
                $classes = DB::table('tb_classes')->get();
                
                foreach ($studentUsers as $user) {
                    $randomClass = $classes->random();
                    DB::table('tb_students')->insert([
                        'user_id' => $user->users_id,
                        'students_student_code' => 'STU' . str_pad($user->users_id, 4, '0', STR_PAD_LEFT),
                        'class_id' => $randomClass->classes_id,
                        'students_academic_year' => '2025',
                        'students_current_score' => rand(70, 100),
                        'students_status' => 'active',
                        'students_gender' => rand(0, 1) ? 'male' : 'female',
                        'students_created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                $this->info('✓ เพิ่มข้อมูลนักเรียนแล้ว: ' . $studentUsers->count() . ' รายการ');
            }
            
            // เติมข้อมูล Guardians ถ้าไม่มี
            if ($guardianCount == 0) {
                $this->info('กำลังเพิ่มข้อมูลผู้ปกครอง...');
                $guardianUsers = DB::table('tb_users')->where('users_role', 'guardian')->get();
                
                foreach ($guardianUsers as $user) {
                    DB::table('tb_guardians')->insert([
                        'user_id' => $user->users_id,
                        'guardians_relationship_to_student' => ['father', 'mother', 'guardian'][rand(0, 2)],
                        'guardians_phone' => $user->users_phone_number,
                        'guardians_email' => $user->users_email,
                        'guardians_line_id' => 'line_' . $user->users_id,
                        'guardians_preferred_contact_method' => ['phone', 'email', 'line'][rand(0, 2)],
                        'guardians_created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                $this->info('✓ เพิ่มข้อมูลผู้ปกครองแล้ว: ' . $guardianUsers->count() . ' รายการ');
            }
            
            // เติมข้อมูล Guardian-Student relationships
            $guardianStudentCount = DB::table('tb_guardian_student')->count();
            if ($guardianStudentCount == 0) {
                $this->info('กำลังเพิ่มความเชื่อมโยงผู้ปกครอง-นักเรียน...');
                $guardians = DB::table('tb_guardians')->get();
                $students = DB::table('tb_students')->get();
                
                // สุ่มจับคู่ผู้ปกครองกับนักเรียน (1-2 นักเรียนต่อผู้ปกครอง)
                foreach ($guardians as $guardian) {
                    $numStudents = rand(1, 2);
                    $randomStudents = $students->random(min($numStudents, $students->count()));
                    
                    foreach ($randomStudents as $student) {
                        DB::table('tb_guardian_student')->insert([
                            'guardian_id' => $guardian->guardians_id,
                            'student_id' => $student->students_id,
                            'guardian_student_created_at' => now()
                        ]);
                    }
                }
                $this->info('✓ เพิ่มความเชื่อมโยงผู้ปกครอง-นักเรียนแล้ว');
            }
            
            // เติมข้อมูล Behavior Reports ถ้าไม่มี
            if ($reportCount == 0) {
                $this->info('กำลังเพิ่มข้อมูลรายงานพฤติกรรม...');
                $students = DB::table('tb_students')->get();
                $teachers = DB::table('tb_teachers')->get();
                $violations = DB::table('tb_violations')->get();
                
                // สร้างรายงานพฤติกรรมตัวอย่าง 20-30 รายการ
                for ($i = 0; $i < rand(20, 30); $i++) {
                    $randomStudent = $students->random();
                    $randomTeacher = $teachers->random();
                    $randomViolation = $violations->random();
                    
                    $reportDate = Carbon::now()->subDays(rand(1, 90));
                    
                    DB::table('tb_behavior_reports')->insert([
                        'student_id' => $randomStudent->students_id,
                        'teacher_id' => $randomTeacher->teachers_id,
                        'violation_id' => $randomViolation->violations_id,
                        'reports_description' => 'รายงานพฤติกรรม: ' . $randomViolation->violations_name,
                        'reports_evidence_path' => null,
                        'reports_report_date' => $reportDate,
                        'created_at' => $reportDate
                    ]);
                }
                $this->info('✓ เพิ่มข้อมูลรายงานพฤติกรรมแล้ว: 20-30 รายการ');
            }
            
            // แสดงสถิติสุดท้าย
            $this->info('สถิติหลังจากเติมข้อมูล:');
            $this->info('- Users: ' . DB::table('tb_users')->count());
            $this->info('- Students: ' . DB::table('tb_students')->count());
            $this->info('- Teachers: ' . DB::table('tb_teachers')->count());
            $this->info('- Classes: ' . DB::table('tb_classes')->count());
            $this->info('- Guardians: ' . DB::table('tb_guardians')->count());
            $this->info('- Violations: ' . DB::table('tb_violations')->count());
            $this->info('- Behavior Reports: ' . DB::table('tb_behavior_reports')->count());
            
            $this->info('✅ เติมข้อมูลตัวอย่างเสร็จสิ้น!');
            
        } catch (\Exception $e) {
            $this->error('เกิดข้อผิดพลาด: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
