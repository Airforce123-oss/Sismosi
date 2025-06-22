<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Traits\AbsensiSiswaTrait;
use App\Models\KomentarSiswa;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use App\Models\Tugas;
use Illuminate\Pagination\LengthAwarePaginator;

class ParentController extends Controller
{
    use AbsensiSiswaTrait;
    public function parentDashboard()
    {
        return Inertia::render('Parents/parentsDashboard');
    }
    public function memeriksaTugasSubmit()
    {
        $parent = Auth::user();

        $student = Student::with('class')->where('parent_id', $parent->id)->first();

        if (!$student) {
            abort(403, 'Siswa tidak ditemukan.');
        }

        $kelasList = Classes::all();
        $selectedClassId = request()->query('class_id');
        $selectedClassName = optional($kelasList->firstWhere('id', $selectedClassId))->name;

        $query = Tugas::with(['mapel', 'teacher', 'kelas']);

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        $tugas = $query->paginate(5)->appends(request()->query());

        $tugas->setCollection(
            $tugas->getCollection()->map(function ($t) {
                return [
                    'id' => $t->id,
                    'mapel' => $t->mapel,
                    'teacher' => $t->teacher,
                    'kelas' => $t->kelas,
                    'description' => $t->description,
                    'created_at' => $t->created_at,
                    'updated_at' => $t->updated_at,
                ];
            })
        );
        return Inertia::render('Parents/memeriksaTugasSubmit', [
            'tugas' => [
                'data' => $tugas->items(), // atau $tugas->toArray()['data']
                'meta' => [
                    'current_page' => $tugas->currentPage(),
                    'from' => $tugas->firstItem(),
                    'to' => $tugas->lastItem(),
                    'last_page' => $tugas->lastPage(),
                    'per_page' => $tugas->perPage(),
                    'total' => $tugas->total(),
                ],
                'links' => $tugas->linkCollection()->toArray(),
            ],
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'kelas_id' => $student->class_id,
                'class' => $student->class,
            ],
            'kelasList' => $kelasList,
            'selectedClassId' => $selectedClassId,
            'selectedClassName' => $selectedClassName,
        ]);

    }

    public function memberikanKomentarKepadaSiswa()
    {
        // Ambil siswa yang terkait dengan parent yang sedang login
        $parentId = auth()->id();
        $students = Student::with('komentarSiswas')
            ->where('parent_id', $parentId)
            ->get();

        $kelasList = Classes::pluck('name')->unique()->values();

        return Inertia::render('Parents/memberikanKomentarKepadaSiswa', [
            'students' => $students,
            'kelasList' => $kelasList,
        ]);
    }

    public function storeKomentarSiswa(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'komentar' => 'required|string|max:255',
        ]);

        KomentarSiswa::create([
            'student_id' => $request->student_id,
            'komentar' => $request->komentar,
            'parent_id' => auth()->id(), // jika ingin simpan parent yang memberi komentar
        ]);

        return response()->json(['message' => 'Komentar berhasil disimpan']);
    }
    public function melihatPresensiSiswa(Request $request)
    {
        $classId = $request->input('class_id');
        $year = $request->input('year');
        $month = $request->input('month');
        $mapel = $request->input('mapel');

        // Ambil data filter dari database
        $kelasList = Classes::all(['id', 'name']);
        $tahunList = Student::select('tahun_pelajaran')->distinct()->pluck('tahun_pelajaran');
        $mapelList = \App\Models\Mapel::all(['id', 'mapel']);

        // Jika parameter filter belum lengkap, buat paginasi kosong
        if (!$classId || !$year || !$month || !$mapel) {
            $emptyPaginator = new LengthAwarePaginator(
                new Collection(), // data kosong
                0,               // total data
                10,              // per halaman (sesuai paginate default kamu)
                1,               // halaman sekarang
                ['path' => url()->current()] // base url
            );

            return Inertia::render('Parents/melihatPresensiSiswa', [
                'kelasList' => $kelasList,
                'tahunList' => $tahunList,
                'mapelList' => $mapelList,
                'students' => $emptyPaginator, // objek paginasi kosong
                'classId' => $classId,
                'year' => $year,
                'month' => $month,
                'mapel' => $mapel,
            ]);
        }

        // Decode mapel yang dipisah koma
        $decodedMapel = urldecode($mapel);
        $decodedMapelList = explode(',', $decodedMapel);

        /** @var LengthAwarePaginator $students */
        $students = Student::with([
            'mapel',
            'attendances' => fn($q) =>
                $q
                    ->when($year, fn($q) => $q->whereYear('tanggal_kehadiran', $year))
                    ->when($month, fn($q) => $q->whereMonth('tanggal_kehadiran', $month))
                    ->when(
                        !empty($decodedMapelList),
                        fn($q) =>
                        $q->whereRaw(
                            'LOWER(mapel) IN ("' . implode('","', collect($decodedMapelList)->map(fn($m) => strtolower($m))->toArray()) . '")'
                        )
                    )
        ])->where('class_id', $classId)->paginate(10);

        // Ubah koleksi data di paginator supaya formatnya sesuai frontend
        $students->setCollection(
            $students->getCollection()->transform(fn($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'subject' => $student->mapel->pluck('mapel')->implode(', ') ?: '-',
                'attendances' => $student->attendances->map(fn($a) => [
                    'id' => $a->id,
                    'tanggal' => $a->tanggal_kehadiran,
                    'status' => $a->status_kehadiran,
                    'mapel' => $a->mapel,
                ])->values(),
            ])
        );

        return Inertia::render('Parents/melihatPresensiSiswa', [
            'kelasList' => $kelasList,
            'tahunList' => $tahunList,
            'mapelList' => $mapelList,
            'students' => $students, // objek paginasi asli dengan data sudah transformasi
            'classId' => $classId,
            'year' => $year,
            'month' => $month,
            'mapel' => $mapel,
        ]);
    }

    public function melihatNilaiSiswa(Request $request)
    {
        $studentsQuery = Student::query();

        if ($request->filled('nama')) {
            $studentsQuery->where('name', 'like', '%' . $request->input('nama') . '%');
        }
        if ($request->filled('nomor_induk')) {
            $studentsQuery->where('nomor_induk', 'like', '%' . $request->input('nomor_induk') . '%');
        }
        if ($request->filled('tahun_pelajaran')) {
            $studentsQuery->where('tahun_pelajaran', $request->input('tahun_pelajaran'));
        }
        if ($request->filled('kelas')) {
            $studentsQuery->where('class_id', $request->input('kelas'));
        }

        $students = $studentsQuery->with('class', 'noInduk')->paginate(20);

        // ⬇️⬇️⬇️ TAMBAHKAN BAGIAN INI
        if ($request->wantsJson()) {
            return response()->json([
                'students' => $students,
            ]);
        }
        // ⬆️⬆️⬆️ TAMBAHKAN BAGIAN INI

        $kelasList = Classes::all(['id', 'name']);
        $tahunList = Student::select('tahun_pelajaran')->distinct()->pluck('tahun_pelajaran');
        $studentIds = collect($students->items())->pluck('id');

        $enrollments = \App\Models\Enrollment::with(['mapel', 'student'])
            ->whereIn('student_id', $studentIds)
            ->get();

        $attendances = \App\Models\Attendance::whereIn('student_id', $studentIds)->get();
        $allMapel = \App\Models\Mapel::all(['id', 'mapel']);

        return Inertia::render('Parents/melihatNilaiSiswa', [
            'students' => $students,
            'kelasList' => $kelasList,
            'tahunList' => $tahunList,
            'allMapel' => $allMapel,
            'enrollments' => $enrollments,
            'attendances' => $attendances,
            'filters' => [
                'nama' => $request->input('nama'),
                'nomor_induk' => $request->input('nomor_induk'),
                'tahun_pelajaran' => $request->input('tahun_pelajaran'),
                'kelas' => $request->input('kelas'),
            ],
        ]);
    }

    public function filterStudents(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $filters = $request->only(['nama', 'kelas']);

            $query = Student::where('parent_id', $user->id);

            if (!empty($filters['nama'])) {
                $query->where('name', 'like', '%' . $filters['nama'] . '%');
            }

            if (!empty($filters['kelas'])) {
                $query->where('class_id', $filters['kelas']);
            }

            $students = $query->with(['class', 'noInduk'])->paginate(20);

            return response()->json([
                'message' => 'FILTER OK',
                'students' => $students,
            ]);
        } catch (\Throwable $e) {
            // sementara tampilkan error-nya langsung agar bisa dilihat di browser
            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }


}
