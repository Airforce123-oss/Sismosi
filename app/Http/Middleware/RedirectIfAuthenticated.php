<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            // Cek apakah pengguna sudah terautentikasi
            if (Auth::guard($guard)->check()) {
                $user = Auth::user();

                // Pengalihan berdasarkan peran pengguna
                if ($user->role_name === 'student') {
                    return redirect()->route('student.dashboard');
                } elseif ($user->role_name === 'teacher') {
                    return redirect()->route('teacher.dashboard');
                } elseif ($user->role_name === 'admin') {
                    return redirect()->route('dashboard');  // Ganti dengan rute yang sesuai untuk admin
                }

                // Jika tidak ada role yang cocok, arahkan ke home
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
