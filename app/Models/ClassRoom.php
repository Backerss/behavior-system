<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    use HasFactory;
    
    protected $table = 'tb_classes';
    
    protected $fillable = [
        'level',
        'room_number',
        'academic_year',
        'teacher_id'
    ];
    
    public $timestamps = false;

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
    
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}