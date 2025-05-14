<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    
    protected $table = 'tb_teachers';
    protected $primaryKey = 'teachers_id';
    public $timestamps = false;
    
    protected $fillable = [
        'users_id',
        'teachers_full_name',
        'teachers_position',
        'teachers_created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }
}