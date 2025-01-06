<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\AttendanceController;
use Inertia\Inertia;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\BukuPenghubung1Controller;
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


// Home Route
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Authentication Routes
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('/teachersDashboard', fn() => Inertia::render('teachersDashboard'))->name('teachersdashboard');
    Route::get('/studentsDashboard', fn() => Inertia::render('studentsDashboard'))->name('studentsdashboard');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource Routes
    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('kelas', ClassController::class);
    Route::get('/kelas/edit/{classId}', [ClassController::class, 'edit'])->name('kelas.edit');
    Route::resource('/Profile', ProfileController::class);
    Route::resource('/penilaian', PenilaianController::class);    
    Route::get('/membuatTugasSiswa', [TeacherController::class, 'membuatTugasSiswa'])->name('membuatTugasSiswa');
    Route::get('/bukuPenghubung', [TeacherController::class, 'bukuPenghubung'])->name('bukuPenghubung');
    Route::get('/bukuPenghubung1', [BukuPenghubung1Controller::class, 'index'])->name('bukuPenghubung1');
    Route::get('/bukuPenghubungDashboard', [TeacherController::class, 'bukuPenghubungDashboard'])->name('teacherbukuPenghubung');
    
    // Enrollment Routes
    Route::get('/enrollments/create', [EnrollmentController::class, 'membuatEnrollment'])->name('enrollments.create');
    Route::get('membuat-enrollment', [EnrollmentController::class, 'membuatEnrollment'])->name('teachermembuatEnrollment');

    // Attendance Routes
    Route::get('/absensiSiswaTeacher', [TeacherController::class, 'absensiSiswa'])->name('teachersabsensiSiswa');
    Route::get('/absensiSiswa', [AttendanceController::class, 'absensiSiswa'])->name('studentsabsensiSiswa');
    Route::put('/attendances/{studentId}', [AttendanceController::class, 'update']);
    Route::post('/api/attendances', [AttendanceController::class, 'store'])->name('apiattendancesstore');
    Route::post('/update/{id}/tanggal/{tanggal_kehadiran}', [AttendanceController::class, 'update'])->name('update');
    Route::get('/kelolaAbsensiSiswa', [AttendanceController::class, 'kelolaAbsensiSiswa'])->name('studentkelolaAbsensiSiswa');
    
    // Custom Attendance Views
    Route::get('/AbsensiSiswaSatu', [AttendanceController::class, 'absensiSiswaSatu'])->name('studentsabsensiSiswaSatu');
    Route::get('/AbsensiSiswaDua', [AttendanceController::class, 'absensiSiswaDua'])->name('studentsabsensiSiswaDua');
    Route::get('/AbsensiSiswaTiga', [AttendanceController::class, 'absensiSiswaTiga'])->name('studentsabsensiSiswaTiga');
    Route::get('/AbsensiSiswaEmpat', [AttendanceController::class, 'absensiSiswaEmpat'])->name('studentsabsensiSiswaEmpat');
    Route::get('/AbsensiSiswaLima', [AttendanceController::class, 'absensiSiswaLima'])->name('studentsabsensiSiswaLima');
    Route::get('/AbsensiSiswaEnam', [AttendanceController::class, 'absensiSiswaEnam'])->name('studentsabsensiSiswaEnam');
    
    // Other Routes
    Route::get('/tugasTambah', [TugasController::class, 'tambahTugas'])->name('tugastambah');
    Route::get('/mataPelajaran', [MataPelajaranController::class, 'mataPelajaran'])->name('matapelajaran.index');
    Route::get('/mataPelajaran/create', [MataPelajaranController::class, 'create'])->name('matapelajaran.create');
    Route::post('/mata-pelajaran/store', [MataPelajaranController::class, 'store'])->name('matapelajaran.store');
    Route::delete('/matapelajaran/{id}', [MataPelajaranController::class, 'destroy'])->name('matapelajaran.destroy');
    Route::get('/matapelajaran/{mapel}/edit', [MataPelajaranController::class, 'edit'])->name('matapelajaran.edit');

});

// Admin Routes (with middleware for redirection)
Route::group(['prefix' => 'admin', 'middleware' => 'redirectAdmin'], function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('admin.auth.login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('admin.auth.login.post');
    Route::get('register', [AdminAuthController::class, 'showRegisterForm'])->name('admin.auth.register');
    Route::post('register', [AdminAuthController::class, 'register'])->name('admin.auth.register.post');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('admin.auth.logout');
});

// Role Types Route
Route::get('/role-types', [RoleTypeController::class, 'index']);

// API Routes for Student, Gender, Religion, etc.
Route::get('/api/religions', [StudentController::class, 'getReligion']);
Route::get('/api/students', [StudentController::class, 'indexApi']);
Route::get('/api/genders', [StudentController::class, 'getGender']);
Route::get('/api/no_induks', [StudentController::class, 'getNoInduk']);

Route::middleware(['auth'])->group(function () {
    Route::get('/buku-penghubung', [BukuPenghubung1Controller::class, 'index']);
    Route::post('/buku-penghubung', [BukuPenghubung1Controller::class, 'store']);
    Route::put('/buku-penghubung/{bukuPenghubung1}', [BukuPenghubung1Controller::class, 'update']);
    Route::delete('/buku-penghubung/{bukuPenghubung1}', [BukuPenghubung1Controller::class, 'destroy']);
});

Route::post('/user/{userId}/assign-role', [UserController::class, 'assignRole']);
Route::put('/user/{user}/roles', [UserController::class, 'updateRoles'])->name('user.roles.update');



// Include Auth Routes
require __DIR__ . '/auth.php';
require __DIR__ . '/admin-auth.php';
