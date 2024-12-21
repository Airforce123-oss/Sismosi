<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassesResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\StudentResource;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use App\Models\Teacher;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function absensiSiswa()
    {
        return inertia('Teachers/TugasSiswa/membuatTugasSiswa');
    }
    
    public function bukuPenghubung()
    {
        return inertia('Teachers/BukuPenghubung/bukuPenghubung');
    }

    public function membuatTugasSiswa()
    {
        return inertia('Teachers/absensiSiswa');
    }

    public function index(Request $request)
    {
        $teacherQuery = Teacher::query()->with('class');

        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);

        // Pagination
        $wali_kelas = $teacherQuery->paginate(60)->appends($request->only('search'));

        // Pastikan 'classes' diteruskan ke komponen Vue
        $classes = Classes::all();  // Ambil data classes yang relevan

        return inertia('Teachers/index', [
            'wali_kelas' => TeacherResource::collection($wali_kelas),
            'search' => $request->input('search', ''),
            'classes' => $classes,  // Kirim data classes ke vue
        ]);
    }

    public function indexApi(Request $request)
    {
        $teacherQuery = Teacher::query()->with('class');

        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);

        $teacherQuery->orderBy('id');

        // Pagination
        $teachers = $teacherQuery->paginate(5)->appends($request->only('search'));

        return response()->json($teachers);
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
}
