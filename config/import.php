<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the behavior of data import operations
    |
    */

    'google_sheets' => [
        'max_execution_time' => env('IMPORT_MAX_EXECUTION_TIME', 300), // 5 minutes
        'memory_limit' => env('IMPORT_MEMORY_LIMIT', '512M'),
        'max_input_vars' => env('IMPORT_MAX_INPUT_VARS', 10000), // เพิ่มจาก 5000 เป็น 10000
        'max_input_nesting_level' => env('IMPORT_MAX_INPUT_NESTING_LEVEL', 64),
        'chunk_size' => env('IMPORT_CHUNK_SIZE', 15), // ลดเป็น 15 เพื่อลด input variables ต่อ request
        'chunk_delay' => env('IMPORT_CHUNK_DELAY', 100000), // Microseconds (0.1 second)
        'timeout' => env('IMPORT_TIMEOUT', 300), // AJAX timeout in seconds
    ],

    'validation' => [
        'required_fields' => [
            'students' => ['first_name', 'last_name'],
            'teachers' => ['first_name', 'last_name'],
            'guardians' => ['first_name', 'last_name'],
        ],
        'max_phone_length' => 15,
        'min_phone_length' => 9,
        'max_line_id_length' => 100,
    ],

    'defaults' => [
        'password' => '$2y$12$Yq98CXdMRT3w20RJM2vyYuyhS918XgHt2afpZKqQqrDYXJ5V447w.', // 123456789
        'student_score' => 100,
        'status' => 'active',
        'contact_method' => 'phone',
    ],
];
