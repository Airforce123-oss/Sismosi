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
use Illuminate\Support\Facades\Log;


class TeacherController extends Controller
{

   

    public function index(Request $request)
    {
        $teacherQuery = Teacher::query();

        // Apply search filter if present
        //$this->applySearch($teacherQuery, $request->search);

        // Pagination
        $wali_kelas = $teacherQuery->paginate(10)->appends($request->only('search'));

        return inertia('Teachers/indexTeacher', [
            'wali_kelas' => TeacherResource::collection($wali_kelas),
            'search' => $request->input('search', '')
        ]);
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
        Log::info('Ini adalah pesan log sederhana.');

        $data = $request->validated();
        Log::info('Data yang diterima:', $data);
    
        $teacher = Teacher::create($data);
    
        Log::info('Data yang berhasil disimpan:', $teacher->toArray());

        dd($request->all()); // Periksa data yang dikirim
        Teacher::create($request->validated());

        return redirect()->route('teachers.indexTeacher');
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
