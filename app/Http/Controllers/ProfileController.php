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

    public function dashboard(Request $request)
    {
        $user = auth()->user();

      if (!$user) {
        \Log::info('User null, redirect ke login.');
        return redirect()->route('login');
    }
        //$role = $user->role_name;
        $role = $user->roles->first()?->name;

        if (!$role) {
            \Log::warning('User ID ' . $user->id . ' tidak punya role. Role kosong.');
            // Tampilkan error page daripada logout
            return Inertia::render('ErrorPage', [
                'message' => 'Role tidak ditemukan. Silakan hubungi admin.',
            ]);
        }
    
        $selectedStudentId = $request->input('student_id', null);
    
        $totalStudents = cache()->remember('total_students', 60, function () {
            return Student::count();
        });
    
        switch ($role) {
            case 'admin':
                return Inertia::render('dashboard', [
                    'total' => $totalStudents,
                    'role_type' => $role,
                ]);
            case 'teacher':
                return Inertia::render('teachersDashboard', [
                    'total' => $totalStudents,
                    'role_type' => $role,
                ]);
            case 'student':
                $student = $selectedStudentId
                    ? Student::find($selectedStudentId)
                    : Student::where('user_id', $user->id)->first();
    
                if (!$student) {
                    return Inertia::render('ErrorPage', [
                        'message' => 'Student not found.',
                    ]);
                }
    
                return Inertia::render('studentsDashboard', [
                    'total' => $totalStudents,
                    'role_type' => $role,
                    'profileUrl' => route('profile.edit'),
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                ]);
            default:
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
