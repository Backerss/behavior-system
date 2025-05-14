<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    
    protected $table = 'tb_students';
    
    protected $fillable = [
        'user_id', 
        'student_code',
        'class_id',
        'academic_year',
        'current_score',
        'status',
        'gender',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function class()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id');
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'tb_guardian_student', 'student_id', 'guardian_id')
                    ->withTimestamp('created_at');
    }
    
    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'student_id');
    }
}