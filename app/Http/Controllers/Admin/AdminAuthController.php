<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Admin/Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::guard('admin')->attempt($credentials)) {
            $admin = Auth::guard('admin')->user();
            Session::put('name', $admin->name);
    
            $roles = $admin->roles->pluck('name')->toArray();
    
            if (in_array('admin', $roles)) {
                return redirect()->route('admin.dashboard');
            } elseif (in_array('teacher', $roles)) {
                return redirect()->route('teachers.dashboard');
            } elseif (in_array('student', $roles)) {
                // ❌ Tidak perlu ambil students di sini
                return redirect()->route('students.dashboard');
            } else {
                return redirect()->route('home');
            }
        }
    
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    
    
    public function showRegisterForm()
    {
        return Inertia::render('Admin/Auth/Register');
    }

 public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:admins',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $admin = Admin::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role_type' => $request->role_type, // Save role_type
    ]);

    Auth::guard('admin')->login($admin);
    return redirect()->route('dashboard');
}
    public function logout(Request $request)
{
    Auth::guard('admin')->logout();
    return redirect()->route('login'); // Redirect ke halaman login admin
}
}
