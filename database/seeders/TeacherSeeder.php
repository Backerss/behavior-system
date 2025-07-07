<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = [
            ['teachers_id' => 1, 'users_id' => 22, 'teachers_employee_code' => 'T1001', 'teachers_position' => 'ครูชำนาญการพิเศษ', 'teachers_department' => 'คณิตศาสตร์', 'teachers_major' => 'คณิตศาสตร์', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 2, 'users_id' => 23, 'teachers_employee_code' => 'T1002', 'teachers_position' => 'ครูชำนาญการ', 'teachers_department' => 'ภาษาไทย', 'teachers_major' => 'ภาษาไทย', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 3, 'users_id' => 24, 'teachers_employee_code' => 'T1003', 'teachers_position' => 'ครูชำนาญการ', 'teachers_department' => 'วิทยาศาสตร์', 'teachers_major' => 'เคมี', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 4, 'users_id' => 25, 'teachers_employee_code' => 'T1004', 'teachers_position' => 'ครู', 'teachers_department' => 'ภาษาต่างประเทศ', 'teachers_major' => 'ภาษาอังกฤษ', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 5, 'users_id' => 26, 'teachers_employee_code' => 'T1005', 'teachers_position' => 'ครู', 'teachers_department' => 'สังคมศึกษา', 'teachers_major' => 'ประวัติศาสตร์', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 6, 'users_id' => 27, 'teachers_employee_code' => 'T1006', 'teachers_position' => 'ครู', 'teachers_department' => 'ศิลปะ', 'teachers_major' => 'ดนตรีไทย', 'teachers_is_homeroom_teacher' => 0, 'assigned_class_id' => null],
            ['teachers_id' => 7, 'users_id' => 28, 'teachers_employee_code' => 'T1007', 'teachers_position' => 'ครูชำนาญการ', 'teachers_department' => 'สุขศึกษาและพลศึกษา', 'teachers_major' => 'พลศึกษา', 'teachers_is_homeroom_teacher' => 0, 'assigned_class_id' => null],
            ['teachers_id' => 8, 'users_id' => 29, 'teachers_employee_code' => 'T1008', 'teachers_position' => 'ครู', 'teachers_department' => 'การงานอาชีพ', 'teachers_major' => 'คอมพิวเตอร์', 'teachers_is_homeroom_teacher' => 0, 'assigned_class_id' => null],
            ['teachers_id' => 9, 'users_id' => 30, 'teachers_employee_code' => 'T1009', 'teachers_position' => 'ครูชำนาญการ', 'teachers_department' => 'วิทยาศาสตร์', 'teachers_major' => 'ฟิสิกส์', 'teachers_is_homeroom_teacher' => 1, 'assigned_class_id' => null],
            ['teachers_id' => 10, 'users_id' => 31, 'teachers_employee_code' => 'T1010', 'teachers_position' => 'ครู', 'teachers_department' => 'ภาษาต่างประเทศ', 'teachers_major' => 'ภาษาจีน', 'teachers_is_homeroom_teacher' => 0, 'assigned_class_id' => null],
            ['teachers_id' => 11, 'users_id' => 32, 'teachers_employee_code' => 'T1011', 'teachers_position' => 'ครูชำนาญการ', 'teachers_department' => 'วิทยาศาสตร์', 'teachers_major' => 'วิทยาการคำนวณ', 'teachers_is_homeroom_teacher' => 0, 'assigned_class_id' => null],
        ];

        foreach ($teachers as $teacher) {
            Teacher::create($teacher);
        }
    }
}
