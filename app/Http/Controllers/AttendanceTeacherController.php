<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceTeacher;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Teacher;
use App\Models\AbsensiSiswa;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Classes;

class AttendanceTeacherController extends Controller
{

    public function absensiGuru1()
    {
        return inertia('Teachers/AbsensiGuru/indexx');
    }

public function dataAbsensiGuru(Request $request)
{
    $bulan = $request->input('bulan', now()->format('m'));
    $tahun = $request->input('tahun', now()->format('Y'));

    $perPage = 5;

    // Ambil teacher_id yang punya absensi di bulan dan tahun tersebut
    $teacherIdsQuery = AttendanceTeacher::select('teacher_id')
        ->whereMonth('attendance_date', $bulan)
        ->whereYear('attendance_date', $tahun)
        ->groupBy('teacher_id')
        ->orderBy('teacher_id', 'asc');

    $totalTeachers = $teacherIdsQuery->count();

    // Ambil teacher_id dengan pagination manual (offset & limit)
    $currentPage = LengthAwarePaginator::resolveCurrentPage();
    $skip = ($currentPage - 1) * $perPage;
    $teacherIds = $teacherIdsQuery->skip($skip)->take($perPage)->pluck('teacher_id');

    // Ambil data teacher dengan relasi waliKelas
    $teachers = Teacher::with('waliKelas')
        ->whereIn('id', $teacherIds)
        ->orderBy('id')
        ->get();

    // Ambil absensi untuk teacher yang di halaman ini
    $attendances = AttendanceTeacher::whereIn('teacher_id', $teacherIds)
        ->whereMonth('attendance_date', $bulan)
        ->whereYear('attendance_date', $tahun)
        ->get()
        ->groupBy('teacher_id');

    // Transformasi data per teacher
    $transformed = $teachers->map(function ($teacher) use ($attendances) {
        $waliKelas = $teacher->waliKelas;

        $attendanceMap = collect($attendances[$teacher->id] ?? [])->mapWithKeys(function ($item) {
            return [Carbon::parse($item->attendance_date)->format('Y-m-d') => $item->status];
        })->toArray();

        return [
            'id' => $teacher->id,
            'teacher_id' => $teacher->id,
            'name' => $teacher->name,
            'nip' => $teacher->nip ?? '-',
            'attendance' => $attendanceMap,
        ];
    });

    // Buat paginator manual dari data teacher
    $paginated = new LengthAwarePaginator(
        $transformed,
        $totalTeachers,
        $perPage,
        $currentPage,
        [
            'path' => $request->url(),
            'query' => $request->query(),
        ]
    );

    return inertia('Teachers/AbsensiGuru/dataAbsensiGuru', [
        'teachers' => [
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
            ],
            'links' => [
                'first' => $paginated->url(1),
                'last' => $paginated->url($paginated->lastPage()),
                'prev' => $paginated->previousPageUrl(),
                'next' => $paginated->nextPageUrl(),
            ],
        ],
      'bulan' => $bulan,
      'tahun' => $tahun,
      'filter_nama' => $request->input('filter_nama', ''),
      'filter_nip' => $request->input('filter_nip', ''),
    ]);

}

    public function getAttendances(Request $request)
    {
        $query = AbsensiSiswa::query();
    
        if ($request->has('siswa_id')) {
            $query->where('siswa_id', $request->siswa_id);
        }
    
        if ($request->has('month') && $request->has('year')) {
            $query->whereMonth('tanggal_kehadiran', $request->month)
                  ->whereYear('tanggal_kehadiran', $request->year);
        }
    
        return response()->json($query->get());
    }
       
    public function absensiGuru()
    {
        // Ambil semua absensi guru dengan relasi teacher dan class
        $attendance = AttendanceTeacher::with('teacher', 'class')->get();
    
        // Buat struktur attendanceRecords yang dikelompokkan per teacher_id dan tanggal
        $attendanceRecords = [];
    
        foreach ($attendance as $record) {
            $teacherId = $record->teacher_id;
            $date = $record->attendance_date;
            $status = $record->status;
    
            if (!isset($attendanceRecords[$teacherId])) {
                $attendanceRecords[$teacherId] = [];
            }
    
            $attendanceRecords[$teacherId][$date] = $status;
        }
    
        // Ambil data guru yang memiliki role 'teacher' dengan pagination
        $teachers = User::whereHas('roles', function ($query) {
            $query->where('name', 'teacher');
        })->paginate(5);
    
        // Ambil semua kelas
        $classes = Classes::all();
    
        // Ambil semua user yang memiliki role wali_kelas
        $waliKelas = User::whereHas('roles', function ($query) {
            $query->where('name', 'wali_kelas');
        })->get();
    
        // Kelompokkan data absensi berdasarkan class_id jika dibutuhkan
        $groupedByClass = $attendance->groupBy('class_id');
    
        return inertia('Teachers/AbsensiGuru/index', [
            'attendance' => $groupedByClass,
            'attendanceRecords' => $attendanceRecords,
            'teachers' => $teachers->items(),
            'classes' => $classes,
            'wali_kelas' => $waliKelas,
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
    
        // Validasi dan format tanggal menjadi Y-m-d menggunakan Carbon
        try {
            $attendanceDate = Carbon::parse($attendanceDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format'], 400);
        }
    
        // Cari absensi berdasarkan teacher_id dan attendance_date
        $attendance = AttendanceTeacher::where('teacher_id', $teacherId)
            ->whereDate('attendance_date', $attendanceDate)
            ->with('teacher') // Pastikan relasi teacher dimuat
            ->get();
    
        // Mapping ulang data agar format lebih bersih dan aman
        $attendance = $attendance->map(function ($item) {
            return [
                'id' => $item->id,
                'teacher_id' => $item->teacher_id,
                'class_id' => $item->class_id ?? 'N/A', // Hindari `null`
                'attendance_date' => $item->attendance_date->format('Y-m-d'), // Pastikan format tanggal
                'is_present' => (bool) $item->is_present, // Pastikan boolean
                'status' => $item->status,
                'teacher' => $item->teacher ? [
                    'id' => $item->teacher->id,
                    'name' => $item->teacher->name,
                ] : null,
            ];
        });
    
        // Kirim data melalui Inertia
        return Inertia::render('Teachers/AbsensiGuru/Index', [
            'attendance' => $attendance,
        ]);
    }
    
    public function updateAttendance(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'attendance_date' => 'required|date',
            'status' => 'required|string|in:P,A,S,I',
        ]);
    
        $attendance_date = Carbon::parse($validated['attendance_date'])->format('Y-m-d');
    
        $attendance = AttendanceTeacher::firstOrCreate(
            [
                'teacher_id' => $validated['teacher_id'],
                'attendance_date' => $attendance_date,
            ],
            [
                'status' => $validated['status'],
            ]
        );
    
        if ($attendance->status !== $validated['status']) {
            $attendance->status = $validated['status'];
            $attendance->save();
        }
    
        return Inertia::render('Teachers/AbsensiGuru/Detail', [
            'attendance' => $attendance,
        ]);
    }
    
    

    public function store(Request $request)
    {
        // Debug data yang diterima
        //Log::info('Request data:', $request->all());
    
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'nullable|exists:classes,id|integer|min:1',
            'attendance_date' => 'required|date_format:Y-m-d H:i:s',
            'is_present' => 'required|boolean',
        ]);

        Log::info('Data yang diterima untuk create attendance:', $validated);
    
        $attendance = AttendanceTeacher::create($validated);
    
        return response()->json([
            'message' => 'Attendance created successfully',
            'attendance' => $attendance,
        ], 201);
    }
    public function storeAttendance(Request $request)
    {
        // Cek data yang diterima dari frontend
        Log::info('Data yang diterima untuk absensi:', $request->all());
    
        // Validasi data yang diterima (tanpa class_id)
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'attendance_date' => 'required|date', // Pastikan format tanggal valid
            'status' => 'required|string|in:P,A,S,I', // Validasi status
        ]);
    
        // Mengonversi tanggal yang diterima ke format 'Y-m-d' menggunakan Carbon
        $attendanceDate = Carbon::parse($validated['attendance_date'])->format('Y-m-d'); // Hanya tanggalnya, tanpa waktu
    
        // Simpan data ke database (tanpa class_id)
        $attendance = AttendanceTeacher::create([
            'teacher_id' => $validated['teacher_id'],
            'attendance_date' => $attendanceDate, // Gunakan tanggal tanpa waktu
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
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id|integer|min:1',
            'attendance_date' => 'required|date',
            'is_present' => 'required|boolean',
            'status' => 'required|string|in:P,A,S,I', 
        ]);
        
        // Buat record absensi
        $attendance = AttendanceTeacher::create([
            'teacher_id' => $validated['teacher_id'],
            'class_id' => $validated['class_id'],
            //'attendance_date' => $validated['attendance_date'],
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
    public function getAttendanceReport(Request $request)
    {
        $month = (int) $request->query('month');
        $year = (int) $request->query('year');
        $teacherId = $request->query('teacher_id');
    
        if (!$month || !$year) {
            return response()->json(['error' => 'Bulan dan tahun wajib diisi.'], 400);
        }
    
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
    
        // Ambil semua guru, termasuk relasi attendanceTeachers (bisa kosong)
        $teacherQuery = Teacher::with(['attendanceTeachers' => function ($query) use ($month, $year) {
            $query->whereMonth('attendance_date', $month)
                  ->whereYear('attendance_date', $year);
        }]);
    
        if ($teacherId) {
            $teacherQuery->where('id', $teacherId);
        }
    
        $teachers = $teacherQuery->get();
    
        $data = $teachers->map(function ($teacher) use ($month, $year, $daysInMonth) {
            // Format ulang semua attendance record jadi array [tanggal => status]
            $attendanceRecords = collect();
            if ($teacher->attendanceTeachers) {
                $attendanceRecords = $teacher->attendanceTeachers->mapWithKeys(function ($record) {
                    $date = Carbon::parse($record->attendance_date)->format('Y-m-d');
                    return [$date => $record->status];
                });
            }
    
            // Bangun array attendance harian
            $attendance = [];
            foreach (range(1, $daysInMonth) as $day) {
                $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                $attendance[$date] = $attendanceRecords->get($date, 'Belum Absen');
            }
    
            return [
                'teacher_id' => $teacher->id,
                'name' => $teacher->name,
                'attendance' => $attendance,
            ];
        });
    
        return response()->json($data);
    }   
}
