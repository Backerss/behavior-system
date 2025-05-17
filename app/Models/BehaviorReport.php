<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorReport extends Model
{
    use HasFactory;
    
    protected $table = 'tb_behavior_reports';
    protected $primaryKey = 'reports_id';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'violation_id',
        'reports_description',
        'reports_evidence_path',
        'reports_report_date',
        'created_at'
    ];

    protected $casts = [
        'reports_report_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Set accessor properties
    public function getDescriptionAttribute()
    {
        return $this->reports_description;
    }

    public function getEvidencePathAttribute()
    {
        return $this->reports_evidence_path;
    }

    public function getReportDateAttribute()
    {
        return $this->reports_report_date;
    }

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
    
    public function logs()
    {
        return $this->hasMany(BehaviorLog::class, 'behavior_report_id', 'reports_id');
    }
}