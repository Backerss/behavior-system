<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    
    protected $table = 'tb_notifications';
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
        'created_at'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'users_id');
    }
    
    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
        
        return $this;
    }
    
    public function isRead()
    {
        return $this->read_at !== null;
    }
}