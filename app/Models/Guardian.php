<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;
    
    protected $table = 'tb_guardians';
    protected $primaryKey = 'guardians_id';
    
    // ปิดการใช้งาน Laravel timestamps เพราะใช้ custom timestamp columns
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'guardians_relationship_to_student',
        'guardians_phone',
        'guardians_email',
        'guardians_line_id',
        'guardians_preferred_contact_method',
        'guardians_created_at',
        'updated_at'
    ];

    protected $casts = [
        'guardians_created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Set accessor properties
    public function getRelationshipToStudentAttribute()
    {
        return $this->guardians_relationship_to_student;
    }

    public function getPhoneAttribute()
    {
        return $this->guardians_phone;
    }

    public function getEmailAttribute()
    {
        return $this->guardians_email;
    }

    public function getLineIdAttribute()
    {
        return $this->guardians_line_id;
    }

    public function getPreferredContactMethodAttribute()
    {
        return $this->guardians_preferred_contact_method;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'tb_guardian_student', 'guardian_id', 'student_id')
                    ->withPivot('guardian_student_created_at');
    }
}