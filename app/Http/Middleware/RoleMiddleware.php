<?php

// app/Http/Middleware/RoleMiddleware.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        \Log::info('RoleMiddleware dijalankan pada URL: ' . $request->fullUrl());

        // Periksa apakah pengguna sudah login
        if (Auth::check()) {
            // Periksa apakah pengguna memiliki role yang benar
            if (Auth::user()->Role('admin')) {
                return $next($request);
            } else {
                // Arahkan ke halaman yang sesuai jika tidak memiliki role admin
                return redirect()->route('home')->with('error', 'Unauthorized');
            }
        }
        
        // Jika pengguna tidak terautentikasi, arahkan ke halaman login
        return redirect()->route('login');
    }
}
