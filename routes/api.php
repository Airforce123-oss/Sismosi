<?php

use Illuminate\Http\Request;
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
