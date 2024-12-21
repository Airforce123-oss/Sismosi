<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendancesResources;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Classes; 
use App\Models\Section;
use App\Models\Religion;
use App\Models\Teacher;
use App\Models\Gender;
use Illuminate\Support\Facades\DB;
use App\Models\NoInduk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class AttendanceController extends Controller
{

    public function try1()
    {

        return view('try1');
    }

    public function absensiSiswaSatu(Request $request)
    {
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaSatu', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
    
    public function absensiSiswaDua(Request $request)
    {
        // Query attendance untuk absensi Siswa Dua
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaDua', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
    
    public function absensiSiswaTiga(Request $request)
    {
        // Query attendance untuk absensi Siswa Tiga
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaTiga', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
    
    public function absensiSiswaEmpat(Request $request)
    {
        // Query attendance untuk absensi Siswa Empat
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaEmpat', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
    
    public function absensiSiswaLima(Request $request)
    {
        // Query attendance untuk absensi Siswa Lima
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaLima', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
    
    public function absensiSiswaEnam(Request $request)
    {
        // Query attendance untuk absensi Siswa Enam
        $attendances = Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get();
    
        $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });
    
        return inertia('Students/absensiSiswaEnam', [
            'attendances' => $formattedAttendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }
 
    public function absensiSiswa() {
        return inertia('Students/absensiSiswa');
    }

    public function kelolaAbsensiSiswa() {
        return inertia('Students/kelolaAbsensiSiswa');
    }

    public function indexApi1(Request $request)
    {
        $perPage = $request->input('per_page', 5); // Ambil jumlah item per halaman
        $page = $request->input('page', 1); // Ambil halaman saat ini
    
        // Cek jika ada parameter 'date' yang dikirimkan dari frontend
        if ($request->has('date')) {
            $date = $request->input('date');
            // Debugging: Cek nilai parameter date yang diterima
            Log::info('Tanggal yang diterima:', ['date' => $date]);
        
            // Ambil data absensi dengan tanggal yang sesuai dan paginasi
            $attendances = Attendance::with('siswa')
                ->whereDate('tanggal_kehadiran', $date)
                ->paginate($perPage, ['*'], 'page', $page); // Menambahkan paginasi
        } else {
            // Jika tidak ada parameter 'date', ambil seluruh data absensi dengan paginasi
            $attendances = Attendance::with('siswa')
                ->paginate($perPage, ['*'], 'page', $page); // Menambahkan paginasi
        }
    
        // Cek apakah data absensi ditemukan
        if ($attendances->isEmpty()) {
            Log::info('Tidak ada data absensi ditemukan.');
        }
    
        // Format data absensi untuk response
        $formattedAttendances = [];
        foreach ($attendances as $attendance) {
            $studentId = $attendance->student_id;
            $date = $attendance->tanggal_kehadiran;
            $status = $attendance->status_kehadiran;
        
            if (!isset($formattedAttendances[$studentId])) {
                $formattedAttendances[$studentId] = [];
            }
        
            $formattedAttendances[$studentId][$date] = $status;
        }
    
        // Kirim response dalam format JSON dengan data paginasi
        return response()->json([
            'attendances' => $formattedAttendances,
            'pagination' => [
                'total' => $attendances->total(),
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
            ]
        ], 200);
    }
    
    
    
    protected function applySearch(Builder $query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function store(Request $request)
    {
        Log::info('Data yang diterima:', $request->all());
    
        // Validasi data yang diterima
        $request->validate([
            'tanggal_kehadiran' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|integer|exists:students,id',
            'attendances.*.status_kehadiran' => 'required|in:A,P,I',
        ]);
    
        // Parsing dan format tanggal
        $tanggal_kehadiran = Carbon::parse($request->tanggal_kehadiran)->format('Y-m-d');
        
        // Mendapatkan data absensi
        $attendances = $request->input('attendances', []); // Mendapatkan data absensi
    
        // Log sebelum pengecekan lebih lanjut untuk melihat apa yang diterima
        Log::info('Attendance data before mapping:', ['attendances' => $attendances]);
    
        if (!is_array($attendances) || empty($attendances)) {
            return response()->json(['message' => 'Data absensi tidak valid.'], 400);
        }
        Log::info('Attendance Data:', ['attendance' => $attendances]);
    
        // Map data absensi untuk disimpan
        $attendanceData = array_map(function ($attendance) use ($tanggal_kehadiran) {
            $parsedStudentId = (int) $attendance['student_id']; // Konversi ID siswa
            return [
                'tanggal_kehadiran' => $tanggal_kehadiran,
                'student_id' => $parsedStudentId,
                'status_kehadiran' => $attendance['status_kehadiran'],
            ];
        }, $attendances);
    
        // Insert data absensi dalam sekali proses
        $insertedCount = Attendance::insert($attendanceData);
    
        Log::info('Data absensi berhasil disimpan', ['inserted_count' => $insertedCount]);
    
        if ($insertedCount) {
            // Data berhasil disimpan
            $attendances = Attendance::whereDate('tanggal_kehadiran', $tanggal_kehadiran)->get();
            Log::info('Absensi yang disimpan:', ['attendances' => $attendances]);
            return response()->json([
                'message' => 'Attendance saved successfully',
                'attendances' => $attendances,
            ], 201);
        } else {
            // Jika insert gagal
            return response()->json([
                'message' => 'Failed to save attendance.',
            ], 500);
        }
    }
    
    


    public function storeAttendance(Request $request)
{
    // Validasi input
    $request->validate([
        'tanggal_kehadiran' => 'required|date',
        'attendances' => 'required|array|min:1',
        'attendances.*.student_id' => 'required|integer|exists:students,id',
        'attendances.*.status_kehadiran' => 'required|in:A,P,I',
    ]);

    // Logging data awal
    Log::info('Data yang diterima:', $request->all());

    // Filter data yang valid
    $attendanceData = array_filter($request->attendances, function ($attendance) {
        return $attendance['status_kehadiran'] !== 'Belum diabsen';
    });

    // Log jumlah data setelah filter
    Log::info('Jumlah data absensi yang valid:', ['count' => count($attendanceData)]);

    // Jika tidak ada data yang valid
    if (empty($attendanceData)) {
        Log::info('Tidak ada data absensi ditemukan setelah filter.');
        return response()->json(['message' => 'Tidak ada data absensi yang valid untuk disimpan.'], 422);
    }

    // Proses menyimpan data absensi
    try {
        foreach ($attendanceData as $attendance) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $attendance['student_id'],
                    'tanggal_kehadiran' => $attendance['tanggal_kehadiran']
                ],
                [
                    'status_kehadiran' => $attendance['status_kehadiran']
                ]
            );
        }

        Log::info('Data absensi berhasil disimpan.', ['data' => $attendanceData]);

        return response()->json(['message' => 'Data absensi berhasil disimpan.'], 200);

    } catch (\Exception $e) {
        Log::error('Gagal menyimpan data absensi:', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data absensi.'], 500);
    }
}

    
public function update(Request $request, $studentId, $attendance)
{
    Log::info('Received student_id:', ['student_id' => $studentId]);
    Log::info('Parsing student_id:', ['student_id' => $attendance['student_id']]);
    // Konversi studentId menjadi integer
    $parsedStudentId = (int) $studentId;
    $studentId = (int) $attendance['student_id'];

    // Validasi data yang diterima
    $validated = $request->validate([
        'tanggal_kehadiran' => 'required|date',
        'status_kehadiran' => 'required|string|max:1',
    ]);

    // Cek absensi berdasarkan student_id dan tanggal_kehadiran
    $attendance = Attendance::where('student_id', $parsedStudentId)
        ->where('tanggal_kehadiran', $validated['tanggal_kehadiran'])
        ->first();

    // Debugging: Menampilkan query yang digunakan untuk mengambil absensi
    DB::listen(function ($query) {
        Log::info('SQL Query:', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
        ]);
    });

    // Debugging: Menampilkan data absensi yang diambil
    dd($attendance);

    if ($attendance) {
        // Perbarui status kehadiran
        $attendance->status_kehadiran = $validated['status_kehadiran'];
        $attendance->save();

        // Kembalikan data absensi yang telah diperbarui
        return response()->json($attendance, 200);
    } else {
        // Jika absensi tidak ditemukan
        return response()->json(['message' => 'Attendance not found'], 404);
    }
}

    
}

/*
     foreach ($validated['student_id'] as $index => $student_id) {
            
            Attendance::create([
                'student_id' => $student_id,
                'tanggal_kehadiran' => $validated['tanggal_kehadiran'],
                'status_kehadiran' => $validated['status_kehadiran'][$index],
            ]);
        }
        */

            /*
              Log::info('Request Data:', $request->all());
        // Validasi data
        $validatedData = $request->validate([
            'id_siswa' => 'required|array',
            'tanggal_kehadiran' => 'required|date',
            'status_kehadiran' => 'required|array',
        ]);
    
        // Cek apakah data absensi sudah ada
        $cekkehadiran = Attendance::where('tanggal_kehadiran', $validatedData['tanggal_kehadiran'])
            ->whereIn('id_siswa', $validatedData['id_siswa'])
            ->get();
    
        if ($cekkehadiran->count() > 0) {
            return response()->json(['message' => 'Data absensi sudah ada'], 409); // Conflict status
        }
    
        $dataSiswa = count($validatedData['id_siswa']);
    
        for ($i = 0; $i < $dataSiswa; $i++) {
            Attendance::create([
                'id_siswa' => $validatedData['id_siswa'][$i],
                'tanggal_kehadiran' => $validatedData['tanggal_kehadiran'],
                'status_kehadiran' => $validatedData['status_kehadiran'][$i],
            ]);
        }
    
        return redirect()->back()->with('success', 'data berhsil di simpan');
        */

         /*
    
    public function store(Request $request)
    {
        Log::info('POST Request Data:', $request->all()); // Cek data yang diterima
        dd($request->all());
        $request->validate([
            'tanggal_kehadiran' => 'required|date',
            'student_id' => 'required|integer|exists:students,id',
            'status_kehadiran' => 'required|string|in:P,A,S,I',
        ]);
        
        $tanggalKehadiran = Carbon::parse($request->tanggal_kehadiran)->format('Y-m-d');
        $attendances = $request->input('attendances', []);
        if (empty($attendances)) {
       return response()->json(['message' => 'Data absensi tidak valid.'], 400);
}


        $attendanceData = array_map(function ($attendance) use ($tanggalKehadiran) {
            return [
                'tanggal_kehadiran' => $tanggalKehadiran,
                'student_id' => (int) $attendance['student_id'],
                'status_kehadiran' => $attendance['status_kehadiran'],
            ];
        }, $request->attendances);
        try {
            Log::info('Attendance data:', $attendanceData);
            Attendance::insert($attendanceData);

            $attendances = Attendance::whereDate('tanggal_kehadiran', $tanggalKehadiran)->get();

            return response()->json([
                'message' => 'Attendance saved successfully',
                'attendances' => $attendances, // Kirim data absensi yang sudah disimpan
            ], 201);


        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan absensi.', 'error' => $e->getMessage()], 500);
        }
    }
    */
    

