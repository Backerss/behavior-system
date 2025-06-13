<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table = 'tb_classes';
    protected $primaryKey = 'classes_id';
    public $timestamps = false;

    protected $fillable = [
        'classes_level',
        'classes_room_number', 
        'classes_academic_year',
        'teachers_id'
    ];

    // Relationship กับ Teacher
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teachers_id', 'teachers_id');
    }

    // Relationship กับ Students
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'classes_id');
    }
}