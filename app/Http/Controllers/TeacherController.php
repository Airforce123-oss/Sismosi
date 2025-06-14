<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ClassesResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\BukuPenghubungResource; //Import BukuPenghubungResource
use App\Http\Requests\StoreStudentRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Tugas;
use App\Models\WaliKelas;
use App\Models\Classes;
use App\Models\AbsensiSiswa;
use App\Models\BukuPenghubung;
use App\Models\Teacher;
use App\Models\Mapel;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{  
    public function showAbsensi($kelas, $year, $mapel, $month)
    {
        Log::info("Received parameters: Year: $year, Mapel: $mapel, Kelas: $kelas, Month: $month");
    
        $mapelArray = explode(',', $mapel); // ubah jadi array mapel
        $validMapel = DB::table('master_mapel')->pluck('mapel')->toArray();
    
        // Validasi semua mapel harus valid
        foreach ($mapelArray as $m) {
            if (!in_array($m, $validMapel)) {
                return redirect()->route('studentsabsensiSiswaSatu');
            }
        }
    
        $validKelas = DB::table('classes')->pluck('id')->toArray();
        if (!in_array($kelas, $validKelas)) {
            return redirect()->route('absensiSiswaJanuari');
        }
    
        // Validasi kombinasi tahun + kelas + mapel
        $isValidCombination = DB::table('attendances')
            ->whereYear('tanggal_kehadiran', $year)
            ->where('kelas', $kelas)
            ->whereIn('mapel', $mapelArray)
            ->exists();
    
        if (!$isValidCombination) {
            return redirect()->route('absensiSiswaJanuari');
        }
    
        // Ambil data absensi
        $dataAbsensi = DB::table('attendances')
            ->whereYear('tanggal_kehadiran', $year)
            ->where('kelas', $kelas)
            ->whereIn('mapel', $mapelArray)
            ->get();
    
        return response()->json([
            'dataAbsensi' => $dataAbsensi,
            'year' => $year,
            'mapel' => $mapelArray,
            'kelas' => $kelas,
            'month' => $month,
        ]);
    }
    

    public function bukuPenghubung()
    {
        $classes_for_student = Classes::all();
        return inertia('Teachers/BukuPenghubung/bukuPenghubung', [
            'classes_for_student' => $classes_for_student,
        ]);
    }

        public function getAllTeachers()
    {
        return Teacher::with('masterMapel')->get();
    }

    public function membuatTugasSiswa()
    {
        $tugas = Tugas::with(['mapel', 'student', 'teacher', 'kelas'])
            ->paginate(5)
            ->appends(request()->query());

        $teachers = Teacher::with('masterMapel')->get()->map(function ($teacher) {
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

        $students = Student::all();

        $mapels = \DB::table('teacher_mapel')
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

        $classes_for_student = Classes::all();

        return Inertia::render('Teachers/TugasSiswa/membuatTugasSiswa', [
            'tugas' => [
                'data' => collect($tugas->items())->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'mapel' => $t->mapel,
                        'student' => $t->student,
                        'teacher' => $t->teacher,
                        'kelas' => $t->kelas,
                        'description' => $t->description, // ✅ ditambahkan di sini
                        'created_at' => $t->created_at,
                        'updated_at' => $t->updated_at,
                    ];
                }),
                'meta' => [
                    'current_page' => $tugas->currentPage(),
                    'from' => $tugas->firstItem(),
                    'to' => $tugas->lastItem(),
                    'last_page' => $tugas->lastPage(),
                    'per_page' => $tugas->perPage(),
                    'total' => $tugas->total(),
                ],
                'links' => [
                    'first' => $tugas->url(1),
                    'last' => $tugas->url($tugas->lastPage()),
                    'prev' => $tugas->previousPageUrl(),
                    'next' => $tugas->nextPageUrl(),
                ],
            ],
            'teachers' => $teachers,
            'students' => $students,
            'mapels' => $mapels,
            'classes_for_student' => $classes_for_student,
        ]);
    }
    public function createTugasSiswa(Request $request)
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:master_mapel,id',
            'description' => 'required|string|min:1',
            'class_id' => 'required|exists:classes,id',
            // Tidak terima teacher_id dari frontend (demi keamanan)
        ]);

        // ✅ Ambil data guru yang sedang login
        $teacher = Teacher::findOrFail(Auth::id());

        // ✅ Validasi: guru harus mengajar mapel tersebut
        $teachesMapel = $teacher->masterMapel()->where('mapel_id', $validated['mapel_id'])->exists();

        if (!$teachesMapel) {
            return response()->json([
                'message' => 'Anda tidak mengajar mata pelajaran tersebut.'
            ], 422);
        }

        // ✅ Simpan tugas
        $tugas = new Tugas();
        $tugas->mapel_id = $validated['mapel_id'];
        $tugas->description = $validated['description'];
        $tugas->teacher_id = $teacher->id;
        $tugas->class_id = $validated['class_id'];
        $tugas->save();

        return response()->json([
            'message' => 'Tugas berhasil ditambahkan.',
            'data' => $tugas->load(['mapel', 'teacher', 'kelas']),
            'classes_for_student' => Classes::all(),
            'courses' => $teacher->masterMapel, // ✅ tetap gunakan relasi masterMapel()
            'teacher_id' => $teacher->id,
        ], 201);
    }

    public function index(Request $request)
    {
        $teacherQuery = Teacher::query()->with(['class', 'masterMapel', 'user']);
    
        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);
    
        // Pagination
        //$wali_kelas = $teacherQuery->paginate(5)->appends($request->only('search'));
        //$wali_kelas = $teacherQuery->paginate(5)->appends($request->query());
        $wali_kelas = $teacherQuery->paginate(5)->appends($request->only('search'));

        //dd($wali_kelas);

        if ($request->wantsJson()) {
            return response()->json($wali_kelas);
        }
    
       //dd($wali_kelas->items());

        $itemsPerPage = $request->input('itemsPerPage', 20); // Default to 20 items per page
        $currentPage = $request->input('currentPage', 1); // Default to page 1
    
        // Ambil data classes yang relevan
        $classesQuery = Classes::query();
    
        // Ambil data master_mapel yang relevan
        $mapelQuery = Mapel::query();
        $mapelData = $mapelQuery->get();
    
        // Ambil data teacher berdasarkan ID jika diberikan dalam request
        $teacherId = $request->input('teacher_id'); // Ambil ID guru dari request jika ada
        $teacher = $teacherId ? Teacher::with('masterMapel')->find($teacherId) : null;
    
        //Cek apakah data `mapel` ditemukan
        // dd($mapelData);
    
        $classes_for_student = $classesQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only('search', 'itemsPerPage', 'currentPage'));
    

        return inertia('Teachers/index', [
            'wali_kelas' => TeacherResource::collection($wali_kelas),
            'search' => $request->input('search', ''),
            'classes_for_student' => $classes_for_student ?? [],
            'mapel' => $mapelData ?? [],
            'teacher' => $teacher ?? null,
            'meta' => [
                'total' => $wali_kelas->total(),
                'per_page' => $wali_kelas->perPage(),
                'current_page' => $wali_kelas->currentPage(),
                'last_page' => $wali_kelas->lastPage(),
                'links' => array_merge(
                    [[
                        'url' => $wali_kelas->url(1),
                        'label' => 'First',
                        'active' => $wali_kelas->currentPage() == 1,
                    ]],
                    collect(range(1, $wali_kelas->lastPage()))->map(function ($page) use ($wali_kelas) {
                        return [
                            'url' => $wali_kelas->url($page),
                            'label' => $page,
                            'active' => $wali_kelas->currentPage() == $page,
                        ];
                    })->toArray(),
                    [[
                        'url' => $wali_kelas->previousPageUrl(),
                        'label' => 'Previous',
                        'active' => $wali_kelas->previousPageUrl() !== null,
                    ],
                    [
                        'url' => $wali_kelas->nextPageUrl(),
                        'label' => 'Next',
                        'active' => $wali_kelas->nextPageUrl() !== null,
                    ],
                    [
                        'url' => $wali_kelas->url($wali_kelas->lastPage()),
                        'label' => 'Last',
                        'active' => $wali_kelas->currentPage() == $wali_kelas->lastPage(),
                    ]]
                ),
            ],
        ]);
    }
    

    public function indexApi(Request $request)
    {
        $teacherQuery = Teacher::query()->with('class');

        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);

        $teacherQuery->orderBy('id');

        // Pagination
        $teachers = $teacherQuery->paginate(20)->appends($request->only('search'));

        //return response()->json($teachers);
        return response()->json([
            'data' => $teachers->items(),  // Mengembalikan data guru sebagai array
        ]);
    }

    protected function applySearch(Builder $query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function showAbsensiSiswa(Request $request)  
{  
    // Ambil semua data mata pelajaran  
    $mapelList = Mapel::all(['id', 'mapel']); // Ambil semua data mata pelajaran  
  
    //Log::info('Data Mapel:', $mapelList->toArray());
    // Query untuk mengambil data kelas    
    $classesQuery = Classes::query();    
    
  
    // Terapkan filter pencarian jika ada  
    $this->applySearch($classesQuery, $request->search);  
  
    // Urutkan berdasarkan ID kelas    
    $classesQuery->orderBy('id');    
  
    // Pagination, dengan jumlah per halaman 20    
    $classes = $classesQuery->paginate(20)->appends($request->only('search'));     
    //Log::info('Classes Type:', [gettype($classes)]);

  
    // Kirim data ke komponen Vue  
    return response()->json([    
    'data' => $mapelList, // Return the mapelList in a mapel property    
    'classes' => $classes, // Return the paginated classes   
    ]);   
}  

public function storeAttendance(Request $request)
{
    Log::info('Request received Store Attendance teacherController:', $request->all()); 
    try {
        $validatedData = $request->validate([
            'siswa_id' => 'required|exists:students,id',
            'tanggal_kehadiran' => 'required|date',
            'status' => 'required|in:P,A,S,I'
        ]);

        $attendance = AbsensiSiswa::updateOrCreate(
            [
                'siswa_id' => $validatedData['siswa_id'],
                'tanggal_kehadiran' => $validatedData['tanggal_kehadiran'],
            ],
            [
                'status' => $validatedData['status'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil diperbarui.',
            'data' => $attendance,
        ]);
    } catch (\Exception $e) {
        Log::error('Error updating attendance:', ['error' => $e->getMessage()]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal memperbarui absensi.',
        ], 500);
    }
}


    public function create()
    {
        $classes = ClassesResource::collection(Classes::all());
        $mapelQuery = Mapel::query();
        $mapelData = $mapelQuery->get()->toArray();
    
        return inertia('Teachers/create', [
            'classes' => $classes,
           'mapels' => $mapelData,
        ]);
    }

    public function store(StoreTeacherRequest $request)
    {
        try {
            $data = $request->validated();

            dd(['Test dulu gak seh' => $data]);

    
            Teacher::create([
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'email' => $data['email'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'class_id' => $data['class_id'] ?? null,
            ]);
    
            return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating teacher:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create teacher.');
        }
    }
    
    public function edit(Teacher $teacher)
    {
        $classes = ClassesResource::collection(Classes::all());

        $mapelQuery = Mapel::query();
        $mapelData = $mapelQuery->get();
    
        return inertia('Teachers/edit', [
            'student' => StudentResource::make($teacher),
            'mapels' => $mapelData ?? [],
            'classes' => $classes,
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        try {
            $data = $request->validated();
    
            $teacher->update([
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'email' => $data['email'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'class_id' => $data['class_id'] ?? null,
            ]);
    
            return redirect()->route('teachers.index')->with('success', 'Teacher updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating teacher:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update teacher.');
        }
    }
    

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index');
    }

    public function show($id_kelas)
    {
        try {
            // Ambil data teacher berdasarkan ID kelas
            $teacher = Teacher::findOrFail($id_kelas);
            
            // Ambil data wali kelas yang terkait dengan teacher
            $waliKelas = $teacher->waliKelas;
            
            // Ambil data mapel yang terkait dengan teacher
            $mapels = $teacher->masterMapel; // Menggunakan relasi masterMapel
        
            // Kembalikan data ke vue dengan menggunakan Inertia
            return inertia('Teachers/show', [
                'teacher' => TeacherResource::make($teacher),
                'waliKelas' => $waliKelas,  // Kirimkan waliKelas ke frontend
                'mapels' => $mapels,    // Kirimkan mapels ke frontend
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }
    }
    
    
    // Menampilkan Buku Penghubung
    public function bukuPenghubungApi()
    {
        $books = BukuPenghubung::all();  // Fetch all books from the database
        return response()->json($books);
    }

    public function updateStudentDetail(Request $request, $id)
    {
        Log::info('Data diterima untuk update:', [
            'id' => $id,
            'request_data' => $request->all(),
        ]);
    
        try {
            // Proses update data siswa di database
            $student = Student::findOrFail($id);
            $student->update($request->all());
    
            return response()->json([
                'message' => 'Data siswa berhasil diperbarui!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating student:', [
                'error' => $e->getMessage(),
            ]);
    
            return response()->json([
                'error' => 'Gagal memperbarui data siswa.',
            ], 500);
        }
    }
    
    
    // Menyimpan Buku Penghubung baru
    public function storeBukuPenghubung(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'studentId' => 'required|numeric',
                'gender' => 'required|in:L,P',
                'class' => 'required|string|max:50',
                'parentName' => 'required|string|max:255',
                'address' => 'required|string',
            ]);
        
            BukuPenghubung::create($validated);
        
            return response()->json(['message' => 'Data berhasil disimpan'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => $e->errors(),
                'request_data' => $request->all()
            ], 422);
        } 
    }

    // Mengambil detail siswa
    public function showStudent($id)
    {
        try {
            $student = Student::findOrFail($id);  // Mengambil data siswa berdasarkan ID
            return inertia('Students/show', [
                'student' => StudentResource::make($student),  // Menggunakan StudentResource untuk format data
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }
    }
    public function getClassByTeacher(Request $request)
    {
        $id = $request->query('id');
        Log::info('📥 Semua query param: ' . json_encode($request->query()));
        Log::info('📥 Semua request param: ' . json_encode($request->all()));
        Log::info('Request received with ID: ' . $id);
    
        if (!$id) {
            return response()->json(['message' => 'Parameter ID tidak ditemukan'], 400);
        }
    
        $teacher = Teacher::with('class')->find($id); // pastikan relasi dimuat
    
        if ($teacher && $teacher->class) {
            return response()->json(['class' => $teacher->class]);
        } else {
            return response()->json(['message' => 'Tidak ada kelas terkait'], 404);
        }
    }
    
    public function getMapelByTeacherId(Request $request)
    {
        $teacherId = $request->input('teacher_id');

        if (!$teacherId) {
            return response()->json(['error' => 'teacher_id wajib dikirim'], 400);
        }

        $mapel = DB::table('teacher_mapel')
            ->join('master_mapel', 'teacher_mapel.mapel_id', '=', 'master_mapel.id')
            ->where('teacher_mapel.teacher_id', $teacherId)
            ->select('master_mapel.mapel', 'master_mapel.id as mapel_id') // gunakan 'mapel'
            ->get();

        return response()->json(['mapel' => $mapel]);
    }

    public function getJadwalByTeacher(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->back()->with('error', 'User belum login');
        }
        $teacherId = $user->id;

        $jadwalFlat = \App\Models\JadwalMataPelajaran::with([
                'mapel',
                'kelas',
                'guru',
                'kelas.waliKelas'
            ])
            ->where('guru_id', $teacherId)
            ->get()
            ->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'hari'       => $item->hari,
                    'jam_ke'     => $item->jam_ke,
                    'jam'        => $item->jam,
                    'mapel_id'   => $item->mapel_id,
                    'mapel_nama' => $item->mapel->mapel ?? '-',
                    'guru_id'    => $item->guru_id,
                    'guru_nama'  => $item->guru?->name ?? '-',
                    'kelas_id'   => $item->kelas_id,
                    'kelas_nama' => $item->kelas?->name ?? '-',
                    'wali_kelas' => optional(optional($item->kelas)->waliKelas)->name,
                    'tahun'      => $item->tahun,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });

        // Grouping per jam_ke
        $grouped = [];
        foreach ($jadwalFlat as $item) {
            $jamKe = $item['jam_ke'];
            if (!isset($grouped[$jamKe])) {
                $grouped[$jamKe] = [
                    'jam_ke' => $jamKe,
                    'jam' => $item['jam'],
                    'jadwal' => [],
                ];
            }
            $grouped[$jamKe]['jadwal'][$item['hari']] = $item;
        }

        // Pastikan semua hari ada (meski null)
        $days = ['senin','selasa','rabu','kamis','jumat','sabtu','minggu'];
        foreach ($grouped as &$slot) {
            foreach ($days as $day) {
                if (!isset($slot['jadwal'][$day])) {
                    $slot['jadwal'][$day] = null;
                }
            }
        }

        $schedule = array_values($grouped);

        return inertia('Teachers/JadwalMataPelajaran/index', [
            'schedule' => $schedule,
            'teacher_id' => $teacherId,
            'master_mapel' => Mapel::all(),
            'classes_for_student' => ['data' => Classes::all()],
            'kelas_id' => null,
            'teachers' => Teacher::all(),
        ]);
    }

    public function settingLaporanNilaiSiswa(Request $request)
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

        $allMapel = Mapel::all(['id', 'mapel']);

        $students = $studentsQuery->with('class', 'noInduk')->paginate(20);

        $kelasList = Classes::all(['id', 'name']);
        $tahunList = Student::select('tahun_pelajaran')->distinct()->pluck('tahun_pelajaran'); 
        $studentIds = collect($students->items())->pluck('id');
        // Ambil enrollments berdasarkan student_id yang sudah diambil dari pagination
        $enrollments = \App\Models\Enrollment::with(['mapel', 'student'])
        ->whereIn('student_id', $studentIds)
        ->get();
        
        $attendances = \App\Models\Attendance::whereIn('student_id', collect($students->items())->pluck('id'))->get();

        return inertia('Teachers/settingLaporanNilaiSiswa', [
            'students' => $students, // <-- data asli dari database
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
