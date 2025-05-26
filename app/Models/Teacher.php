<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    
    protected $table = 'tb_teachers';
    protected $primaryKey = 'teachers_id';
    public $timestamps = false;
    
    protected $fillable = [
        'users_id',  // ใช้ users_id แทน user_id
        'teachers_employee_code',
        'teachers_position',
        'teachers_department',
        'teachers_major',
        'teachers_is_homeroom_teacher',
        'assigned_class_id'
    ];

    protected $casts = [
        'teachers_is_homeroom_teacher' => 'boolean',
    ];

    // Set accessor properties
    public function getEmployeeCodeAttribute()
    {
        return $this->teachers_employee_code;
    }

    public function getPositionAttribute()
    {
        return $this->teachers_position;
    }

    public function getDepartmentAttribute()
    {
        return $this->teachers_department;
    }

    public function getMajorAttribute()
    {
        return $this->teachers_major;
    }

    public function getIsHomeroomTeacherAttribute()
    {
        return $this->teachers_is_homeroom_teacher;
    }

    // ความสัมพันธ์กับผู้ใช้
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }
    
    // ความสัมพันธ์กับห้องเรียน
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'assigned_class_id', 'classes_id');
    }
}