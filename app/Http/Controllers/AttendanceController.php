<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendancesResources;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function try1()
    {
        return view('try1');
    }

    // General method for fetching attendance data

    /*
        private function getAttendanceData(Request $request)
    {
        return Attendance::with('siswa')
            ->when($request->has('student_id'), function ($query) use ($request) {
                $query->where('student_id', $request->student_id);
            })
            ->get()
            ->groupBy('student_id')
            ->map(function ($attendance) {
                return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
            });
    }

    */
    private function getAttendanceData(Request $request)
    {
        $month = $request->query('month');
        $year = $request->query('year');
    
        // Mapping nama bulan ke angka
        $monthMapping = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03', 'April' => '04',
            'Mei' => '05', 'Juni' => '06', 'Juli' => '07', 'Agustus' => '08',
            'September' => '09', 'Oktober' => '10', 'November' => '11', 'Desember' => '12',
        ];
    
        // Konversi nama bulan ke angka jika perlu
        if (isset($monthMapping[$month])) {
            $month = $monthMapping[$month];
        }
    
        return Attendance::with('siswa')
            ->when($month, function ($query) use ($month, $year) {
                $startOfMonth = Carbon::createFromFormat('Y-m', "$year-$month")->startOfMonth();
                $endOfMonth = Carbon::createFromFormat('Y-m', "$year-$month")->endOfMonth();
    
                $query->whereBetween('tanggal_kehadiran', [$startOfMonth, $endOfMonth]);
            })
            ->get()
            ->groupBy('student_id')
            ->map(function ($attendance) {
                return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
            });
    }
    
    

    // Reusable method for rendering attendance views
    private function renderAttendanceView(Request $request, $viewName)
    {
        $attendances = $this->getAttendanceData($request);
        return inertia($viewName, [
            'attendances' => $attendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
        ]);
    }

    public function absensiSiswaById(Request $request, $id)
{
    // Daftar nama view berdasarkan ID
    $viewNames = [
        '1' => 'Students/absensiSiswaSatu',
        '2' => 'Students/absensiSiswaDua',
        '3' => 'Students/absensiSiswaTiga',
        '4' => 'Students/absensiSiswaEmpat',
        '5' => 'Students/absensiSiswaLima',
        '6' => 'Students/absensiSiswaEnam',
        '7' => 'Students/absensiSiswaTujuh',
        '8' => 'Students/absensiSiswaDelapan',
        '9' => 'Students/absensiSiswaSembilan',
        '10' => 'Students/absensiSiswaSepuluh',
        '11' => 'Students/absensiSiswaSebelas',
        '12' => 'Students/absensiSiswaDuaBelas',
    ];

    // Validasi apakah ID valid
    if (!array_key_exists($id, $viewNames)) {
        abort(404, 'Halaman tidak ditemukan.');
    }

    // Dapatkan nama view berdasarkan ID
    $viewName = $viewNames[$id];

    // Data absensi
    $attendances = $this->getAttendanceData($request);

    // Render view dengan data yang sesuai
    return inertia($viewName, [
        'attendances' => $attendances,
        'studentCount' => $attendances->count(),
        'filterParams' => $request->all(),
    ]);
}
public function absensiSiswa()
{
    return inertia('Students/absensiSiswa');
}

