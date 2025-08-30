<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
    if (!Auth::check() || Auth::user()->users_role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'ไม่มีสิทธิ์ในการเข้าถึง'
                ], 403);
            }
            
            return redirect()->route('login')->with('error', 'ไม่มีสิทธิ์ในการเข้าถึง');
        }

        return $next($request);
    }
}
