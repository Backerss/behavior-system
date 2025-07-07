<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            ['students_id' => 1, 'user_id' => 1, 'students_student_code' => '2001', 'class_id' => 1, 'students_academic_year' => '2568', 'students_current_score' => 90, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 2, 'user_id' => 2, 'students_student_code' => '2003', 'class_id' => 2, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 3, 'user_id' => 3, 'students_student_code' => '2004', 'class_id' => 3, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 4, 'user_id' => 4, 'students_student_code' => '2005', 'class_id' => 4, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 5, 'user_id' => 5, 'students_student_code' => '2006', 'class_id' => 5, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 6, 'user_id' => 6, 'students_student_code' => '2007', 'class_id' => 6, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 7, 'user_id' => 7, 'students_student_code' => '2008', 'class_id' => 1, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 8, 'user_id' => 8, 'students_student_code' => '2009', 'class_id' => 2, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 9, 'user_id' => 9, 'students_student_code' => '2010', 'class_id' => 3, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 10, 'user_id' => 10, 'students_student_code' => '2012', 'class_id' => 4, 'students_academic_year' => '2568', 'students_current_score' => 90, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 11, 'user_id' => 11, 'students_student_code' => '2013', 'class_id' => 5, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 12, 'user_id' => 12, 'students_student_code' => '2014', 'class_id' => 6, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 13, 'user_id' => 13, 'students_student_code' => '2015', 'class_id' => 1, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 14, 'user_id' => 14, 'students_student_code' => '2016', 'class_id' => 2, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 15, 'user_id' => 15, 'students_student_code' => '2018', 'class_id' => 3, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 16, 'user_id' => 16, 'students_student_code' => '2019', 'class_id' => 4, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'female'],
            ['students_id' => 17, 'user_id' => 17, 'students_student_code' => '2020', 'class_id' => 5, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 18, 'user_id' => 18, 'students_student_code' => '2022', 'class_id' => 6, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 19, 'user_id' => 19, 'students_student_code' => '2023', 'class_id' => 1, 'students_academic_year' => '2568', 'students_current_score' => 90, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 20, 'user_id' => 20, 'students_student_code' => '2026', 'class_id' => 2, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
            ['students_id' => 21, 'user_id' => 21, 'students_student_code' => '2029', 'class_id' => 3, 'students_academic_year' => '2568', 'students_current_score' => 100, 'students_status' => 'active', 'students_gender' => 'male'],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}
