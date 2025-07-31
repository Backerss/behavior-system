<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ExtendExecutionTime
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $time = 300)
    {
        // เพิ่ม execution time สำหรับ import operations
        set_time_limit(intval($time));
        ini_set('max_execution_time', intval($time));
        
        // เพิ่ม memory limit
        ini_set('memory_limit', '512M');
        
        return $next($request);
    }
}
