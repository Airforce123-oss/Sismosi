<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Student;
use App\Models\Attendance;
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

    $role = $user->roles->first()?->name;

    if (!$role) {
        \Log::warning('User ID ' . $user->id . ' tidak punya role.');
        return Inertia::render('ErrorPage', [
            'message' => 'Role tidak ditemukan. Silakan hubungi admin.',
        ]);
    }

    $totalStudents = cache()->remember('total_students', 60, fn () => Student::count());

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
            // ✅ Ambil data student via relasi yang sudah terbukti valid
            $student = $user->student;

            if (!$student) {
                \Log::warning("Student tidak ditemukan untuk user ID {$user->id}");
                return Inertia::render('ErrorPage', [
                    'message' => 'Student tidak ditemukan.',
                ]);
            }

            $studentId = $student->id;
            $studentName = $student->name;

            // ✅ Hitung total absensi siswa
            $totalAbsensi = Attendance::where('student_id', $studentId)->count();

            \Log::info('Mengirim data student ke frontend', [
                'student_id' => $studentId,
                'student_name' => $studentName,
                'total_absensi' => $totalAbsensi,
            ]);

            return Inertia::render('studentsDashboard', [
                'total' => $totalStudents,
                'role_type' => $role,
                'profileUrl' => route('profile.edit'),
                'student_id' => $studentId,
                'student_name' => $studentName,
                'total_absensi' => $totalAbsensi,
                'auth' => ['user' => $user],
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
