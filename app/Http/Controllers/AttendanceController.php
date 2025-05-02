<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendancesResources;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Mapel;
use Inertia\Inertia;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use stdClass;


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
        $selectedMapel = $request->input('selectedMapel');
        return inertia($viewName, [
            'attendances' => $attendances,
            'studentCount' => $attendances->count(),
            'filterParams' => $request->all(),
            'selectedMapel' => $selectedMapel->toArray(),

        ]);
    }

    public function absensiSiswaById(Request $request, $id)
{
    // Daftar nama view berdasarkan ID
    $viewNames = [
        '1' => 'Students/absensiSiswaJanuari',
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

public function absensiSiswaApi(Request $request)  
{  
    $month = $request->query('month');  
    $year = $request->query('year');  

    // Logika untuk mengambil data absensi siswa berdasarkan bulan dan tahun  
    $attendanceData = Attendance::whereMonth('tanggal', $month)  
        ->whereYear('tanggal', $year)  
        ->get();  

    return response()->json(['data' => $attendanceData]);  
}  

public function absensiSiswa()
{
    return inertia('Students/absensiSiswa');
}

public function saveSelectedMapel(Request $request)
{
    // Validasi data yang diterima
    $request->validate([
        'mapel' => 'required|string|max:60',
        'kelasId' => 'required|integer',
        // Hapus validasi student_id dan status_kehadiran jika tidak diperlukan
    ]);

    // Simpan data ke database
    $attendance = new Attendance();
    $attendance->mapel = $request->mapel; // Menyimpan nama mapel
    $attendance->kelas = $request->kelasId; // Menyimpan ID kelas
    $attendance->tanggal_kehadiran = now(); // Mengatur tanggal kehadiran
    // Hapus baris ini jika status_kehadiran tidak diperlukan
    // $attendance->status_kehadiran = $request->status_kehadiran; // Mengambil status kehadiran dari request

    // Simpan ke database
    if ($attendance->save()) {
        return response()->json(['message' => 'Data berhasil disimpan'], 200);
    } else {
        return response()->json(['message' => 'Gagal menyimpan data'], 500);
    }
}


public function absensiSiswaJanuari($kelas, $year, $mapel, $month)
{
    $decodedMapel = urldecode($mapel);
    $decodedMapelList = explode(',', $decodedMapel);

    Log::info('🔥 Route Absensi Dipanggil:', [
        'kelas' => $kelas,
        'year' => $year,
        'mapel' => $decodedMapelList,
        'month' => $month
    ]);

$students = Student::where('class_id', $kelas)->get();

    $selectedMapel = Mapel::whereIn('mapel', $decodedMapelList)->get();

    if ($selectedMapel->isEmpty()) {
        Log::error("⚠️ Tidak ada mata pelajaran ditemukan: " . implode(', ', $decodedMapelList));
    }

    $data = [
        'kelas' => $kelas,
        'year' => $year,
        'mapel' => $decodedMapelList,
        'month' => $month,
        'students' => $students,
        'selectedMapel' => $selectedMapel->toArray(),
    ];

    Log::info('📤 Data yang dikirim ke Vue:', $data);

    return request()->wantsJson()
        ? response()->json($data)
        : Inertia::render('Students/absensiSiswaJanuari', $data);
}


private function handleAbsensiByMonth($kelas, $year, $mapel, $month)
{
    $decodedMapel = urldecode($mapel);
    $students = Student::where('class_id', $kelas)->get();

    $selectedMapel = Mapel::where('mapel', $decodedMapel)->first();
    if (!$selectedMapel) {
        $selectedMapel = (object)[
            'id' => null,
            'mapel' => '',
            'kode_mapel' => '',
        ];
    } else {
        $selectedMapel = (object)$selectedMapel->toArray();
    }

    return Inertia::render('Students/Absensi' . ucfirst($month), [
        'kelas' => $kelas,
        'year' => $year,
        'mapel' => $decodedMapel,
        'month' => $month,
        'students' => $students,
        'selectedMapel' => $selectedMapel,
    ]);
}

public function absensiJanuari($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'januari');
}

public function absensiFebruari($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'februari');
}

public function absensiMaret($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'maret');
}

public function absensiApril($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'april');
}

public function absensiMei($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'mei');
}

public function absensiJuni($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'juni');
}

public function absensiJuli($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'juli');
}

public function absensiAgustus($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'agustus');
}

public function absensiSeptember($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'september');
}

public function absensiOktober($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'oktober');
}

public function absensiNovember($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'november');
}

public function absensiDesember($kelas, $year, $mapel)
{
    return $this->handleAbsensiByMonth($kelas, $year, $mapel, 'desember');
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

