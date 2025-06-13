<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'tb_teachers';
    protected $primaryKey = 'teachers_id';
    public $timestamps = false;

    protected $fillable = [
        'users_id',
        'teachers_employee_code',
        'teachers_position',
        'teachers_department',
        'teachers_major',
        'teachers_is_homeroom_teacher',
        'assigned_class_id'
    ];

    // Relationship กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }

    // Relationship กับ ClassRoom
    public function assignedClass()
    {
        return $this->belongsTo(ClassRoom::class, 'assigned_class_id', 'classes_id');
    }

    // Relationship กับ BehaviorReports
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'teacher_id', 'teachers_id');
    }
}