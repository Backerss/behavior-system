<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$app->boot();

use Illuminate\Support\Facades\DB;

echo "นักเรียนที่มีสถานะ graduated:\n";
echo "============================\n";

$students = DB::table('tb_students')
    ->where('students_status', 'graduated')
    ->get(['students_id', 'students_student_code', 'students_status', 'updated_at']);

if ($students->count() > 0) {
    foreach($students as $student) {
        echo "ID: {$student->students_id} | รหัส: {$student->students_student_code} | สถานะ: {$student->students_status} | อัปเดต: {$student->updated_at}\n";
    }
    echo "\nจำนวนทั้งหมด: " . $students->count() . " คน\n";
} else {
    echo "ไม่พบนักเรียนที่มีสถานะ graduated\n";
}

echo "\nตรวจสอบนักเรียนทั้งหมด:\n";
echo "=====================\n";
$allStudents = DB::table('tb_students')->get(['students_student_code', 'students_status']);
foreach($allStudents->take(10) as $student) {
    echo "รหัส: {$student->students_student_code} | สถานะ: " . ($student->students_status ?: 'null') . "\n";
}
echo "...(แสดง 10 คนแรก)\n";
