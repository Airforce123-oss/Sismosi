<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use App\Models\Student;
use Illuminate\Http\JsonResponse; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        $students = Student::select('id', 'name')->get();
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'students' => $students,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // Autentikasi user dulu
        $request->authenticate();
    
        // Regenerate session ID untuk keamanan
        $request->session()->regenerate();
    
        // Ambil user yang baru login
        $user = Auth::user();
    
        // Generate token baru
        $token = $user->createToken('spa-token')->plainTextToken;
    
        // Kembalikan response JSON berisi token dan data role
        return response()->json([
            'token' => $token,
            'role' => $user->role_name,
            'student_id' => $user->student_id ?? null, 
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
