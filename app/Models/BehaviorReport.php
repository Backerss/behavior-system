<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

/**
 * BehaviorReport Model
 * 
 * จัดการข้อมูลรายงานพฤติกรรมนักเรียน
 * 
 * @property int $reports_id
 * @property int $student_id
 * @property int $teacher_id
 * @property int $violation_id
 * @property string $reports_description
 * @property string|null $reports_evidence_path
 * @property \Carbon\Carbon $reports_report_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class BehaviorReport extends Model
{
    use HasFactory;
    
    /**
     * ตารางที่เชื่อมโยง
     */
    protected $table = 'tb_behavior_reports';
    
    /**
     * ตารางนี้ไม่มีคอลัมน์ updated_at ในฐานข้อมูล จึงปิด timestamps
     */
    public $timestamps = false;
    
    /**
     * Primary key
     */
    protected $primaryKey = 'reports_id';
    
    /**
     * ฟิลด์ที่สามารถ mass assignment ได้
     */
    protected $fillable = [
        'student_id',
        'teacher_id',
        'violation_id',
        'reports_points_deducted',
        'reports_description',
        'reports_evidence_path',
        'reports_report_date'
    ];

    /**
     * Cast attributes ให้เป็น type ที่เหมาะสม
     */
    protected $casts = [
        'reports_report_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'student_id' => 'integer',
        'teacher_id' => 'integer',
        'violation_id' => 'integer',
        'reports_points_deducted' => 'integer',
    ];

    /**
     * ความสัมพันธ์กับ Student (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'students_id');
    }

    /**
     * ความสัมพันธ์กับ Teacher (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teachers_id');
    }

    /**
     * ความสัมพันธ์กับ Violation (Many-to-One)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function violation()
    {
        return $this->belongsTo(Violation::class, 'violation_id', 'violations_id');
    }

    /**
     * Accessor: รับ URL ของหลักฐาน
     * 
     * @return string|null
     */
    public function getEvidenceUrlAttribute(): ?string
    {
        if (!$this->reports_evidence_path) {
            return null;
        }
        
        return asset('storage/' . $this->reports_evidence_path);
    }

    /**
     * Accessor: วันที่รายงานแบบฟอร์แมตไทย
     * 
     * @return string
     */
    public function getFormattedReportDateAttribute(): string
    {
        return $this->reports_report_date->locale('th')->format('d M Y H:i');
    }

    /**
     * Scope: รายงานตามช่วงวันที่
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('reports_report_date', [$startDate, $endDate]);
    }

    /**
     * Scope: รายงานของเดือนปัจจุบัน
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCurrentMonth($query)
    {
        $now = Carbon::now();
        return $query->whereMonth('reports_report_date', $now->month)
                     ->whereYear('reports_report_date', $now->year);
    }

    /**
     * Scope: รายงานตามระดับความรุนแรง
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $severity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBySeverity($query, string $severity)
    {
        return $query->whereHas('violation', function ($q) use ($severity) {
            $q->where('violations_category', $severity);
        });
    }
}