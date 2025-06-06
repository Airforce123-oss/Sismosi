<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use App\Traits\AbsensiSiswaTrait;
use App\Models\KomentarSiswa;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    use AbsensiSiswaTrait;
    public function parentDashboard()
    {
        return Inertia::render('Parents/parentsDashboard');
    }

    public function memeriksaTugasSubmit()
    {
        return Inertia::render('Parents/memeriksaTugasSubmit');

    }
    public function memberikanKomentarKepadaSiswa()
    {
        // Ambil siswa yang terkait dengan parent yang sedang login
        $parentId = auth()->id();
        $students = \App\Models\Student::with('komentarSiswas')
            ->where('parent_id', $parentId)
            ->get();

        $kelasList = \App\Models\Classes::pluck('name')->unique()->values();

        return Inertia::render ('Parents/memberikanKomentarKepadaSiswa',  [
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
        $kelasList = \App\Models\Classes::all(['id', 'name']);
        $tahunList = \App\Models\Student::select('tahun_pelajaran')->distinct()->pluck('tahun_pelajaran');
        $mapelList = \App\Models\Mapel::all(['id', 'mapel']);

        // Jika parameter filter belum diisi, kirim data filter saja
        if (!$classId || !$year || !$month || !$mapel) {
            return Inertia::render('Parents/melihatPresensiSiswa', [
                'kelasList' => $kelasList,
                'tahunList' => $tahunList,
                'mapelList' => $mapelList,
                'students' => [],
                'classId' => $classId,
                'year' => $year,
                'month' => $month,
                'mapel' => $mapel,
            ]);
        }

        // Ambil data siswa beserta absensi dan mapel
        $decodedMapel = urldecode($mapel);
        $decodedMapelList = explode(',', $decodedMapel);

        $students = \App\Models\Student::with([
            'mapel',
            'attendances' => function($q) use ($year, $month, $decodedMapelList) {
                if ($year) $q->whereYear('tanggal_kehadiran', $year);
                if ($month) $q->whereMonth('tanggal_kehadiran', $month);
                if (!empty($decodedMapelList)) {
                    // Case-insensitive filter
                    $lowerMapel = collect($decodedMapelList)->map(fn($m) => strtolower($m))->toArray();
                    $q->whereRaw('LOWER(mapel) IN ("'.implode('","', $lowerMapel).'")');
                }
            }
        ])
        ->where('class_id', $classId)
        ->get();

        // Map ke bentuk array yang siap dikonsumsi FE
        $studentsWithAttendance = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->name,
                'subject' => $student->mapel->mapel ?? '-',
                'attendances' => $student->attendances->map(function($a) {
                    return [
                        'id' => $a->id,
                        'tanggal' => $a->tanggal_kehadiran,
                        'status' => $a->status_kehadiran,
                        'mapel' => $a->mapel,
                    ];
                })->values(),
            ];
        });

        return Inertia::render('Parents/melihatPresensiSiswa', [
            'kelasList' => $kelasList,
            'tahunList' => $tahunList,
            'mapelList' => $mapelList,
            'students' => $studentsWithAttendance,
            'classId' => $classId,
            'year' => $year,
            'month' => $month,
            'mapel' => $mapel,
        ]);
    }
    public function melihatNilaiSiswa(Request $request)
    {
        // Ambil parent yang sedang login
        $parentId = auth()->id();

        // Ambil semua siswa yang terkait parent ini
        $studentsQuery = \App\Models\Student::where('parent_id', $parentId);

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

        $kelasList = \App\Models\Classes::all(['id', 'name']);
        $tahunList = \App\Models\Student::select('tahun_pelajaran')->distinct()->pluck('tahun_pelajaran');
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

}
