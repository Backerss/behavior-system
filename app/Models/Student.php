<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $table = 'tb_students';
    protected $primaryKey = 'students_id';
    public $timestamps = false;
    
    // กำหนดคอลัมน์ที่สามารถกำหนดค่าได้
    protected $fillable = [
        'user_id',  // เปลี่ยนจาก users_id เป็น user_id
        'students_student_code',
        'class_id',
        'students_academic_year',
        'students_current_score',
        'students_status',
        'students_gender'
    ];
    
    // กำหนดความสัมพันธ์กับผู้ใช้
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');  // เปลี่ยนจาก users_id เป็น user_id
    }
    
    // กำหนดความสัมพันธ์กับห้องเรียน
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'classes_id');
    }
    
    // กำหนดความสัมพันธ์กับบันทึกพฤติกรรม
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'student_id', 'students_id');
    }
    
    // คอนสแตนท์สำหรับชื่อคอลัมน์ timestamp
    const CREATED_AT = 'students_created_at';
    const UPDATED_AT = 'updated_at';
}