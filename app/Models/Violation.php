<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;
    
    protected $table = 'tb_violations';
    
    protected $fillable = [
        'name',
        'description',
        'category',
        'points_deducted'
    ];
    
    public $timestamps = false;

    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'violation_id');
    }
}