<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Teacher Model
 * 
 * จัดการข้อมูลครู
 * 
 * @property int $teachers_id
 * @property int $users_id
 * @property string|null $teachers_employee_code
 * @property string|null $teachers_position
 * @property string|null $teachers_department
 * @property string|null $teachers_major
 * @property bool $teachers_is_homeroom_teacher
 * @property int|null $assigned_class_id
 * 
 * @property-read User $user
 * @property-read Classroom|null $assignedClass
 * @property-read \Illuminate\Database\Eloquent\Collection<BehaviorReport> $behaviorReports
 */
class Teacher extends Model
{
    use HasFactory;
    
    /**
     * ตารางที่เชื่อมโยง
     */
    protected $table = 'tb_teachers';
    
    /**
     * Primary key
     */
    protected $primaryKey = 'teachers_id';
    
    /**
     * ปิดการใช้ timestamps อัตโนมัติ
     */
    public $timestamps = false;

    /**
     * ฟิลด์ที่สามารถ mass assignment ได้
     */
    protected $fillable = [
        'users_id',
        'teachers_employee_code',
        'teachers_position',
        'teachers_department',
        'teachers_major',
        'teachers_is_homeroom_teacher',
        'assigned_class_id'
    ];

    /**
     * Cast attributes ให้เป็น type ที่เหมาะสม
     */
    protected $casts = [
        'teachers_id' => 'integer',
        'users_id' => 'integer',
        'assigned_class_id' => 'integer',
        'teachers_is_homeroom_teacher' => 'boolean',
    ];

    /**
     * ความสัมพันธ์กับ User (Many-to-One)
     * 
     * @return BelongsTo<User, Teacher>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'users_id', 'users_id');
    }

    /**
     * ความสัมพันธ์กับ Classroom ที่รับผิดชอบ (Many-to-One)
     * 
     * @return BelongsTo<Classroom, Teacher>
     */
    public function assignedClass(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'assigned_class_id', 'classes_id');
    }

    /**
     * ความสัมพันธ์กับ BehaviorReports (One-to-Many)
     * 
     * @return HasMany<BehaviorReport>
     */
    public function behaviorReports(): HasMany
    {
        return $this->hasMany(BehaviorReport::class, 'teacher_id', 'teachers_id');
    }

    /**
     * Accessor: ชื่อเต็มของครู
     * 
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        if (!$this->user) {
            return 'ไม่มีข้อมูล';
        }
        
        $prefix = $this->user->users_name_prefix ?? '';
        $firstName = $this->user->users_first_name ?? '';
        $lastName = $this->user->users_last_name ?? '';
        
        return trim($prefix . $firstName . ' ' . $lastName) ?: 'ไม่มีข้อมูล';
    }

    /**
     * Accessor: ตำแหน่งและแผนก
     * 
     * @return string
     */
    public function getPositionWithDepartmentAttribute(): string
    {
        $parts = array_filter([
            $this->teachers_position,
            $this->teachers_department
        ]);
        
        return implode(' - ', $parts) ?: 'ไม่ระบุตำแหน่ง';
    }

    /**
     * Scope: ครูประจำชั้น
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeHomeroom(Builder $query): Builder
    {
        return $query->where('teachers_is_homeroom_teacher', true);
    }

    /**
     * Scope: ครูในแผนกที่ระบุ
     * 
     * @param Builder $query
     * @param string $department
     * @return Builder
     */
    public function scopeInDepartment(Builder $query, string $department): Builder
    {
        return $query->where('teachers_department', $department);
    }

    /**
     * Scope: ครูที่มีตำแหน่งที่ระบุ
     * 
     * @param Builder $query
     * @param string $position
     * @return Builder
     */
    public function scopeByPosition(Builder $query, string $position): Builder
    {
        return $query->where('teachers_position', $position);
    }

    /**
     * ตรวจสอบว่าเป็นครูประจำชั้นหรือไม่
     * 
     * @return bool
     */
    public function isHomeroomTeacher(): bool
    {
        return $this->teachers_is_homeroom_teacher === true;
    }

    /**
     * ตรวจสอบว่าเป็นผู้บริหารหรือไม่
     * 
     * @return bool
     */
    public function isAdministrator(): bool
    {
        $adminPositions = ['headmaster', 'deputy_headmaster', 'academic_head'];
        return in_array($this->teachers_position, $adminPositions);
    }

    /**
     * ดึงจำนวนรายงานพฤติกรรมที่บันทึกในเดือนปัจจุบัน
     * 
     * @return int
     */
    public function getCurrentMonthReportsCount(): int
    {
        return $this->behaviorReports()
                    ->whereMonth('reports_report_date', now()->month)
                    ->whereYear('reports_report_date', now()->year)
                    ->count();
    }
}