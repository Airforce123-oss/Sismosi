<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendancesResources;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\WaliKelas;
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
            'Januari' => '01',
            'Februari' => '02',
            'Maret' => '03',
            'April' => '04',
            'Mei' => '05',
            'Juni' => '06',
            'Juli' => '07',
            'Agustus' => '08',
            'September' => '09',
            'Oktober' => '10',
            'November' => '11',
            'Desember' => '12',
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
        $user = Auth::user();
        return Inertia::render('Students/absensiSiswa', [
            'teacherClass' => $user->classroom, // ✅ kirimkan kelas
        ]);
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

        if ($attendance->save()) {
            return response()->json(['message' => 'Data berhasil disimpan'], 200);
        } else {
            return response()->json(['message' => 'Gagal menyimpan data'], 500);
        }
    }

    private function getSemesterFromMonth($month)
    {
        // Jika berupa string nama bulan, konversi ke angka
        if (!is_numeric($month)) {
            $monthMap = [
                'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
                'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
                'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
            ];

            $lowerMonth = strtolower($month);
            $month = $monthMap[$lowerMonth] ?? null;
        }

        // Validasi fallback
        if (!$month || !is_numeric($month)) {
            return 'Tidak diketahui';
        }

        return in_array((int) $month, [7, 8, 9, 10, 11, 12]) ? 'Ganjil' : 'Genap';
    }

        private function convertMonthToNumber($month)
    {
        $monthMap = [
            'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
            'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
            'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
        ];

        $lower = strtolower(trim($month));
        return $monthMap[$lower] ?? null;
    }

    public function absensiSiswaByMonth($classId, $year, $mapel, $month)
    {
        $user = Auth::user();

        // Decode dan proses mapel
        $decodedMapel = urldecode($mapel);
        $decodedMapelList = array_map('ucfirst', explode(',', strtolower($decodedMapel)));
        Log::info('🧪 Route parameters', compact('classId', 'year', 'mapel', 'month'));
        Log::info('🧪 Authenticated User', [
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        // Konversi bulan ke angka jika perlu
        $monthNumber = is_numeric($month) ? (int) $month : $this->convertMonthToNumber($month);
        $semester = $this->getSemesterFromMonth($monthNumber);

        // Validasi bahwa user adalah wali kelas dari classId
        $waliKelas = WaliKelas::where('user_id', $user->id)
            ->where('class_id', (int) $classId)
            ->first();

        if (!$waliKelas) {
            return response()->json(['message' => 'Anda bukan wali kelas dari kelas ini.'], 403);
        }

        $teacherClassData = DB::table('classes')
            ->where('id', $classId)
            ->first();

        Log::info('📘 teacherClassData', ['data' => $teacherClassData]);

        // Ambil siswa di kelas beserta absensinya
        $students = Student::with([
            'mapel',
            'attendances' => function ($q) use ($year, $monthNumber, $decodedMapelList) {
                if ($year) {
                    $q->whereYear('tanggal_kehadiran', $year);
                }
                if ($monthNumber) {
                    $q->whereMonth('tanggal_kehadiran', $monthNumber);
                }
                if (!empty($decodedMapelList)) {
                    $q->whereIn('mapel', $decodedMapelList);
                }
            }
        ])
        ->where('class_id', $classId)
        ->get();

        // Format data siswa
        $students = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'subject' => $student->mapel->pluck('mapel')->implode(', '),
                'attendances' => $student->attendances->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'tanggal' => $a->tanggal_kehadiran,
                        'status' => $a->status_kehadiran,
                        'mapel' => $a->mapel,
                    ];
                })->values(),
            ];
        });

        Log::info('📋 Final students with attendances:', $students->toArray());

        // Ambil info mapel yang dipilih
        $selectedMapel = Mapel::whereIn('mapel', $decodedMapelList)->get();

        if ($selectedMapel->isEmpty()) {
            Log::warning('⚠️ Mapel tidak ditemukan:', $decodedMapelList);
        }
        return Inertia::render('Students/absensiSiswaJanuari', [
            'kelas' => $classId,
            'year' => $year,
            'mapel' => $decodedMapelList,
            'month' => $month,
            'semester' => $semester,
            'students' => $students,
            'selectedMapel' => $selectedMapel->toArray(),
            'teacherClass' => $teacherClassData,
        ]);
    }

    private function handleAbsensiByMonth($kelas, $year, $mapel, $month)
    {
        $decodedMapel = urldecode($mapel);
        $student = Student::where('class_id', $kelas)->get();

        $selectedMapel = Mapel::where('mapel', $decodedMapel)->first();
        if (!$selectedMapel) {
            $selectedMapel = (object) [
                'id' => null,
                'mapel' => '',
                'kode_mapel' => '',
            ];
        } else {
            $selectedMapel = (object) $selectedMapel->toArray();
        }

        return Inertia::render('Students/absensi' . ucfirst($month), [
            'kelas' => $kelas,
            'year' => $year,
            'bulan' => $month,
            'mapel' => $decodedMapel,
            'month' => $month,
            'students' => $student,
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

        \Log::info('Total Attendances:', ['total' => $attendances->total()]);
        \Log::info('Last Page:', ['last_page' => $attendances->lastPage()]);

        // Format attendances
        $formattedAttendances = [];
        foreach ($attendances as $attendance) {
            $studentId = $attendance->siswa_id;
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
        try {
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
                if (!isset($attendance['student_id'], $attendance['status_kehadiran'])) {
                    throw new \Exception("Data attendance tidak lengkap: " . json_encode($attendance));
                }

                return [
                    'tanggal_kehadiran' => $tanggal_kehadiran,
                    'student_id' => (int) $attendance['student_id'],
                    'status_kehadiran' => $attendance['status_kehadiran'],
                     'kelas' => optional(Student::find($attendance['student_id'])->class)->name,
                    'mapel' => $attendance['mapel'] ?? null, // jika kamu juga punya mapel
                ];
            }, $attendances);

            Attendance::insert($attendanceData);

            $attendances = Attendance::whereDate('tanggal_kehadiran', $tanggal_kehadiran)->get();

            return response()->json([
                'message' => 'Attendance saved successfully',
                'attendances' => $attendances,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Error saat menyimpan absensi:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan absensi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function storeAttendance(Request $request)
    {
        try {
            $validated = $request->validate([
                'siswa_id' => 'required|exists:students,id',
                'tanggal_kehadiran' => 'required|date',
                'status' => 'required|in:Hadir,Tidak Hadir,Izin,Sakit,Terlambat',
            ]);

            $attendance = Attendance::where('siswa_id', $validated['siswa_id'])
                ->whereDate('tanggal_kehadiran', $validated['tanggal_kehadiran'])
                ->first();

            if (!$attendance) {
                // Kalau record absensi belum ada, bisa buat baru
                $attendance = Attendance::create([
                    'siswa_id' => $validated['siswa_id'],
                    'tanggal_kehadiran' => $validated['tanggal_kehadiran'],
                    'status' => $validated['status'],
                ]);
            } else {
                // Kalau sudah ada, update statusnya
                $attendance->status = $validated['status'];
                $attendance->save();
            }

            return response()->json([
                'message' => 'Absensi berhasil diperbarui.',
                'attendance' => $attendance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memperbarui absensi.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function batchUpdate(Request $request)
    {
        $data = $request->input('data', []);

        foreach ($data as $entry) {
            \Log::info('Attendance entry:', (array) $entry);  // Pastikan log berupa array

            Attendance::updateOrCreate(
                [
                    'student_id' => $entry['siswa_id'],
                    'tanggal_kehadiran' => $entry['tanggal_kehadiran'],
                ],
                [
                    'status_kehadiran' => $entry['status'],
                    'mapel' => $entry['mapel'],

                ]
            );
        }

        return response()->json(['message' => 'Semua absensi berhasil disimpan.']);
    }

}

