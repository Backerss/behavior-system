<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Academic Year and Semester Configuration
    |--------------------------------------------------------------------------
    |
    | กำหนดการตั้งค่าปีการศึกษาและภาคเรียน สำหรับระบบการเลื่อนชั้นอัตโนมัติ
    |
    */

    // ปีการศึกษาปัจจุบัน (จะถูกอัปเดตอัตโนมัติ)
    'current_academic_year' => env('ACADEMIC_YEAR', 2568),

    // ภาคเรียนปัจจุบัน (จะถูกอัปเดตอัตโนมัติ)
    'current_semester' => env('CURRENT_SEMESTER', 1),

    // การตั้งค่าช่วงเวลาภาคเรียน
    'semester_periods' => [
        // ภาคเรียนที่ 1: 16 พ.ค. - 31 ต.ค.
        '1' => [
            'name' => 'ภาคเรียนที่ 1',
            'start_month' => env('SEMESTER_1_START_MONTH', 5),    // พฤษภาคม
            'start_day' => env('SEMESTER_1_START_DAY', 16),       // วันที่ 16
            'end_month' => env('SEMESTER_1_END_MONTH', 10),       // ตุลาคม
            'end_day' => env('SEMESTER_1_END_DAY', 31),           // วันที่ 31
        ],
        
        // ภาคเรียนที่ 2: 1 พ.ย. - 15 พ.ค. ปีถัดไป
        '2' => [
            'name' => 'ภาคเรียนที่ 2',
            'start_month' => env('SEMESTER_2_START_MONTH', 11),   // พฤศจิกายน
            'start_day' => env('SEMESTER_2_START_DAY', 1),        // วันที่ 1
            'end_month' => env('SEMESTER_2_END_MONTH', 5),        // พฤษภาคม (ปีถัดไป)
            'end_day' => env('SEMESTER_2_END_DAY', 15),           // วันที่ 15
        ],
    ],

    // การตั้งค่าการเลื่อนชั้นอัตโนมัติ
    'auto_promotion' => [
        // เปิด/ปิด การเลื่อนชั้นอัตโนมัติ
        'enabled' => env('AUTO_PROMOTION_ENABLED', true),
        
        // วันที่เริ่มการเลื่อนชั้น (หลังจากสิ้นสุดภาคเรียนที่ 2)
        'promotion_date' => [
            'month' => env('PROMOTION_MONTH', 5),  // พฤษภาคม
            'day' => env('PROMOTION_DAY', 16),     // วันที่ 16 (วันแรกของภาคเรียนที่ 1 ปีใหม่)
        ],
        
        // เวลาในการรันการเลื่อนชั้น (24 ชั่วโมง)
        'promotion_time' => env('PROMOTION_TIME', '01:00'), // เวลา 01:00 น.
    ],

    // การตั้งค่าการแจ้งเตือน
    'notifications' => [
        // แจ้งเตือนเมื่อใกล้สิ้นสุดภาคเรียน (กี่วันก่อน)
        'semester_end_warning_days' => env('SEMESTER_END_WARNING_DAYS', 7),
        
        // แจ้งเตือนเมื่อใกล้เริ่มต้นภาคเรียนใหม่ (กี่วันก่อน)
        'semester_start_warning_days' => env('SEMESTER_START_WARNING_DAYS', 3),
        
        // แจ้งเตือนหลังการเลื่อนชั้นสำเร็จ
        'promotion_success_notification' => env('PROMOTION_SUCCESS_NOTIFICATION', true),
    ],

    // ข้อความแจ้งเตือนต่างๆ
    'messages' => [
        'current_academic_info' => 'ปีการศึกษา :year ภาคเรียนที่ :semester',
        'semester_end_warning' => 'ภาคเรียนที่ :semester จะสิ้นสุดในอีก :days วัน',
        'semester_start_warning' => 'ภาคเรียนที่ :semester จะเริ่มขึ้นในอีก :days วัน',
        'promotion_success' => 'การเลื่อนชั้นปีการศึกษา :year เสร็จสิ้นแล้ว จำนวน :count คน',
        'promotion_failed' => 'การเลื่อนชั้นปีการศึกษา :year ล้มเหลว: :error',
    ],
];
