<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        BehaviorSystemException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Custom logging for behavior system exceptions
            if ($e instanceof BehaviorSystemException) {
                Log::warning('Behavior System Exception', [
                    'error_code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                    'context' => $e->getContext(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
            }
        });

        $this->renderable(function (Throwable $e, Request $request) {
            // Handle API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }

            // Handle web requests
            return $this->handleWebException($e, $request);
        });
    }

    /**
     * Handle exceptions for API requests
     */
    protected function handleApiException(Throwable $e, Request $request): JsonResponse
    {
        // Custom behavior system exceptions
        if ($e instanceof BehaviorSystemException) {
            return $e->render($request);
        }

        // Validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error_code' => 'VALIDATION_FAILED',
                'message' => 'ข้อมูลไม่ถูกต้อง',
                'errors' => $e->errors(),
            ], 422);
        }

        // Model not found exceptions
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error_code' => 'RESOURCE_NOT_FOUND',
                'message' => 'ไม่พบข้อมูลที่ระบุ',
            ], 404);
        }

        // Route not found exceptions
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'error_code' => 'ROUTE_NOT_FOUND',
                'message' => 'ไม่พบเส้นทางที่ระบุ',
            ], 404);
        }

        // Method not allowed exceptions
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'error_code' => 'METHOD_NOT_ALLOWED',
                'message' => 'วิธีการเรียกใช้ไม่ถูกต้อง',
            ], 405);
        }

        // General server errors
        $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = config('app.debug') ? $e->getMessage() : 'เกิดข้อผิดพลาดในระบบ';

        $response = [
            'success' => false,
            'error_code' => 'INTERNAL_SERVER_ERROR',
            'message' => $message,
        ];

        // Add debug information in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        return response()->json($response, $status);
    }

    /**
     * Handle exceptions for web requests
     */
    protected function handleWebException(Throwable $e, Request $request)
    {
        // Custom behavior system exceptions
        if ($e instanceof BehaviorSystemException) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()])
                ->withInput();
        }

        // Let Laravel handle other web exceptions
        return null;
    }
}