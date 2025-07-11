<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * BehaviorSystemException
 * 
 * Exception class สำหรับระบบจัดการพฤติกรรม
 */
class BehaviorSystemException extends Exception
{
    protected array $context;
    protected string $errorCode;

    public function __construct(
        string $message = "",
        string $errorCode = 'GENERAL_ERROR',
        array $context = [],
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    /**
     * ดึง error code
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * ดึง context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * แปลง exception เป็น JSON response
     */
    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
        ];

        // เพิ่ม context ถ้าอยู่ใน debug mode
        if (config('app.debug') && !empty($this->context)) {
            $response['context'] = $this->context;
            $response['trace'] = $this->getTraceAsString();
        }

        return response()->json($response, $this->getHttpStatusCode());
    }

    /**
     * กำหนด HTTP status code ตาม error code
     */
    protected function getHttpStatusCode(): int
    {
        return match ($this->errorCode) {
            'STUDENT_NOT_FOUND', 'TEACHER_NOT_FOUND', 'VIOLATION_NOT_FOUND' => 404,
            'UNAUTHORIZED_ACTION', 'INSUFFICIENT_PERMISSION' => 403,
            'INVALID_INPUT', 'VALIDATION_FAILED' => 422,
            'FILE_UPLOAD_FAILED', 'STORAGE_ERROR' => 500,
            default => 400
        };
    }
}

/**
 * StudentNotFoundException
 */
class StudentNotFoundException extends BehaviorSystemException
{
    public function __construct(int $studentId, array $context = [])
    {
        parent::__construct(
            "ไม่พบข้อมูลนักเรียน ID: {$studentId}",
            'STUDENT_NOT_FOUND',
            array_merge(['student_id' => $studentId], $context)
        );
    }
}

/**
 * TeacherNotFoundException
 */
class TeacherNotFoundException extends BehaviorSystemException
{
    public function __construct(int $teacherId, array $context = [])
    {
        parent::__construct(
            "ไม่พบข้อมูลครู ID: {$teacherId}",
            'TEACHER_NOT_FOUND',
            array_merge(['teacher_id' => $teacherId], $context)
        );
    }
}

/**
 * ViolationNotFoundException
 */
class ViolationNotFoundException extends BehaviorSystemException
{
    public function __construct(int $violationId, array $context = [])
    {
        parent::__construct(
            "ไม่พบข้อมูลประเภทการกระทำผิด ID: {$violationId}",
            'VIOLATION_NOT_FOUND',
            array_merge(['violation_id' => $violationId], $context)
        );
    }
}

/**
 * UnauthorizedActionException
 */
class UnauthorizedActionException extends BehaviorSystemException
{
    public function __construct(string $action, array $context = [])
    {
        parent::__construct(
            "คุณไม่มีสิทธิ์ในการ{$action}",
            'UNAUTHORIZED_ACTION',
            array_merge(['action' => $action], $context)
        );
    }
}

/**
 * FileUploadException
 */
class FileUploadException extends BehaviorSystemException
{
    public function __construct(string $reason, array $context = [])
    {
        parent::__construct(
            "การอัพโหลดไฟล์ล้มเหลว: {$reason}",
            'FILE_UPLOAD_FAILED',
            $context
        );
    }
}

/**
 * InvalidScoreException
 */
class InvalidScoreException extends BehaviorSystemException
{
    public function __construct(int $score, array $context = [])
    {
        parent::__construct(
            "คะแนนไม่ถูกต้อง: {$score} (ต้องอยู่ระหว่าง 0-100)",
            'INVALID_SCORE',
            array_merge(['score' => $score], $context)
        );
    }
}
