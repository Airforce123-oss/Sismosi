<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SectionController;
use App\Models\Student;
use App\Models\Attendance;
use App\Http\Controllers\AttendanceController;
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
Route::get('/students/{id}', [StudentController::class, 'show']);

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

Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']); // Rute untuk mendapatkan detail enrollment

// Menambahkan rute untuk menyimpan enrollment
Route::post('/enrollments', [EnrollmentController::class, 'store']);


// Menambahkan rute untuk menyimpan marks
Route::post('/marks', [MarkController::class, 'store']);
Route::post('/tugas', [TugasController::class, 'store']);

Route::get('/teachers', [TeacherController::class, 'indexApi']);