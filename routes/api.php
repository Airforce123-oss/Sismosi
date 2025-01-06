<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SectionController;
use App\Models\Student;
use App\Models\Attendance;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BukuPenghubung1Controller; 
use App\Models\Mapel;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MataPelajaranController; // Pastikan controller ini diimport
use App\Http\Controllers\TugasController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\MarkController; // Import MarkController

// Authentication routes for API
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/refresh-token', [AuthController::class, 'refreshToken']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/attendance', [AttendanceController::class, 'indexApi1']);
Route::middleware('auth:sanctum')->put('/attendance/{studentId}', [AttendanceController::class, 'update']);

Route::get('sections', SectionController::class)->name('sections.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance1', [AttendanceController::class, 'indexApi1']);
    Route::get('/attendance2', [AttendanceController::class, 'getAttendances']);
    Route::post('/attendance3', [AttendanceController::class, 'store']);
});

Route::get('/students/count', function () {
    $totalSiswa = Student::count();
    return response()->json(['total' => $totalSiswa]);
});

Route::get('/mapel/count', function () {
    $totalMapel = Mapel::count();
    return response()->json(['total' => $totalMapel]);
});

Route::get('/session-name', function () {
    return response()->json(['name' => Session::get('name')]);
});

Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getUser']);

// Tambahkan rute ini untuk courses
Route::get('/courses', [MataPelajaranController::class, 'apiCourses']);

// Tanpa autentikasi
//Route::get('/enrollments', [EnrollmentController::class, 'getEnrollments']);

Route::get('/enrollments', [EnrollmentController::class, 'getPaginatedEnrollments']);

Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']); 
//Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);

// Menambahkan rute untuk menyimpan enrollment
Route::post('/enrollments', [EnrollmentController::class, 'store']);

// Menambahkan rute untuk menyimpan marks
Route::post('/marks', [MarkController::class, 'store']);
Route::get('/tugas', [TugasController::class, 'index']);
Route::post('/tugas', [TugasController::class, 'store']);
Route::get('/teachers', [TeacherController::class, 'indexApi']);
//Route::get('/teachers', [TeacherController::class, 'index']);

//Route::middleware('auth:sanctum')->put('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);

// Route untuk Buku Penghubung (API)
Route::get('/buku-penghubung', [TeacherController::class, 'bukuPenghubungApi']);
Route::post('/buku-penghubung', [TeacherController::class, 'storeBukuPenghubung']);

// Route untuk detail siswa di TeacherController (API)
// Rute untuk TeacherController, hanya bisa diakses oleh guru
Route::middleware('role:teacher')->get('/students/{id}', [TeacherController::class, 'showStudent']);

// Rute untuk StudentController, hanya bisa diakses oleh siswa
Route::middleware('role:student')->get('/students/{id}', [StudentController::class, 'show']);


Route::post('/store-teacher', [TeacherController::class, 'store']);
Route::post('/students', [StudentController::class, 'storeStudent']);
Route::get('/detailstudents', [StudentController::class, 'indexApiDetailStudent']);
// Menambahkan rute untuk update detail siswa
Route::put('/detailstudents/{id}/detail', [TeacherController::class, 'updateStudentDetail']);

Route::get('/buku-penghubung', [BukuPenghubung1Controller::class, 'index']);

Route::get('/class-by-teacher', [TeacherController::class, 'getClassByTeacher']);

// Menambahkan route /dashboard yang hanya bisa diakses oleh role tertentu
Route::middleware('role')->get('/dashboard', function () {
    return response()->json(['message' => 'Welcome to the dashboard!']);
});
