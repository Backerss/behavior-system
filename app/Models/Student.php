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
    
    protected $fillable = [
        'user_id',
        'students_student_code',
        'class_id',
        'students_academic_year',
        'students_current_score',
        'students_status',
        'students_gender',
        'id_number', // เพิ่มฟิลด์นี้เข้าไป
    ];
    
    // ความสัมพันธ์กับผู้ใช้
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');
    }
    
    // ความสัมพันธ์กับห้องเรียน
    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'classes_id');
    }
    
    // ความสัมพันธ์กับผู้ปกครอง
    public function guardians()
    {
        return $this->belongsToMany(
            Guardian::class, 
            'tb_guardian_student', 
            'student_id', 
            'guardian_id', 
            'students_id', 
            'guardians_id'
        );
    }
    
    // ความสัมพันธ์กับรายงานพฤติกรรม
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'student_id', 'students_id');
    }
}