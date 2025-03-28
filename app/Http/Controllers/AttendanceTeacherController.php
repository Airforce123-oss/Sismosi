<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceTeacher;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Classes;

class AttendanceTeacherController extends Controller
{
    public function absensiGuru()
    {
        // Mengambil data absensi dari database, dengan relasi teacher dan class
        $attendance = AttendanceTeacher::with('teacher', 'class')->get();
        Log::info('Attendance data:', $attendance->toArray());
    
        // Mengambil data guru yang memiliki role 'teacher' dengan pagination
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher'); // Hanya ambil guru
        })->paginate(5);
        Log::info('Teachers data:', $teachers->items());
    
        // Mengambil semua kelas
        $classes = Classes::all();
        Log::info('Classes data:', $classes->toArray());
    
        // Mengambil data wali_kelas
        $waliKelas = User::whereHas('roles', function ($query) {
            $query->where('name', 'wali_kelas'); // Hanya ambil wali_kelas
        })->get();
        Log::info('Wali Kelas data:', $waliKelas->toArray());
    
        // Kelompokkan absensi berdasarkan class_id
        $groupedByClass = $attendance->groupBy('class_id');
        Log::info('Grouped Attendance by Class ID:', $groupedByClass->toArray());
    
        // Mengirim data absensi, teachers, dan classes ke halaman Inertia
        return inertia('Teachers/AbsensiGuru/index', [
            'attendance' => $groupedByClass,
            'attendanceRecords' => $attendanceRecords ?? [],
            'teachers' => $teachers->items(),  // Kirimkan data teacher sebagai array biasa
            'classes' => $classes,  // Pastikan classes dikirim
            'wali_kelas' => $waliKelas,  // Kirimkan data wali_kelas
            'currentPage' => $teachers->currentPage(),
            'lastPage' => $teachers->lastPage(),
            'total' => $teachers->total(),
            'perPage' => $teachers->perPage(),
        ]);
    }
    
    
    // API Method to fetch attendance data for teachers
    public function getAttendance($teacherId, $attendanceDate)
    {
        $attendance = AttendanceTeacher::where('teacher_id', $teacherId)
            ->where('attendance_date', $attendanceDate)
            ->first();
    
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found.'], 404);
        }
    
        return response()->json($attendance);
    }

    public function getAttendanceTeachers(Request $request)
    {
        // Ambil teacher_id dan attendance_date dari query string
        $teacherId = $request->query('teacher_id');
        $attendanceDate = $request->query('attendance_date');
    
        // Validasi input
        if (!$teacherId || !$attendanceDate) {
            return response()->json(['message' => 'Teacher ID and Attendance Date are required'], 400);
        }
    
        // Format tanggal menjadi Y-m-d menggunakan Carbon
        $attendanceDate = Carbon::parse($attendanceDate)->format('Y-m-d');
    
        // Cari absensi berdasarkan teacher_id dan attendance_date
        $attendance = AttendanceTeacher::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $attendanceDate)
            ->get();
    
        // Kirim data melalui Inertia
        return Inertia::render('Teachers/AbsensiGuru/Index', [
            'attendance' => $attendance,
        ]);
    }
    
    
    public function updateAttendance(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'status' => 'required|string',
        ]);
    
        // Menggunakan Carbon untuk memformat tanggal
        $attendance_date = Carbon::parse($validated['attendance_date'])->format('Y-m-d');
    
        // Mencari absensi berdasarkan teacher_id dan attendance_date
        $attendance = AttendanceTeacher::where('teacher_id', $validated['teacher_id'])
                                        ->where('attendance_date', $attendance_date)
                                        ->first();
    
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found.'], 404);
        }
    
        // Update status absensi
        $attendance->status = $validated['status'];
        $attendance->save();
    
        // Kembalikan data melalui Inertia
        return Inertia::render('Teachers/AbsensiGuru/Detail', [
            'attendance' => $attendance,
        ]);
    }
    

    public function store(Request $request)
    {
        // Debug data yang diterima
        Log::info('Request data:', $request->all());
    
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id|integer|min:1',
            'attendance_date' => 'required|date',
            'is_present' => 'required|boolean',
        ]);
    
        $attendance = AttendanceTeacher::create($validated);
    
        return response()->json([
            'message' => 'Attendance created successfully',
            'attendance' => $attendance,
        ], 201);
    }

    public function storeAttendance(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id|integer|min:1',
            'attendance_date' => 'required|date',
            'status' => 'required|string|in:P,A,S,I', // Validasi status
        ]);
    
        // Simpan data ke database
        $attendance = AttendanceTeacher::create([
            'teacher_id' => $validated['teacher_id'],
            'class_id' => $validated['class_id'],
            'attendance_date' => $validated['attendance_date'],
            'status' => $validated['status'],
        ]);
    
        // Kembalikan respon JSON
        return response()->json([
            'message' => 'Attendance status saved successfully',
            'attendance' => $attendance,
        ], 201);
    }
    public function create(Request $request)
    {
        // Validasi data yang diterima
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id|integer|min:1',
            'attendance_date' => 'required|date',
            'is_present' => 'required|boolean',
            'status' => 'required|string', // Validasi status
        ]);
        
        // Buat record absensi
        $attendance = AttendanceTeacher::create([
            'teacher_id' => $validated['teacher_id'],
            'class_id' => $validated['class_id'],
            'attendance_date' => $validated['attendance_date'],
            'is_present' => $validated['is_present'],
            'status' => $validated['status'],  // Simpan status
        ]);
    
        return response()->json([
            'message' => 'Attendance created successfully',
            'attendance' => $attendance,
        ], 201);
    }

    public function show($teacher_id, $date)
    {
        // Format tanggal menjadi Y-m-d
        $formattedDate = Carbon::parse($date)->format('Y-m-d');
        
        // Cari absensi berdasarkan teacher_id dan tanggal
        $attendance = AttendanceTeacher::where('teacher_id', $teacher_id)
            ->whereDate('attendance_date', $formattedDate)
            ->first();
        
        // Jika tidak ditemukan, kirimkan respon 404
        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found.'], 404);
        }
        
        // Kirimkan data absensi sebagai respon JSON
        return response()->json($attendance);
    }
}
