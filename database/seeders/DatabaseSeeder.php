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
        // สร้างข้อมูลพื้นฐานเท่านั้น (Admin user และ Violation types)
        // ข้อมูลอื่นๆ จะถูกนำเข้าจาก Google Sheets
        $this->call([
            UserSeeder::class,           // Admin user สำหรับเข้าถึงระบบ
            ViolationSeeder::class,      // ประเภทพฤติกรรมต่างๆ
        ]);
    }
}
