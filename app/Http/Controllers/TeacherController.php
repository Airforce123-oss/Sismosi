<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassesResource;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\ReligionResource;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes; 
use App\Models\Teacher;
use App\Models\Religion;
use App\Models\Gender;
use App\Models\NoInduk;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;


class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $teacherQuery = Teacher::query()->with('class');

        // Apply search filter if present
        $this->applySearch($teacherQuery, $request->search);

        // Pagination
        $wali_kelas = $teacherQuery->paginate(60)->appends($request->only('search'));

        return inertia('Teachers/index', [
            'wali_kelas' => TeacherResource::collection($wali_kelas),
            'search' => $request->input('search', '')
        ]);
    } 

    protected function applySearch(Builder $query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }


    public function absensiSiswa()
    {
        return inertia('teachers/absensiSiswa');
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
        // Validate the request data
        $validated = $request->validated();
    
        // Store the teacher data
        Teacher::create($validated);
    
        // Redirect to the index page with a success message
        return redirect()->route('teachers.index')->with('success', 'Teacher created successfully.');
    }
    

    public function show($id)
    {
        $teacher = Teacher::find($id);

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Teacher not found');
        }

        return inertia('Teachers/show', [
            'teacher' => TeacherResource::make($teacher)
        ]);
    }
}
