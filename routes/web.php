<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\AttendanceController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
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

// Home route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authentication routes
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Dashboard routes
Route::get('/dashboard', [ProfileController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/teachersDashboard', function () {
    return Inertia::render('teachersDashboard');
})->middleware(['auth', 'verified'])->name('teachersdashboard');

Route::get('/studentsDashboard', function () {
    return Inertia::render('studentsDashboard');
})->middleware(['auth', 'verified'])->name('studentsdashboard');

// Grouping routes that require authentication
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profil.eupdate');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource routes for students and teachers
    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);
    Route::get('/membuatTugasSiswa', [TeacherController::class, 'membuatTugasSiswa'])->name('membuatTugasSiswa');
    Route::get('/bukuPenghubung', [TeacherController::class, 'bukuPenghubung'])->name('bukuPenghubung');

    Route::get('/melihatDataAbsensiSiswa', [StudentController::class, 'melihatDataAbsensiSiswa'])->name('melihatDataAbsensiSiswa');

    // Custom routes for teachers and attendance
    Route::put('/attendances/{studentId}', [AttendanceController::class, 'update']);
    Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('/absensiSiswaTeacher', [TeacherController::class, 'absensiSiswa'])->name('teachersabsensiSiswa');
    Route::get('/absensiSiswa', [AttendanceController::class, 'absensiSiswa'])->name('studentsabsensiSiswa');
    Route::get('/AbsensiSiswaSatu', [AttendanceController::class, 'absensiSiswaSatu'])->name('studentsabsensiSiswaSatu');
    Route::get('/AbsensiSiswaDua', [AttendanceController::class, 'absensiSiswaDua'])->name('studentsabsensiSiswaDua');
    Route::get('/AbsensiSiswaTiga', [AttendanceController::class, 'absensiSiswaTiga'])->name('studentsabsensiSiswaTiga');
    Route::get('/AbsensiSiswaEmpat', [AttendanceController::class, 'absensiSiswaEmpat'])->name('studentsabsensiSiswaEmpat');
    Route::get('/AbsensiSiswaLima', [AttendanceController::class, 'absensiSiswaLima'])->name('studentsabsensiSiswaLima');
    Route::get('/AbsensiSiswaEnam', [AttendanceController::class, 'absensiSiswaEnam'])->name('studentsabsensiSiswaEnam');
    Route::get('/wali-kelas-profile', [StudentController::class, 'showWaliKelas']);
    Route::get('/bukuPenghubungDashboard', [TeacherController::class, 'bukuPenghubungDashboard'])->name('teacherbukuPenghubung');

    // Attendance management routes
    Route::get('/try1', [AttendanceController::class, 'try1']);
    Route::post('/api/attendances', [AttendanceController::class, 'store'])->name('apiattendancesstore');
    Route::post('/update/{id}/tanggal/{tanggal_kehadiran}', [AttendanceController::class, 'update'])->name('update');
    Route::get('/kelolaAbsensiSiswa', [AttendanceController::class, 'kelolaAbsensiSiswa'])->name('studentkelolaAbsensiSiswa');

    // Resource route for classes
    Route::resource('kelas', ClassController::class);
    //Route::get('/kelas', [ClassController::class, 'index'])->name('kelas');

    // API routes for various resources
    Route::get('/api/religions', [StudentController::class, 'getReligion']);
    Route::get('/api/students', [StudentController::class, 'indexApi']);
    Route::get('/api/attendances', [AttendanceController::class, 'indexApi1']);
    Route::get('/api/genders', [StudentController::class, 'getGender']);
    Route::get('/api/no_induks', [StudentController::class, 'getNoInduk']);

    // Resource routes for profile, tasks, and assessments
    Route::resource('/Profile', ProfileController::class);
    Route::get('/tugasTambah', [TugasController::class, 'tambahTugas'])->name('tugastambah');
    Route::resource('/penilaian', PenilaianController::class);
    Route::get('/mataPelajaran', [MataPelajaranController::class, 'mataPelajaran'])->name('matapelajaran.index');
    Route::get('/mataPelajaran/create', [MataPelajaranController::class, 'create'])->name('matapelajaran.create');
    Route::post('/mata-pelajaran/store', [MataPelajaranController::class, 'store'])->name('matapelajaran.store');
    Route::delete('/matapelajaran/{id_mapel}', [MataPelajaranController::class, 'destroy'])->name('matapelajaran.destroy');




});

// Admin routes with middleware for redirection
// Admin routes with middleware for redirection
Route::group(['prefix' => 'admin', 'middleware' => 'redirectAdmin'], function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.auth.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.auth.login.post');
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.auth.register');
    Route::post('register', [AdminAuthController::class, 'register'])->name('admin.auth.register.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
});



// Endpoint to fetch role types
Route::get('/role-types', [RoleTypeController::class, 'index']);

require __DIR__ . '/auth.php';
require __DIR__ . '/admin-auth.php';
