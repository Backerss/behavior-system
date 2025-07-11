<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CSRF Protection Middleware สำหรับ AJAX Requests
 */
class VerifyAjaxCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ตรวจสอบเฉพาะ AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            
            // ดึง CSRF token จาก header หรือ request
            $token = $request->header('X-CSRF-TOKEN') ?? 
                    $request->input('_token') ?? 
                    $request->header('X-XSRF-TOKEN');

            // ตรวจสอบ token
            if (!$token || !hash_equals(session()->token(), $token)) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'CSRF_TOKEN_MISMATCH',
                    'message' => 'CSRF token mismatch. กรุณาโหลดหน้าเว็บใหม่'
                ], 419);
            }
        }

        return $next($request);
    }
}
