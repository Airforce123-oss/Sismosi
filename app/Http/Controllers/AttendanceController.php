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
    // Log query SQL yang dieksekusi
    DB::listen(function ($query) {
        Log::info('Executed Query:', [
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
        ]);
    });

    // Query data attendances
    $attendances = Attendance::with('siswa')
        ->when($request->has('student_id'), function ($query) use ($request) {
            $query->where('student_id', $request->student_id);
        })
        ->get();

    // Format data attendances
    $formattedAttendances = $attendances->groupBy('student_id')->map(function ($attendance) {
        return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran');
    });

    // Log formatted attendances sebelum mengirimkan response
    Log::info('Returning formatted attendances:', ['formattedAttendances' => $formattedAttendances]);

    //return response()->json(['attendances' => /$formattedAttendances]);

    // Mengirimkan response dalam format JSON
    //return response()->json([
      //  'attendances' => $formattedAttendances
    //]);
    return inertia('Students/absensiSiswaSatu', [
        'attendances' => $formattedAttendances
    ]);
}

 
    public function absensiSiswa() {
        return inertia('Students/absensiSiswa');
    }

    public function kelolaAbsensiSiswa() {
        return inertia('Students/kelolaAbsensiSiswa');
    }


    /*
        public function indexApi1(Request $request)
    {
        // Membuat query untuk mengambil absensi
        $query = Attendance::query()->with('siswa');
    
        // Menambahkan filter berdasarkan tanggal jika ada
        if ($request->has('date')) {
            $query->whereDate('tanggal_kehadiran', $request->date);
        }
    
        // Mengambil data absensi
        $attendances = $query->get();
    
        Log::info('Fetched attendances:', ['attendances' => $attendances]);

        if ($attendances->isEmpty()) {
            Log::info('No attendance records found.');
        }
    
        // Memformat data absensi menjadi array yang diinginkan
        $formattedAttendances = $attendances->map(function ($attendance) {
            return [
                'student_id' => $attendance->student_id,
                'date' => $attendance->tanggal_kehadiran,
                'status' => $attendance->status_kehadiran,
            ];
        });
    
        
        // Mengembalikan data dalam format yang sesuai
        return response()->json([
            'attendances' => $formattedAttendances
        ]);
    }
    */

    public function indexApi1(Request $request)
    {
        $query = Attendance::query()->with('siswa');
    
        if ($request->has('date')) {
            $query->whereDate('tanggal_kehadiran', $request->date);
        }
    
        $attendances = $query->get();
    
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

        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        // Log untuk memastikan data benar
        Log::info('Formatted Attendances:', $formattedAttendances);
    
        // Pastikan respons JSON dikembalikan
        return response()->json([
            'attendances' => $formattedAttendances
        ], 200, ['Content-Type' => 'application/json']);
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
            'attendances.*.status_kehadiran' => 'required|string|in:P,A,S,I',
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
    
        try {
            // Map data absensi untuk disimpan
            $attendanceData = array_map(function ($attendance) use ($tanggal_kehadiran) {
                return [
                    'tanggal_kehadiran' => $tanggal_kehadiran,
                    'student_id' => $attendance['student_id'],
                    'status_kehadiran' => $attendance['status_kehadiran'],
                ];
            }, $attendances);
    
            // Log data setelah diproses

    
            // Menyimpan data absensi
            $insertedCount = Attendance::insert($attendanceData);
    
            // Cek apakah data berhasil disimpan
            if ($insertedCount > 0) {
                // Data berhasil disimpan
                $attendances = Attendance::whereDate('tanggal_kehadiran', $tanggal_kehadiran)->get();
    
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
            
    
        } catch (\Exception $e) {
            Log::error('Error saving attendance:', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);
    
            return response()->json([
                'message' => 'Gagal menyimpan absensi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function getAttendances()
    {
        // Mengambil semua data absensi dari database
        //$attendances = Attendance::all();
       // $attendances = $attendanceQuery->get();
        // Menampilkan data absensi untuk debugging
       // dd($attendances);
    }


    public function update(Request $request, $studentId)
    {

            $validated = $request->validate([
            'tanggal_kehadiran' => 'required|date',
            'status_kehadiran' => 'required|string|max:1', // misalnya hanya status yang valid
        ]);
    
        $attendance = Attendance::where('student_id', $studentId)
            ->where('tanggal_kehadiran', $validated['tanggal_kehadiran'])
            ->first();
    
        if ($attendance) {
            $attendance->status_kehadiran = $validated['status_kehadiran'];
            $attendance->save();
            return response()->json($attendance, 200);
        } else {
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
    

