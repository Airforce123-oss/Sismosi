<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SectionController;
use App\Models\Student;
use App\Models\AttendanceTeacher;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceTeacherController;
use App\Http\Controllers\BukuPenghubung1Controller; 
use App\Models\Mapel;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MataPelajaranController; 
use App\Http\Controllers\ClassController;
use App\Http\Controllers\TugasController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\MarkController; 

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
Route::get('/marks', [EnrollmentController::class, 'getMarks']);
Route::post('/enrollment/update', [EnrollmentController::class, 'updateEnrollment']);


Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']); 
//Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);

// Menambahkan rute untuk menyimpan enrollment
Route::post('/enrollments', [EnrollmentController::class, 'store']);

// Menambahkan rute untuk menyimpan marks
Route::post('/marks', [MarkController::class, 'store']);
Route::get('/tugas', [TugasController::class, 'index']);
Route::post('/tugas', [TugasController::class, 'store']);
Route::get('/teachers', [TeacherController::class, 'index']);

//Route::middleware('auth:sanctum')->put('/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);

// Route untuk Buku Penghubung (API)
Route::get('/buku-penghubung', [TeacherController::class, 'bukuPenghubungApi']);
Route::post('/buku-penghubung', [TeacherController::class, 'storeBukuPenghubung']);

// Route untuk detail siswa di TeacherController (API)
// Rute untuk TeacherController, hanya bisa diakses oleh guru
Route::middleware('role:teacher')->get('/students/{id}', [TeacherController::class, 'showStudent']);

// Rute untuk StudentController, hanya bisa diakses oleh siswa
Route::middleware('role:student')->get('/students/profile', [StudentController::class, 'show']);

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

Route::get('/attendance-teacher/{teacher_id}/{attendance_date}', [AttendanceTeacherController::class, 'show']);
Route::get('/attendance-teacher-detail/{teacher_id}/{attendance_date}', [AttendanceTeacherController::class, 'getAttendance']);
Route::post('/attendance', [AttendanceTeacherController::class, 'storeAttendance']);
Route::post('/attendances', [AttendanceController::class, 'store'])->name('attendance.store');
Route::get('/attendances', [AttendanceTeacherController::class, 'getAttendances']);
//Route::post('/attendance-teacher-create', [AttendanceTeacherController::class, 'store'])->name('attendance-teacher-create');
Route::get('/teachers/attendance', [AttendanceTeacherController::class, 'absensiGuru'])->name('teachers.attendance');
Route::post('/attendance-teacher-create', [AttendanceTeacherController::class, 'create']);
Route::post('/attendance/update', [AttendanceTeacherController::class, 'updateAttendanceTeachers']);

Route::post('/attendance/store', [AttendanceTeacherController::class, 'storeAttendance']);
Route::get('/api/mapel', [MataPelajaranController::class, 'getMapel']);
Route::get('/api/classes', [TeacherController::class, 'showAbsensiSiswa'])->name('absensiSiswa');
Route::get('/absensi-siswa', [TeacherController::class, 'showAbsensiSiswa'])->name('absensiSiswa');
Route::get('/get-mapel-by-teacher-id', [TeacherController::class, 'getMapelByTeacherId']);
Route::get('/absensiSiswa', [AttendanceController::class, 'absensiSiswaApi']);  
Route::get('/api/classes', [ClassController::class, 'index']);
Route::get('/teacher-attendance-report', [AttendanceTeacherController::class, 'getAttendanceReport']);

Route::middleware('auth:sanctum')->get('/logged-in-student', [StudentController::class, 'getLoggedInStudent']);
Route::get('/students-dashboard', [ProfileController::class, 'dashboard']);
Route::put('/attendances/update', [TeacherController::class, 'storeAttendance'])->name('attendances.update');

Route::post('/save-selected-mapel', [AttendanceController::class, 'saveSelectedMapel']);

Route::post('/jadwal', [MataPelajaranController::class, 'storeJadwal']);

Route::get('/jadwal', [MataPelajaranController::class, 'getAllJadwal']);


Route::get('/tahun_ajaran', action: [MataPelajaranController::class, 'getTahunAjaran']);

Route::get('/fetch-all-students', action: [StudentController::class, 'fetchAllStudents']);










