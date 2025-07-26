<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Str;
use App\Models\JadwalMataPelajaran;
use App\Models\Teacher;
use App\Models\Attendance;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class StudentRoleController extends Controller
{
    public function index()
    {
        $students = Student::all();
        return Inertia::render('Students/Index', [
            'students' => $students,
        ]);
    }

    public function create()
    {
        return Inertia::render('Students/Create');
    }


    /*
        public function store(Request $request)  
    {  
        $validatedData = $request->validate([  
            'name' => 'required|string|max:255',  
            'description' => 'nullable|string',  
        ]);  

        Student::create($validatedData);  

        return redirect()->route('student_roles.index')->with('success', 'Student role created successfully.');  
    }  

    */

    public function show(Student $student)
    {
        return Inertia::render('Students/Show', [
            'student' => $student,
        ]);
    }

    public function edit(Student $student)
    {
        return Inertia::render('Students/Edit', [
            'student' => $student,
        ]);
    }


    /*
        public function update(Request $request, Student $student)  
   {  
       $validatedData = $request->validate([  
           'name' => 'required|string|max:255',  
           'description' => 'nullable|string',  
       ]);  

       $student->update($validatedData);  

       return redirect()->route('student_roles.index')->with('success', 'Student role updated successfully.');  
   }  
    */

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('student_roles.index')->with('success', 'Student role deleted successfully.');
    }
    public function melihatTugas()
    {
        $student = $this->getStudentFromRequest();

        if (!$student || !$student->class_id) {
            return Inertia::render('Students/melihatTugasSiswa', [
                'student' => [
                    'id' => $student?->id ?? null,
                    'name' => $student?->name ?? '-',
                    'class' => $student?->class?->name ?? '-',
                ],
                'tugas' => [
                    'data' => [],
                    'meta' => [],
                    'links' => [],
                ],
                'teachers' => [],
                'students' => [],
                'mapels' => [],
                'classes_for_student' => [],
            ]);
        }

        $student->load('class');
        $tugas = $this->getTugasForStudent($student);
        $teachers = $this->getTeachers();
        $students = Student::all();
        $classes_for_student = Classes::all();
        $mapels = $this->getMapelOptions();

        return Inertia::render('Students/melihatTugasSiswa', [
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'class' => $student->class->name,
            ],
            'tugas' => $tugas,
            'teachers' => $teachers,
            'students' => $students,
            'mapels' => $mapels,
            'classes_for_student' => $classes_for_student,
        ]);
    }

    protected function getStudentFromRequest(): ?Student
    {
        $user = auth()->user();
        $isStudent = $user->role_type === 'siswa';

        return $isStudent
            ? $user->student
            : Student::with('class')->find(request('student_id'));
    }

    protected function getTugasForStudent(Student $student)
    {
        $tugas = Tugas::with(['mapel', 'student', 'teacher', 'kelas'])
            ->where('class_id', $student->class_id)
            ->orderByDesc('id')
            ->paginate(5)
            ->appends(request()->query());

        $formatted = collect($tugas->items())->values()->map(function ($t, $index) use ($tugas) {
            $currentPage = $tugas->currentPage();
            $perPage = $tugas->perPage();
            $no = ($currentPage - 1) * $perPage + $index + 1;

            return [
                'no' => $no,
                'id' => $t->id,
                'title' => $t->title,
                'mapel' => $t->mapel,
                'student' => $t->student,
                'teacher' => $t->teacher,
                'kelas' => $t->kelas,
                'description' => $t->description,
                'created_at' => $t->created_at,
                'updated_at' => $t->updated_at,
            ];
        });

        return [
            'data' => $formatted,
            'meta' => [
                'current_page' => $tugas->currentPage(),
                'from' => $tugas->firstItem(),
                'to' => $tugas->lastItem(),
                'last_page' => $tugas->lastPage(),
                'per_page' => $tugas->perPage(),
                'total' => $tugas->total(),
            ],
            'links' => $tugas->linkCollection(),
        ];
    }

    protected function getTeachers()
    {
        return Teacher::with('masterMapel')->get()->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'class_id' => $teacher->class_id,
                'masterMapel' => $teacher->masterMapel->map(function ($mapel) {
                    return [
                        'id' => $mapel->id,
                        'nama_mapel' => $mapel->mapel,
                    ];
                }),
            ];
        });
    }

    protected function getMapelOptions()
    {
        return DB::table('teacher_mapel')
            ->join('master_mapel', 'teacher_mapel.mapel_id', '=', 'master_mapel.id')
            ->join('teachers', 'teacher_mapel.teacher_id', '=', 'teachers.id')
            ->select(
                'teacher_mapel.id',
                'teacher_mapel.teacher_id',
                'teacher_mapel.mapel_id',
                'teacher_mapel.kode_mapel',
                'master_mapel.mapel as mapel_name',
                'teachers.name as teacher_name'
            )
            ->get();
    }

    public function melihatDataAbsensiSiswa(Request $request)
    {
        $studentId = $request->query('student_id');
        $month = $request->query('month');
        $year = $request->query('year');

        // Ambil data siswa
        if ($studentId) {
            $student = Student::with('class')->findOrFail($studentId);
        } else {
            $user = auth()->user();

            if (!$user || !$user->student) {
                return redirect()->route('login')->with('error', 'Sesi login tidak valid.');
            }

            $student = $user->student->load('class');
        }

        // Query absensi
        $query = Attendance::where('student_id', $student->id);

        // Tambahkan filter bulan dan tahun jika tersedia
        if ($month && $year) {
            $query->whereMonth('tanggal_kehadiran', $month)
                ->whereYear('tanggal_kehadiran', $year);
        }

        $attendanceData = $query->orderByDesc('tanggal_kehadiran')
                                ->paginate(5)
                                ->withQueryString();

        // Ambil list mapel (optional)
        $subjects = Attendance::where('student_id', $student->id)
            ->pluck('mapel')
            ->filter()
            ->unique()
            ->values();

        return Inertia::render('Students/melihatDataAbsensiSiswa', [
            'attendanceData' => $attendanceData,
            'student' => $student,
            'subjects' => $subjects,
        ]);
    }

    public function melihatJadwalPelajaran(Request $request)
    {
        $studentId = $request->query('student_id');

        // Cari siswa berdasarkan ID dari query
        $student = Student::with('class')->find($studentId);

        if (!$student || !$student->class_id) {
            return back()->withErrors(['error' => 'Siswa atau kelas tidak ditemukan.']);
        }

        $classId = $student->class_id;

        // Ambil semua jadwal berdasarkan kelas siswa
        $schedule = JadwalMataPelajaran::with(['mapel', 'kelas'])
            ->where('kelas_id', $classId)
            ->get()
            ->groupBy('jam_ke')
            ->map(function ($group) use ($classId) {
                $jamKe = $group[0]->jam_ke;
                $jam = $group[0]->jam;

                $data = [
                    'jam_ke' => $jamKe,
                    'jam' => $jam,
                    'jadwal' => [],
                ];

                foreach ($group as $jadwal) {
                    $hari = Str::lower($jadwal->hari);
                    $data['jadwal'][$hari] = [
                        'mapel' => optional($jadwal->mapel)->mapel ?? '-',
                        'kelas' => optional($jadwal->kelas)->name ?? '-',
                        'wali_kelas' => Teacher::where('class_id', $classId)->value('name') ?? 'Tidak ada wali kelas',
                    ];
                }

                return $data;
            })
            ->values();

        return Inertia::render('Students/melihatJadwalPelajaran', [
            'schedule' => $schedule,
            'kelas' => optional($student->class)->name ?? '-',
            'wali_kelas' => Teacher::where('class_id', $classId)->value('name') ?? 'Tidak ada wali kelas',
            'student_name' => $student->name,
        ]);
    }
}
