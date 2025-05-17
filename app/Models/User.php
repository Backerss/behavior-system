<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'tb_users';
    protected $primaryKey = 'users_id';

    protected $fillable = [
        'users_name_prefix',
        'users_first_name',
        'users_last_name',
        'users_email',
        'users_phone_number',
        'users_password',
        'users_role',
        'users_profile_image',
        'users_birthdate',
        'users_created_at',
        'users_updated_at'
    ];

    protected $hidden = [
        'users_password',
    ];

    protected $casts = [
        'users_birthdate' => 'date',
        'users_created_at' => 'datetime',
        'users_updated_at' => 'datetime',
    ];

    // เพิ่มบรรทัดนี้เพื่อบอก Laravel ว่า model นี้ไม่ใช้ remember_token
    public $rememberTokenName = false;

    public function getAuthPassword()
    {
        return $this->users_password;
    }

    // Set accessor properties to work with database prefix fields
    public function getNamePrefixAttribute()
    {
        return $this->users_name_prefix;
    }

    public function getFirstNameAttribute()
    {
        return $this->users_first_name;
    }

    public function getLastNameAttribute()
    {
        return $this->users_last_name;
    }

    public function getEmailAttribute()
    {
        return $this->users_email;
    }

    public function getPhoneNumberAttribute()
    {
        return $this->users_phone_number;
    }

    public function getRoleAttribute()
    {
        return $this->users_role;
    }

    public function getBirthdateAttribute()
    {
        return $this->users_birthdate;
    }

    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'users_id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'users_id');
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class, 'user_id', 'users_id');
    }
    
    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id', 'users_id');
    }
}
