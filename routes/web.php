<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\AttendanceController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\RoleTypeController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

Route::get('/dashboard', [ProfileController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/teachersDashboard', function () {
    return Inertia::render('teachersDashboard');
})->middleware(['auth', 'verified'])->name('teachers.dashboard');

Route::get('/studentsDashboard', function () {
    return Inertia::render('studentsDashboard');
})->middleware(['auth', 'verified'])->name('students.dashboard');


Route::middleware('auth:sanctum')->put('/api/attendances/{studentId}', [AttendanceController::class, 'update'])->name('api.attendances.update');



// Grouping routes that require authentication
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource routes for students and teachers
    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);

    // Custom routes for teachers and attendance
    Route::put('/attendances/{studentId}', [AttendanceController::class, 'update']);
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/absensiSiswaTeacher', [TeacherController::class, 'absensiSiswa'])->name('teachers.absensiSiswa');
    Route::get('/absensiSiswa', [AttendanceController::class, 'absensiSiswa'])->name('students.absensiSiswa');
    Route::get('/AbsensiSiswaSatu', [AttendanceController::class, 'absensiSiswaSatu'])->name('students.absensiSiswaSatu');
    Route::get('/wali-kelas-profile', [StudentController::class, 'showWaliKelas']);
    Route::get('/bukuPenghubungDashboard', [TeacherController::class, 'bukuPenghubungDashboard'])->name('teacher.bukuPenghubung');

    // Attendance management routes
    Route::get('/try1', [AttendanceController::class, 'try1']);
    Route::post('/api/attendances', [AttendanceController::class, 'store'])->name('api.attendances.store');
    Route::post('/update/{id}/tanggal/{tanggal_kehadiran}', [AttendanceController::class, 'update'])->name('update');
    Route::get('/kelolaAbsensiSiswa', [AttendanceController::class, 'kelolaAbsensiSiswa'])->name('student.kelolaAbsensiSiswa');

    // Resource route for classes
    Route::resource('kelas', ClassController::class);

    // API routes for various resourcese
    Route::get('/api/sections', [StudentController::class, 'getSections']);
    Route::get('/api/religions', [StudentController::class, 'getReligion']);
    Route::get('/api/students', [StudentController::class, 'indexApi']);
    Route::get('/api/attendances', [AttendanceController::class, 'indexApi1']);
    Route::get('/api/genders', [StudentController::class, 'getGender']);
    Route::get('/api/no_induks', [StudentController::class, 'getNoInduk']);

    // Resource routes for profile, tasks, and assessments
    Route::resource('/Profile', ProfileController::class);
    Route::get('/tugasTambah', [TugasController::class, 'tambahTugas'])->name('tugas.tambah');
    Route::resource('/penilaian', PenilaianController::class);
    Route::resource('matapelajaran', MataPelajaranController::class);
    Route::resource('master_mapel', MataPelajaranController::class);

    // MataPelajaran routes
    Route::get('/mataPelajaran', [MataPelajaranController::class, 'index']);
    Route::post('/mataPelajaran', [MataPelajaranController::class, 'store'])->name('mataPelajaran.store');
    Route::put('/mataPelajaran/{id}', [MataPelajaranController::class, 'update']);
    Route::delete('/mataPelajaran/{id}', [MataPelajaranController::class, 'destroy']);
});

// Admin routes with middleware for redirection
Route::group(['prefix' => 'admin', 'middleware' => 'redirectAdmin'], function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.register');
    Route::post('register', [AdminAuthController::class, 'register'])->name('admin.register.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
});

/*
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});
*/

// Endpoint to fetch role types
Route::get('/role-types', [RoleTypeController::class, 'index']);

require __DIR__ . '/auth.php';
require __DIR__ . '/admin-auth.php';

/*
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    /*
        if (Auth::check()) {
        $roleType = Auth::user()->role_type;

        switch ($roleType) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Teachers':
                return redirect()->route('teacher.dashboard');
            case 'Student':
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('dashboard');
        }
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
 */
