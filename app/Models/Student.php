<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'tb_students';
    protected $primaryKey = 'students_id';
    public $timestamps = false; // เพราะใช้ students_created_at
    
    protected $fillable = [
        'user_id',
        'students_student_code',
        'class_id',
        'students_academic_year',
        'students_current_score',
        'students_status',
        'students_gender'
    ];

    // Relationships - แก้ไข foreign key ให้ตรงกับโครงสร้างฐานข้อมูล
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'classes_id');
    }

    // แก้ไข relationship guardian เพื่อป้องกัน error
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'tb_guardian_student', 'student_id', 'guardian_id');
    }

    // สำหรับ guardian หลัก (ตัวแรก) - ป้องกัน null
    public function guardian()
    {
        return $this->guardians()->first();
    }

    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'student_id', 'students_id');
    }

    // เพิ่ม accessor สำหรับ user profile image ที่ปลอดภัย
    public function getUserProfileImageAttribute()
    {
        if ($this->user && $this->user->users_profile_image) {
            return $this->user->users_profile_image;
        }
        return null;
    }

    // เพิ่ม accessor สำหรับชื่อเต็มที่ปลอดภัย
    public function getFullNameAttribute()
    {
        if ($this->user) {
            return ($this->user->users_name_prefix ?? '') . 
                   ($this->user->users_first_name ?? '') . ' ' . 
                   ($this->user->users_last_name ?? '');
        }
        return 'ไม่มีข้อมูล';
    }
}