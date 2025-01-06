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

    public function index()
    {
        return inertia('Profile/index');

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
    
        Log::info('User roles on dashboard access:', ['roles' => $roles]);

        $totalStudents = Student::count();

        // Tentukan role_type untuk frontend
        $roleType = $roles->first() ?? 'guest';
    
        // Cek berdasarkan peran dengan switch
        switch (true) {
            case $roles->contains('admin'):
                return Inertia::render('dashboard', [
                    'total' => $totalStudents, // Mengirimkan total siswa ke Vue
                    'role_type' => $roleType,
                ]);
            case $roles->contains('teacher'):
                return Inertia::render('teachersDashboard', [
                    'total' => $totalStudents, // Mengirimkan total siswa ke Vue
                    'role_type' => $roleType,
                ]);
            case $roles->contains('student'):
                return Inertia::render('studentsDashboard', [
                    'total' => $totalStudents, // Mengirimkan total siswa ke Vue
                    'role_type' => $roleType,
                ]);
            default:
                return redirect()->route('dashboard');
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
