<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tb_users';

    protected $fillable = [
        'name_prefix',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'password',
        'role',
        'profile_image',
        'birthdate',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'birthdate' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // เพิ่มบรรทัดนี้เพื่อบอก Laravel ว่า model นี้ไม่ใช้ remember_token
    public $rememberTokenName = false;

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'user_id');
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
}
