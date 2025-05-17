<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorLog extends Model
{
    use HasFactory;
    
    protected $table = 'tb_behavior_logs';
    public $timestamps = false;
    
    protected $fillable = [
        'behavior_report_id',
        'action_type',
        'performed_by',
        'before_change',
        'after_change',
        'created_at'
    ];

    protected $casts = [
        'before_change' => 'array',
        'after_change' => 'array',
        'created_at' => 'datetime'
    ];

    public function behaviorReport()
    {
        return $this->belongsTo(BehaviorReport::class, 'behavior_report_id', 'reports_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by', 'users_id');
    }
}