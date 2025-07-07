<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // สร้างข้อมูลตัวอย่างจากไฟล์ SQL ตามลำดับ foreign key
        $this->call([
            UserSeeder::class,           // ต้องมาก่อนเพราะ teachers และ students อ้างอิง
            ViolationSeeder::class,      // ต้องมาก่อน behavior reports
            ClassroomSeeder::class,      // สร้างห้องเรียนก่อน (ไม่มี teachers_id ก่อน)
            TeacherSeeder::class,        // สร้างครูหลังจากมี users และ classes
            StudentSeeder::class,        // สร้างนักเรียนหลังจากมี users และ classes
            GuardianSeeder::class,       // สร้างผู้ปกครองหลังจากมี users
            GuardianStudentSeeder::class, // สร้างความเชื่อมโยงผู้ปกครอง-นักเรียน
            BehaviorReportSeeder::class, // สร้างรายงานพฤติกรรมสุดท้าย
        ]);
    }
}
