<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BehaviorReport extends Model
{
    protected $table = 'tb_behavior_reports';
    protected $primaryKey = 'reports_id';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'violation_id',
        'reports_description',
        'reports_evidence_path',
        'reports_report_date'
    ];

    protected $casts = [
        'reports_report_date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'students_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teachers_id');
    }

    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id', 'violations_id');
    }
}