<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // ปิด PHP warnings และ notices ที่อาจรบกวน JSON response
        $oldErrorReporting = error_reporting(E_ERROR | E_PARSE);
        $oldDisplayErrors = ini_get('display_errors');
        ini_set('display_errors', 0);
        
        // เริ่มต้น output buffering
        if (!ob_get_level()) {
            ob_start();
        }
        
        $response = $next($request);
        
        // ล้าง output buffer หากมี content ที่ไม่ควรมี
        if (ob_get_level()) {
            $content = ob_get_contents();
            if (!empty($content) && $request->expectsJson()) {
                ob_clean();
            }
        }
        
        // คืนค่า error reporting เดิม
        error_reporting($oldErrorReporting);
        ini_set('display_errors', $oldDisplayErrors);
        
        return $response;
    }
}
