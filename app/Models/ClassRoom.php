<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Classroom Model
 * 
 * จัดการข้อมูลห้องเรียน
 * 
 * @property int $classes_id
 * @property string $classes_level
 * @property string $classes_room_number
 * @property string $classes_academic_year
 * @property int $teachers_id
 */
class Classroom extends Model
{
    use HasFactory;
    
    /**
     * ตารางที่เชื่อมโยง
     */
    protected $table = 'tb_classes';
    
    /**
     * Primary key
     */
    protected $primaryKey = 'classes_id';
    
    /**
     * ปิดการใช้ timestamps อัตโนมัติ
     */
    public $timestamps = false;

    /**
     * ฟิลด์ที่สามารถ mass assignment ได้
     */
    protected $fillable = [
        'classes_level',
        'classes_room_number', 
        'classes_academic_year',
        'teachers_id'
    ];

    /**
     * Cast attributes ให้เป็น type ที่เหมาะสม
     */
    protected $casts = [
        'classes_id' => 'integer',
        'teachers_id' => 'integer',
    ];

    /**
     * ความสัมพันธ์กับ Teacher (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teachers_id', 'teachers_id');
    }

    /**
     * ความสัมพันธ์กับ Students (One-to-Many)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id', 'classes_id');
    }

    /**
     * Accessor: ชื่อห้องเรียนแบบเต็ม
     * 
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->classes_level . '/' . $this->classes_room_number;
    }

    /**
     * Scope: ค้นหาตามระดับชั้น
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('classes_level', $level);
    }

    /**
     * Scope: ค้นหาตามปีการศึกษา
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $academicYear
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAcademicYear($query, string $academicYear)
    {
        return $query->where('classes_academic_year', $academicYear);
    }
}