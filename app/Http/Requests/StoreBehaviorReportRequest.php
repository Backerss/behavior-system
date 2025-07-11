<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreBehaviorReportRequest
 * 
 * จัดการการตรวจสอบข้อมูลสำหรับการบันทึกรายงานพฤติกรรม
 */
class StoreBehaviorReportRequest extends FormRequest
{
    /**
     * ตรวจสอบว่าผู้ใช้มีสิทธิ์ในการส่งคำขอนี้หรือไม่
     */
    public function authorize(): bool
    {
        // ตรวจสอบว่าเป็นครูหรือไม่
        return auth()->check() && auth()->user()->users_role === 'teacher';
    }

    /**
     * กฎการตรวจสอบข้อมูล
     */
    public function rules(): array
    {
        $rules = [
            'student_id' => [
                'required',
                'integer',
                'exists:tb_students,students_id'
            ],
            'violation_id' => [
                'required',
                'integer',
                'exists:tb_violations,violations_id'
            ],
            'violation_datetime' => [
                'required',
                'date_format:Y-m-d H:i',
                'before_or_equal:now'
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'evidence' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048' // 2MB
            ]
        ];

        // กฎเพิ่มเติมสำหรับการอัปเดต
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // ทำให้ฟิลด์เป็น optional สำหรับการอัปเดต
            $rules['student_id'][0] = 'sometimes';
            $rules['violation_id'][0] = 'sometimes';
            $rules['violation_datetime'][0] = 'sometimes';
        }

        return $rules;
    }

    /**
     * ข้อความข้อผิดพลาดที่กำหนดเอง
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'กรุณาเลือกนักเรียน',
            'student_id.exists' => 'ไม่พบข้อมูลนักเรียนที่เลือก',
            'violation_id.required' => 'กรุณาเลือกประเภทการกระทำผิด',
            'violation_id.exists' => 'ไม่พบข้อมูลประเภทการกระทำผิดที่เลือก',
            'violation_datetime.required' => 'กรุณาระบุวันที่และเวลา',
            'violation_datetime.date_format' => 'รูปแบบวันที่และเวลาไม่ถูกต้อง (ต้องเป็น Y-m-d H:i)',
            'violation_datetime.before_or_equal' => 'วันที่และเวลาไม่สามารถเป็นอนาคตได้',
            'description.max' => 'รายละเอียดไม่สามารถเกิน 1,000 ตัวอักษร',
            'evidence.image' => 'ไฟล์หลักฐานต้องเป็นรูปภาพเท่านั้น',
            'evidence.mimes' => 'ไฟล์หลักฐานต้องเป็นนามสกุล jpeg, png, jpg, gif, หรือ webp เท่านั้น',
            'evidence.max' => 'ไฟล์หลักฐานต้องมีขนาดไม่เกิน 2MB'
        ];
    }

    /**
     * เตรียมข้อมูลสำหรับการตรวจสอบ
     */
    protected function prepareForValidation(): void
    {
        // แปลงวันที่เป็นรูปแบบที่ถูกต้อง
        if ($this->has('violation_datetime')) {
            $this->merge([
                'violation_datetime' => $this->formatDateTime($this->violation_datetime)
            ]);
        }

        // ตรวจสอบและแปลง student_id เป็น integer
        if ($this->has('student_id')) {
            $this->merge([
                'student_id' => (int) $this->student_id
            ]);
        }

        // ตรวจสอบและแปลง violation_id เป็น integer
        if ($this->has('violation_id')) {
            $this->merge([
                'violation_id' => (int) $this->violation_id
            ]);
        }
    }

    /**
     * กำหนดชื่อ attributes สำหรับข้อความข้อผิดพลาด
     */
    public function attributes(): array
    {
        return [
            'student_id' => 'นักเรียน',
            'violation_id' => 'ประเภทการกระทำผิด',
            'violation_datetime' => 'วันที่และเวลา',
            'description' => 'รายละเอียด',
            'evidence' => 'หลักฐาน'
        ];
    }

    /**
     * จัดรูปแบบวันที่และเวลา
     */
    private function formatDateTime(string $datetime): string
    {
        try {
            // ลองแปลงรูปแบบต่างๆ
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d H:i',
                'd/m/Y H:i',
                'd-m-Y H:i'
            ];

            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $datetime);
                if ($date !== false) {
                    return $date->format('Y-m-d H:i');
                }
            }

            return $datetime; // คืนค่าเดิมถ้าแปลงไม่ได้
        } catch (\Exception $e) {
            return $datetime;
        }
    }

    /**
     * ข้อมูลที่ผ่านการตรวจสอบแล้วพร้อมการปรับแต่ง
     */
    public function validatedWithDefaults(): array
    {
        $validated = $this->validated();

        // ตั้งค่าเริ่มต้นสำหรับ description
        if (!isset($validated['description']) || empty($validated['description'])) {
            $validated['description'] = 'ไม่มีรายละเอียดเพิ่มเติม';
        }

        // ตั้งค่าเริ่มต้นสำหรับ violation_datetime
        if (!isset($validated['violation_datetime'])) {
            $validated['violation_datetime'] = now()->format('Y-m-d H:i');
        }

        return $validated;
    }
}
