<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Teacher;
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

    // Tetap pakai query yang benar (gender_id 1 & 2)
    $malePerMonth = Student::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
        ->where('gender_id', '1')
        ->whereYear('created_at', now()->year)
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

    $femalePerMonth = Student::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
        ->where('gender_id', '2')
        ->whereYear('created_at', now()->year)
        ->groupBy('month')
        ->pluck('total', 'month')
        ->toArray();

    $maleData = [];
    $femaleData = [];
    for ($i = 1; $i <= 12; $i++) {
        $maleData[] = $malePerMonth[$i] ?? 0;
        $femaleData[] = $femalePerMonth[$i] ?? 0;
    }

    // Data tambahan untuk semua role
    $sharedData = [
        'total' => $totalStudents,
        'role_type' => $role,
        'totalMaleStudentsPerMonth' => $maleData,
        'totalFemaleStudentsPerMonth' => $femaleData,
    ];

    switch ($role) {
        case 'admin':
            $totalTeachers = cache()->remember('total_teachers', 60, fn () => Teacher::count());
            $totalMaleStudents = cache()->remember('total_male_students', 60, fn () => Student::where('gender', 'L')->count());
            $totalFemaleStudents = cache()->remember('total_female_students', 60, fn () => Student::where('gender', 'P')->count());

            return Inertia::render('dashboard', array_merge($sharedData, [
                'totalTeachers' => $totalTeachers,
                'totalMaleStudents' => $totalMaleStudents,
                'totalFemaleStudents' => $totalFemaleStudents,
            ]));

        case 'teacher':
            return Inertia::render('teachersDashboard', $sharedData);

        case 'student':
            $student = $user->student;

            if (!$student) {
                \Log::warning("Student tidak ditemukan untuk user ID {$user->id}");
                return Inertia::render('ErrorPage', [
                    'message' => 'Student tidak ditemukan.',
                ]);
            }

            $studentId = $student->id;
            $studentName = $student->name;
            $totalAbsensi = Attendance::where('student_id', $studentId)->count();

            return Inertia::render('studentsDashboard', array_merge($sharedData, [
                'profileUrl' => route('profile.edit'),
                'student_id' => $studentId,
                'student_name' => $studentName,
                'total_absensi' => $totalAbsensi,
                'auth' => ['user' => $user],
            ]));

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
           $request->user()->email_verified_at = now(); 
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
