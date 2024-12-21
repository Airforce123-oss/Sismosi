<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClassesResource;
use App\Http\Resources\GenderResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\ReligionResource;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\NoIndukResource;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Classes; 
use App\Models\Section;
use App\Models\Religion;
use App\Models\Teacher;
use App\Models\Gender;
use App\Models\NoInduk;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{


    public function index(Request $request)
    {
        // Ambil data siswa dengan relasi
        $studentQuery = Student::query()->with('noInduk', 'religion', 'gender', 'class');
    
        // Terapkan filter pencarian jika ada
        $this->applySearch($studentQuery, $request->search);
        $studentQuery->orderBy('id');
        
        // Pagination
        $students = $studentQuery->paginate(5)->appends($request->only('search'));
    
        // Ambil data untuk classes, genders, no_induks, dan religions
        $classes = Classes::all();  // Ganti dengan model yang sesuai
        $genders = Gender::all();      // Ganti dengan model yang sesuai
        $noInduks = NoInduk::all();    // Ganti dengan model yang sesuai
        $religions = Religion::all();  // Ganti dengan model yang sesuai
    
        // Kirim data ke komponen Inertia
        return inertia('Students/index', [
            'students' => StudentResource::collection($students),
            'search' => $request->input('search', ''),
            'classes' => $classes,  // Kirim data classes
            'genders' => $genders,  // Kirim data genders
            'no_induks' => $noInduks, // Kirim data noInduks
            'religions' => $religions, // Kirim data religions
        ]);
    }
    

 

    public function melihatDataAbsensiSiswa(Request $request)
    {
        // Misalnya kita mendapatkan tanggal hari ini
        $currentDate = now()->format('Y-m-d'); // Format sesuai dengan yang Anda butuhkan
        
        // Ambil data absensi berdasarkan tanggal
        $attendanceRecords = Attendance::whereDate('tanggal_kehadiran', $currentDate)
            ->with('student') // Pastikan relasi dengan model Student ada
            ;
    
        return inertia('Students/melihatDataAbsensiSiswa', [
            'attendanceRecords' => $attendanceRecords,
            'currentDate' => $currentDate,
        ]);
    }

    public function indexApi(Request $request)
    {
        $studentQuery = Student::query()->with('noInduk', 'religion', 'gender', 'class');

        // Apply search filter if present
        $this->applySearch($studentQuery, $request->search);

        $studentQuery->orderBy('id');

        // Pagination
        $students = $studentQuery->paginate(5)->appends($request->only('search'));

        return response()->json($students);
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
        $genders = GenderResource::collection(Gender::all());
        $religions = ReligionResource::collection(Religion::all());
        $no_induks = NoIndukResource::collection(NoInduk::all());


        return inertia('Students/create', [
            'no_induks' => $no_induks,
            'classes' => $classes,
            'genders' => $genders,
            'religions' => $religions,
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        //Log::info('Data yang diterima:', $request->validated());
        
        //$student = Student::create($request->validated());
        
        //Log::info('Data yang berhasil disimpan:', $student->toArray());

        try {
            Student::create($request->validated());
            return redirect()->route('students.index')->with('success', 'Student created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating student:', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create student.');
        }
    }
    

    public function edit(Student $student)
    {
        $classes = ClassesResource::collection(Classes::all());
        $religions = ReligionResource::collection(Religion::all());
        $no_induks = NoIndukResource::collection(NoInduk::all());
        $genders = GenderResource::collection(Gender::all());


        return inertia('Students/edit', [
            'student' => StudentResource::make($student),
            'classes' => $classes,
            'religions' => $religions,
            'genders' => $genders,
            'no_induks' => $no_induks,
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student->update($request->validated());

        return redirect()->route('students.index');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index');
    }
    
    public function getSections(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $sections = Section::where('class_id', $classId)->get();

        return response()->json($sections);
    }

    public function getGender(Request $request)
    {
        $genderId = $request->query('gender_id');
        if (!$genderId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $genders = Gender::where('class_id', $genderId)->get();

        return response()->json($genders);
    }

    public function getReligion(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $religions = Religion::where('class_id', $classId)->get();

        return response()->json($religions);
    }

    public function getNoInduk(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $noInduks = NoInduk::where('class_id', $classId)->get();

        return response()->json($noInduks);
    }
    
    public function show($id)
    {
        $student = Student::find($id);

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Student not found');
        }

        return inertia('Students/show', [
            'student' => StudentResource::make($student)
        ]);
    }
}
