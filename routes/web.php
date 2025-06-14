<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceTeacherController;
use App\Http\Controllers\MasterJabatanController;
use App\Models\Student;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
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
use App\Http\Controllers\ParentController;
use App\Http\Controllers\RoleTypeController;
use App\Http\Controllers\MataPelajaranController;
use App\Http\Controllers\StudentRoleController;  
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

Route::middleware([
    'web',
    EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum',
])->get('/api/logged-in-student', function () {
    return Auth::user();
});

// Authentication Routes
Route::middleware('web')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});


// Dashboard Routes
Route::middleware(['auth', 'verified'])->group(function () {
// Dashboard Admin
Route::middleware('auth')->get('/dashboard', function () {
    $user = Auth::user();

    \Log::info('Redirecting user to their dashboard. Role: ' . $user->role_name);

    switch ($user->role_name) {
        case 'student':
            // Cari data student berdasarkan user_id
            $student = Student::where('user_id', $user->id)->first();

            if ($student) {
                // Redirect ke route student.dashboard dengan parameter lengkap
                return Inertia::location(route('student.dashboard', [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                ]));
            } else {
                // Jika tidak ada student terkait, log dan redirect ke dashboard umum
                \Log::warning("User ID {$user->id} role student tapi tidak punya data student.");
                return Inertia::location(route('dashboard'));
            }

        case 'teacher':
            return Inertia::location(route('teacher.dashboard'));
        case 'parent':
            return Inertia::location(route('parent.dashboard'));
        case 'admin':
            return Inertia::location(route('admin.dashboard'));
        default:
            \Log::info('Invalid role, redirecting to login.');
            return Inertia::location(route('login'));
    }
})->name('dashboard');


    // Dashboard Admin
    Route::get('/admin-dashboard', function () {
        return Inertia::render('dashboard');
    })->name('admin.dashboard');

    // Dashboard Guru
    Route::get('/teacher-dashboard', function () {
        return Inertia::render('teachersDashboard');
    })->name('teacher.dashboard');

    Route::get('/parent-dashboard', function () {
        return Inertia::render('Parents/parentsDashboard');
    })->name('parent.dashboard');
    


    // Dashboard Siswa
    Route::middleware('auth')->get('/student-dashboard/{student_id?}', function (Request $request, $student_id = null) {
        $user = Auth::user();
    
        // Ambil student_id dari route param atau query string
        $studentId = $student_id ?? $request->input('student_id');
    
        // Ambil student_name dari query string, jika tidak ada gunakan nama siswa dari DB nanti
        $studentNameFromQuery = $request->query('student_name');
    
        // Query siswa dengan user_id dan student_id jika ada
        $studentQuery = Student::where('user_id', $user->id);
    
        if ($studentId) {
            $studentQuery->where('id', $studentId);
        }
    
        $student = $studentQuery->first();
    
        if (!$student) {
            return redirect()->route('dashboard')->with('message', 'Student not found or unauthorized access');
        }
    
        // Jika student_name dari query string tidak ada, pakai nama dari DB
        $studentName = $studentNameFromQuery ?: $student->name;
    
        return Inertia::render('studentsDashboard', [
            'student_id' => $student->id,
            'student_name' => $studentName,
        ]);
    })->name('student.dashboard');
    
      
    Route::get('/memeriksa-tugas', [ParentController::class, 'memeriksaTugasSubmit'])->name('memeriksa-tugas');
    Route::get('/memberikan-komentar', [ParentController::class, 'memberikanKomentarKepadaSiswa'])->name('memberikan-komentar');
    Route::get('/melihat-presensi', [ParentController::class, 'melihatPresensiSiswa'])->name('melihat-presensi');
    Route::get('/melihat-nilai', [ParentController::class, 'melihatNilaiSiswa'])->name('melihat-nilai');
    

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Resource Routes
    Route::resource('students', StudentController::class);
    Route::resource('teachers', TeacherController::class);
    Route::resource('jadwalmataPelajarans', MataPelajaranController::class);
    Route::resource('settingjadwalmataPelajarans', MataPelajaranController::class);
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
    Route::get('/indexx', [AttendanceTeacherController::class, 'absensiGuru1'])->name('studentsabsensiGuru');
    Route::get('/dataAbsensiGuru', [AttendanceTeacherController::class, 'dataAbsensiGuru'])->name('teachersabsensiGuru');
    Route::get('/attendance-teachers', [AttendanceTeacherController::class, 'getAttendanceTeachers']);
    Route::get('/absensiGuru', [AttendanceTeacherController::class, 'absensiGuru'])->name('studentsabsensiGuru');
    Route::post('/absensiGuru', [AttendanceTeacherController::class, 'store']);
    Route::put('/attendances/{studentId}', [AttendanceController::class, 'update']);
    Route::post('/api/attendances', [AttendanceController::class, 'store'])->name('apiattendancesstore');
    Route::post('/update/{id}/tanggal/{tanggal_kehadiran}', [AttendanceController::class, 'update'])->name('update');
    Route::get('/kelolaAbsensiSiswa', [AttendanceController::class, 'kelolaAbsensiSiswa'])->name('studentkelolaAbsensiSiswa');
    
    // Custom Attendance Views
    //Route::get('/absensi/{kelas}/{year}/{mapel}/{month}', [TeacherController::class, 'showAbsensi'])->name('absensi');  
    Route::get('/absensi/{classId}/{year}/{mapel}/{month}', [AttendanceController::class, 'absensiSiswaJanuari'])->name('absensiSiswaJanuari');
    Route::get('/absensi/{kelas}/{year}/{mapel}/januari', [AttendanceController::class, 'absensiJanuari'])->name('absensiSiswaJanuari');
    Route::get('/absensi/{kelas}/{year}/{mapel}/februari', [AttendanceController::class, 'absensiFebruari'])->name('absensiSiswaFebruari');
    Route::get('/absensi/{kelas}/{year}/{mapel}/maret', [AttendanceController::class, 'absensiMaret'])->name('absensiSiswaMaret');
    Route::get('/absensi/{kelas}/{year}/{mapel}/april', [AttendanceController::class, 'absensiApril'])->name('absensiSiswaApril');
    Route::get('/absensi/{kelas}/{year}/{mapel}/mei', [AttendanceController::class, 'absensiMei'])->name('absensiSiswaMei');
    Route::get('/absensi/{kelas}/{year}/{mapel}/juni',  [AttendanceController::class, 'absensiJuni'])->name('absensiSiswaJuni');
    Route::get('/absensi/{kelas}/{year}/{mapel}/juli', [AttendanceController::class, 'absensiJuli'])->name('absensiSiswaJuli');
    Route::get('/absensi/{kelas}/{year}/{mapel}/agustus', [AttendanceController::class, 'absensiAgustus'])->name('absensiSiswaAgustus');
    Route::get('/absensi/{kelas}/{year}/{mapel}/september', [AttendanceController::class, 'absensiSeptember'])->name('absensiSiswaSeptember');
    Route::get('/absensi/{kelas}/{year}/{mapel}/oktober', [AttendanceController::class, 'absensiOktober'])->name('absensiSiswaOktober');
    Route::get('/absensi/{kelas}/{year}/{mapel}/november', [AttendanceController::class, 'absensiNovember'])->name('absensiSiswaNovember');
    Route::get('/absensi/{kelas}/{year}/{mapel}/desember', [AttendanceController::class, 'absensiDesember'])->name('absensiSiswaDesember');
    // Other Routes
    Route::get('/tugasTambah', [TugasController::class, 'tambahTugas'])->name('tugastambah');
    Route::get('/mataPelajaran', [MataPelajaranController::class, 'mataPelajaran'])->name('matapelajaran.index');
    Route::get('/settingJadwalMataPelajaran', [MataPelajaranController::class, 'settingJadwalMataPelajaran'])->name('matapelajaran.JadwalMataPelajaran');
    Route::get('/laporanJadwalMataPelajaran', [MataPelajaranController::class, 'laporanJadwalMataPelajaran'])->name('matapelajaran.LaporanJadwalMataPelajaran');
    Route::get('/settingjadwalmataPelajarans/settingJadwalMataPelajaran', [MataPelajaranController::class, 'settingJadwalMataPelajaran'])
    ->name('settingjadwalmataPelajarans.settingJadwalMataPelajaran');
    Route::get('/createJadwalMataPelajaran', [MataPelajaranController::class, 'createJadwalMataPelajaran'])->name('matapelajaran.createJadwalMataPelajaran');
    Route::get('/mataPelajaran/create', [MataPelajaranController::class, 'create'])->name('matapelajaran.create');
    Route::post('/mata-pelajaran/store', [MataPelajaranController::class, 'store'])->name('matapelajaran.store');
    Route::delete('/matapelajaran/{id}', [MataPelajaranController::class, 'destroy'])->name('matapelajaran.destroy');
    Route::get('/matapelajaran/{mapel}/edit', [MataPelajaranController::class, 'edit'])->name('matapelajaran.edit');
    Route::post('/jadwal-mata-pelajaran', [MataPelajaranController::class, 'storeJadwal'])
     ->name('jadwal.store');
     Route::get('/jadwal', [MataPelajaranController::class, 'getJadwal'])->name('jadwal.get');
     Route::get('/jadwal-mata-pelajaran/create', [MataPelajaranController::class, 'createJadwalMataPelajaran'])
     ->name('jadwalmataPelajarans.createjadwalmatapelajaran');

 

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



Route::get('melihatTugas', [StudentRoleController::class, 'melihatTugas'])->name('melihatTugas');  
Route::get('melihatDataAbsensiSiswa', [StudentRoleController::class, 'melihatDataAbsensiSiswa'])->name('melihatDataAbsensiSiswa');  
Route::get('melihatJadwalPelajaran', [StudentRoleController::class, 'melihatJadwalPelajaran'])->name('melihatJadwalPelajaran');  

Route::resource('student_roles', StudentRoleController::class);  




Route::put('/user/{user}/roles', [UserController::class, 'updateRoles'])->name('user.roles.update');

Route::post('/attendance/store', [AttendanceTeacherController::class, 'storeAttendance']);

Route::get('/indexMasterJabatan', [MasterJabatanController::class, 'indexMasterJabatan'])->name('indexMasterJabatan');


Route::resource('master-jabatan', MasterJabatanController::class);

Route::get('/master-jabatan/{master_jabatan}/edit', [MasterJabatanController::class, 'edit'])->name('master-jabatan.edit');

Route::post('/tugas-siswa', [TeacherController::class, 'createTugasSiswa'])->name('tugas-siswa.create');

Route::get('/jadwal-mata-pelajaran/by-teacher', [TeacherController::class, 'getJadwalByTeacher'])->name('jadwal.byTeacher');

Route::get('/setting-laporan-nilai-siswa', [TeacherController::class, 'settingLaporanNilaiSiswa'])->name('settingLaporanNilaiSiswa');

Route::get('/api/parent/students/filter', [ParentController::class, 'filterStudents'])
    ->name('api.parent.students.filter')
    ->middleware('role:parent');


// Include Auth Routes
require __DIR__ . '/auth.php';
require __DIR__ . '/admin-auth.php';