/*
public function absensiSiswa(Request $request)
{
    // Set locale untuk Carbon ke bahasa Indonesia
    Carbon::setLocale('id');
    $timezone = 'Asia/Jakarta'; 

    $request->validate([
        'month' => 'required|string|in:Januari,Februari,Maret,April,Mei,Juni,Juli,Agustus,September,Oktober,November,Desember',
        'year' => 'required|integer|min:1900|max:' . date('Y'),
    ]);

    // Ambil query string untuk bulan dan tahun
    $month = $request->query('month');
    $year = $request->query('year');

    // Validasi bulan dan tahun (opsional)
    if (!$month || !$year) {
        return inertia('Students/absensiSiswa', [
            'error' => 'Bulan dan tahun harus dipilih.',
            'attendances' => [],
            'filterParams' => $request->all(),
        ]);
    }

    // Coba untuk memparsing bulan dengan format yang lebih umum
    try {
        if (!in_array($month, ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'])) {
            throw new \Exception("Invalid month");
        }
        $monthNumber = Carbon::parse("1 $month $year", $timezone)->month;
    } catch (\Exception $e) {
        return inertia('Students/absensiSiswa', [
            'error' => 'Bulan atau tahun tidak valid.',
            'attendances' => [],
            'filterParams' => $request->all(),
        ]);
    }
    

    // Ambil data absensi berdasarkan bulan dan tahun
    $attendances = Attendance::with('siswa')
        ->whereYear('tanggal_kehadiran', $year)
        ->whereMonth('tanggal_kehadiran', $monthNumber)
        ->get()
        ->groupBy('student_id')
        ->map(function ($attendance) {
            return $attendance->pluck('status_kehadiran', 'tanggal_kehadiran')->toArray();
        });

    // Return data ke frontend
    return inertia('Students/absensiSiswa', [
        'attendances' => $attendances,
        'studentCount' => $attendances->count(),
        'filterParams' => $request->all(),
    ]);
}
*/

    public function absensiSiswaSatu(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaSatu');
    }

    public function absensiSiswaDua(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaDua');
    }

    public function absensiSiswaTiga(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaTiga');
    }

    public function absensiSiswaEmpat(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaEmpat');
    }

    public function absensiSiswaLima(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaLima');
    }

    public function absensiSiswaEnam(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaEnam');
    }

    public function absensiSiswaTujuh(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaTujuh');
    }

    public function absensiSiswaDelapan(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaDelapan');
    }

    public function absensiSiswaSembilan(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaSembilan');
    }

    public function absensiSiswaSepuluh(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaSepuluh');
    }

    public function absensiSiswaSebelas(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaSebelas');
    }

    public function absensiSiswaDuaBelas(Request $request)
    {
        return $this->renderAttendanceView($request, 'Students/absensiSiswaDuaBelas');
    }

    public function kelolaAbsensiSiswa()
    {
        return inertia('Students/kelolaAbsensiSiswa');
    }

    // API method for attendance with pagination and date filtering
    public function indexApi1(Request $request)
    {
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $attendancesQuery = Attendance::with('siswa');

        if ($request->has('date')) {
            $attendancesQuery->whereDate('tanggal_kehadiran', $request->input('date'));
        }

        $attendances = $attendancesQuery->paginate($perPage, ['*'], 'page', $page);

        // Format attendances
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

    public function store(Request $request)
    {
        Log::info('Data yang diterima:', $request->all());

        $request->validate([
            'tanggal_kehadiran' => 'required|date',
            'attendances' => 'required|array|min:1',
            'attendances.*.student_id' => 'required|integer|exists:students,id',
            'attendances.*.status_kehadiran' => 'required|in:A,P,I',
        ]);

        $tanggal_kehadiran = Carbon::parse($request->tanggal_kehadiran)->format('Y-m-d');
        $attendances = $request->input('attendances', []);

        Log::info('Attendance data before mapping:', ['attendances' => $attendances]);

        if (!is_array($attendances) || empty($attendances)) {
            return response()->json(['message' => 'Data absensi tidak valid.'], 400);
        }

        $attendanceData = array_map(function ($attendance) use ($tanggal_kehadiran) {
            $parsedStudentId = (int) $attendance['student_id'];
            return [
                'tanggal_kehadiran' => $tanggal_kehadiran,
                'student_id' => $parsedStudentId,
                'status_kehadiran' => $attendance['status_kehadiran'],
            ];
        }, $attendances);

        $insertedCount = Attendance::insert($attendanceData);

        Log::info('Data absensi berhasil disimpan', ['inserted_count' => $insertedCount]);

        if ($insertedCount) {
            $attendances = Attendance::whereDate('tanggal_kehadiran', $tanggal_kehadiran)->get();
            Log::info('Absensi yang disimpan:', ['attendances' => $attendances]);
            return response()->json([
                'message' => 'Attendance saved successfully',
                'attendances' => $attendances,
            ], 201);
        } else {
            return response()->json([
                'message' => 'Failed to save attendance.',
            ], 500);
        }
    }
}

