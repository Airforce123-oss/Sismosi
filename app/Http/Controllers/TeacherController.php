<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassesResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\BukuPenghubungResource; // Import BukuPenghubungResource
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use App\Models\BukuPenghubung;
use App\Models\Teacher;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{  
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
        $teacherQuery = Teacher::query()->with('class');
    
        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);
    
        // Pagination
        $wali_kelas = $teacherQuery->paginate(20)->appends($request->only('search'));
        $itemsPerPage = $request->input('itemsPerPage', 20); // Default to 10 items per page

        $currentPage = $request->input('currentPage', 1); // Default
    
        // Ambil data classes yang relevan
        $classesQuery = Classes::query();

        

        $classes_for_student = $classesQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
        ->appends($request->only('search', 'itemsPerPage', 'currentPage'));
        
    
    
        return inertia('Teachers/index', [
            'wali_kelas' => TeacherResource::collection($wali_kelas),
            'search' => $request->input('search', ''),
            'classes_for_student' => $classes_for_student, // Kirim data classes ke vue
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

    public function create()
    {
        $classes = ClassesResource::collection(Classes::all());
        
        return inertia('Teachers/create', [
            'classes' => $classes,
        ]);
    }

    public function store(StoreTeacherRequest $request)
    {
        try {
            Teacher::create($request->validated());
            return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating teacher:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create teacher.');
        }
    }

    public function edit(Teacher $teacher)
    {
        $classes = ClassesResource::collection(Classes::all());
    
        return inertia('Teachers/edit', [
            'student' => StudentResource::make($teacher),
            'classes' => $classes,
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        $teacher->update($request->validated());

        return redirect()->route('teachers.index');
    }

    public function destroy(Teacher $teacher)
    {
        $teacher->delete();

        return redirect()->route('teachers.index');
    }

    public function show($id_kelas)
    {
        try {
            $teacher = Teacher::findOrFail($id_kelas);
            return inertia('Teachers/show', [
                'teacher' => TeacherResource::make($teacher)
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
