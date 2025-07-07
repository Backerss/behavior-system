<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuardianStudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardianStudents = [
            // นักเรียน ID 1 มีทั้งพ่อและแม่
            ['guardian_student_id' => 1, 'guardian_id' => 1, 'student_id' => 1],
            ['guardian_student_id' => 2, 'guardian_id' => 2, 'student_id' => 1],
            // นักเรียน ID 2 มีผู้ปกครองคนเดียว
            ['guardian_student_id' => 3, 'guardian_id' => 3, 'student_id' => 2],
            // นักเรียน ID 3 มีผู้ปกครองคนเดียว
            ['guardian_student_id' => 4, 'guardian_id' => 4, 'student_id' => 3],
            // นักเรียน ID 4 มีทั้งพ่อและแม่
            ['guardian_student_id' => 5, 'guardian_id' => 5, 'student_id' => 4],
            ['guardian_student_id' => 6, 'guardian_id' => 6, 'student_id' => 4],
            // นักเรียน ID 5 มีผู้ปกครองคนเดียว
            ['guardian_student_id' => 7, 'guardian_id' => 1, 'student_id' => 5],
            // นักเรียน ID 6 มีผู้ปกครองคนเดียว
            ['guardian_student_id' => 8, 'guardian_id' => 2, 'student_id' => 6],
        ];

        foreach ($guardianStudents as $guardianStudent) {
            DB::table('tb_guardian_student')->insert($guardianStudent);
        }
    }
}
