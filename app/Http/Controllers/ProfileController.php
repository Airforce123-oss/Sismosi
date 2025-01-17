<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Student;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{

    public function index() {
        return inertia('Profile/index', [
            'user' => auth()->user(),
        ]);
    }
    
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
        
    }

    public function dashboard()
    {
        $user = auth()->user();
        $roles = $user->roles->pluck('name');
    
        // Logging hanya jika tidak ada peran atau user adalah admin
        if ($roles->isEmpty() || $roles->contains('admin')) {
            Log::info('User roles on dashboard access:', ['roles' => $roles]);
        }
    
        // Hitung total siswa dengan cache
        $totalStudents = cache()->remember('total_students', 60, function () {
            return Student::count();
        });
    
        // Tentukan role_type
        $roleType = $roles->first() ?? 'guest';
    
        switch (true) {
            case $roles->contains('admin'):
                return Inertia::render('dashboard', [
                    'total' => $totalStudents,
                    'role_type' => $roleType,
                ]);
            case $roles->contains('teacher'):
                return Inertia::render('teachersDashboard', [
                    'total' => $totalStudents,
                    'role_type' => $roleType,
                ]);
            case $roles->contains('student'):
                return Inertia::render('studentsDashboard', [
                    'total' => $totalStudents,
                    'role_type' => $roleType,
                ]);
            default:
                // Halaman error untuk pengguna tanpa peran
                return Inertia::render('ErrorPage', [
                    'message' => 'Access denied. Please contact the administrator.',
                ]);
        }
    }
    
    
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
