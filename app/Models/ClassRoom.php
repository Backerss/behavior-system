<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;
    
    protected $table = 'tb_classes';
    protected $primaryKey = 'classes_id';
    public $timestamps = false;
    
    protected $fillable = [
        'classes_level',
        'classes_room_number',
        'classes_academic_year',
        'teacher_id'
    ];

    // Set accessor properties
    public function getLevelAttribute()
    {
        return $this->classes_level;
    }

    public function getRoomNumberAttribute()
    {
        return $this->classes_room_number;
    }

    public function getAcademicYearAttribute()
    {
        return $this->classes_academic_year;
    }
    
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teachers_id');
    }
    
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'classes_id');
    }
}