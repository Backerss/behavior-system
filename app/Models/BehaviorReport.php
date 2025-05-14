<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorReport extends Model
{
    use HasFactory;
    
    protected $table = 'tb_behavior_reports';
    
    protected $fillable = [
        'student_id',
        'teacher_id',
        'violation_id',
        'description',
        'evidence_path',
        'report_date',
        'created_at'
    ];

    protected $casts = [
        'report_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    
    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id');
    }
    
    public function logs()
    {
        return $this->hasMany(BehaviorLog::class, 'behavior_report_id');
    }
}