<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAttendance
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
        // Tambahkan logika validasi
        if (!$request->has('attendance_key')) {
            return response()->json(['error' => 'Attendance key missing'], 401);
        }

        return $next($request);
    }
}
