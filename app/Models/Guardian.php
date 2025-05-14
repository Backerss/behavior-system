<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;
    
    protected $table = 'tb_guardians';
    
    protected $fillable = [
        'user_id',
        'relationship_to_student',
        'phone',
        'email',
        'line_id',
        'preferred_contact_method',
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

    public function students()
    {
        return $this->belongsToMany(Student::class, 'tb_guardian_student', 'guardian_id', 'student_id')
                    ->withTimestamp('created_at');
    }
}