<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $table = 'tb_students';
    protected $primaryKey = 'students_id';
    
    protected $fillable = [
        'user_id', 
        'students_student_code',
        'class_id',
        'students_academic_year',
        'students_current_score',
        'students_status',
        'students_gender',
        'students_created_at',
        'updated_at'
    ];

    protected $casts = [
        'students_created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Set accessor properties
    public function getStudentCodeAttribute()
    {
        return $this->students_student_code;
    }

    public function getAcademicYearAttribute()
    {
        return $this->students_academic_year;
    }

    public function getCurrentScoreAttribute()
    {
        return $this->students_current_score;
    }

    public function getStatusAttribute()
    {
        return $this->students_status;
    }

    public function getGenderAttribute()
    {
        return $this->students_gender;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'classes_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'tb_guardian_student', 'student_id', 'guardian_id')
                    ->withPivot('guardian_student_created_at');
    }
    
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'student_id', 'students_id');
    }
}