<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassesResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\BukuPenghubungResource; // Import BukuPenghubungResource
use App\Http\Requests\StoreStudentRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use App\Models\BukuPenghubung;
use App\Models\Teacher;
use App\Models\Mapel;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{  
    public function showAbsensi($kelas, $year, $mapel, $month)              
    {              
        // Log parameter yang diterima              
        Log::info("Received parameters: Year: $year, Mapel: $mapel, Kelas: $kelas, Month: $month");              
            
        // Ambil daftar mata pelajaran dari database              
        $validMapel = DB::table('master_mapel')->pluck('mapel')->toArray();              
          
        // Periksa apakah mapel yang diterima valid              
        if (!in_array($mapel, $validMapel)) {              
            return redirect()->route('studentsabsensiSiswaSatu');              
        }              
          
        // Ambil daftar kelas dari database              
        $validKelas = DB::table('classes')->pluck('id')->toArray(); // Ambil ID kelas      
          
        // Periksa apakah kelas yang diterima valid              
        if (!in_array($kelas, $validKelas)) {              
            return redirect()->route('absensiSiswaJanuari');              
        }              
          
        // Memeriksa apakah kombinasi tahun, mapel, dan kelas ada dalam tabel attendances              
        $isValidCombination = DB::table('attendances')              
            ->whereYear('tanggal_kehadiran', $year)              
            ->where('kelas', $kelas)              
            ->where('mapel', $mapel)              
            ->exists();              
          
        if (!$isValidCombination) {              
            return redirect()->route('absensiSiswaJanuari');              
        }              
          
        // Ambil data absensi untuk ditampilkan              
        $dataAbsensi = DB::table('attendances')              
            ->whereYear('tanggal_kehadiran', $year)              
            ->where('kelas', $kelas)              
            ->where('mapel', $mapel)              
            ->get();              
          
    // Kembalikan data dalam format JSON untuk API      
        return response()->json([      
            'dataAbsensi' => $dataAbsensi,      
            'year' => $year,      
            'mapel' => $mapel,      
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

    public function membuatTugasSiswa()
    {
        return inertia('Teachers/TugasSiswa/membuatTugasSiswa');
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

    /*
        public function showAbsensiSiswa(Request $request)
    {
        // Ambil semua data mata pelajaran
        $mapelList = Mapel::all(['id', 'mapel']); // Ambil semua data mata pelajaran
        // Query untuk mengambil data kelas  
        $classesQuery = Classes::query();  
        // Terapkan filter pencarian jika ada
        $this->applySearch($classesQuery, $request->search);
        // Urutkan berdasarkan ID kelas  
        $classesQuery->orderBy('id');  
        // Pagination, dengan jumlah per halaman 10  
        $classes = $classesQuery->paginate(20)->appends($request->only('search'));    

        // Kirim data ke komponen Vue
        return response()->json([  
            'data' => $mapelList, // Return the mapelList in a data property  
            'classes' => $classes, // Return the paginated classes 
        ]); 
    }
    */

    public function showAbsensiSiswa(Request $request)  
{  
    // Ambil semua data mata pelajaran  
    $mapelList = Mapel::all(['id', 'mapel']); // Ambil semua data mata pelajaran  
  
    Log::info('Data Mapel:', $mapelList->toArray());
    // Query untuk mengambil data kelas    
    $classesQuery = Classes::query();    
    
  
    // Terapkan filter pencarian jika ada  
    $this->applySearch($classesQuery, $request->search);  
  
    // Urutkan berdasarkan ID kelas    
    $classesQuery->orderBy('id');    
  
    // Pagination, dengan jumlah per halaman 20    
    $classes = $classesQuery->paginate(20)->appends($request->only('search'));     
    Log::info('Classes Type:', [gettype($classes)]);

  
    // Kirim data ke komponen Vue  
    return response()->json([    
    'data' => $mapelList, // Return the mapelList in a mapel property    
    'classes' => $classes, // Return the paginated classes   
    ]);   
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
        $name = $request->input('name');
        Log::info('Request received with name: ' . $name);
    
        if (!$name) {
            Log::error('Name parameter is missing.');
            return response()->json(['message' => 'Parameter name tidak ditemukan'], 400);
        }
    
        $teacher = Teacher::where('name', $name)->first();
    
        if ($teacher) {
            Log::info('Teacher found: ' . $teacher->name);
            $kelas = $teacher->class;
            return response()->json(['class' => $kelas ? $kelas->name : 'Tidak ada kelas terkait']);
        } else {
            Log::warning('Teacher not found with name: ' . $name);
            return response()->json(['message' => 'Wali kelas tidak ditemukan'], 404);
        }
    }
    
    

}
