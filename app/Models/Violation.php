<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    use HasFactory;
    
    protected $table = 'tb_violations';
    protected $primaryKey = 'violations_id';
    public $timestamps = false;
    
    protected $fillable = [
        'violations_name',
        'violations_description',
        'violations_category',
        'violations_points_deducted'
    ];
    
    // Set accessor properties
    public function getNameAttribute()
    {
        return $this->violations_name;
    }

    public function getDescriptionAttribute()
    {
        return $this->violations_description;
    }

    public function getCategoryAttribute()
    {
        return $this->violations_category;
    }

    public function getPointsDeductedAttribute()
    {
        return $this->violations_points_deducted;
    }

    public function behaviorReports()
    {
        return $this->hasMany(BehaviorReport::class, 'violation_id', 'violations_id');
    }
}