<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentController extends Controller
{
    public function membuatEnrollment(Request $request)
    {
        // Ambil data siswa dan mata pelajaran
        $students = $this->uniqueByKey(Student::all(), 'id');
        $courses = $this->uniqueByKey(Mapel::all(), 'id');
        $teachers = $this->uniqueByKey(Teacher::all(), 'id');

        // Ambil data enrollments dengan pagination dari request
        $page = $request->input('page', 1); // Default ke halaman 1 jika tidak ada
        $perPage = $request->input('per_page', 10); // Default ke 10 item per halaman jika tidak ada
    
        $enrollments = Enrollment::with(['student', 'course', 'teacher'])  // Menambahkan relasi teacher
            ->paginate($perPage, ['*'], 'page', $page);
    
        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/Enrollment/membuatEnrollment', [
            'students' => $students,
            'courses' => $courses,
            'teachers' => $teachers,
            'enrollments' => $enrollments,
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'total_pages' => $enrollments->lastPage(),
                'total_items' => $enrollments->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'mapel_id' => 'required|exists:master_mapel,id',
            'teacher_id' => 'nullable|exists:teachers,id', // Menjadikan teacher_id nullable
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'mark' => 'nullable|integer',
            'description' => 'nullable|string',
        ]);
    
        // Cek jika teacher_id tidak ada, set null
        $teacher_id = $validated['teacher_id'] ?? null;
    
        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'mapel_id' => $validated['mapel_id'],
            'teacher_id' => $teacher_id, // Jika teacher_id null, maka tetap dikirim null
            'enrollment_date' => $validated['enrollment_date'],
            'status' => $validated['status'],
            'mark' => $validated['mark'] ?? 0,
            'description' => $validated['description'] ?? null,
        ]);
    
        // Mengambil data student dan course
        $student = Student::find($validated['student_id']);
        $course = Mapel::find($validated['mapel_id']);
        $teacher = $teacher_id ? Teacher::find($teacher_id) : null;
    
        return response()->json([
            'student' => $student,
            'course' => $course,
            'teacher' => $teacher,
            'enrollment' => $enrollment,
        ], 201);
    }

    public function getEnrollments(Request $request)
{
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 10);

    // Mengambil data enrollments dengan paginasi
    $enrollments = Enrollment::with(['student', 'course'])
        ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'data' => $enrollments->items(),
        'total' => $enrollments->total(),
        'pagination' => [
            'current_page' => $enrollments->currentPage(),
            'total_pages' => $enrollments->lastPage(),
            'total_items' => $enrollments->total(),
        ]
    ]);
}
    
    public function getPaginatedEnrollments(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        
        $enrollments = Enrollment::with(['student', 'course'])
            ->paginate($perPage, ['*'], 'page', $page);
    
        return response()->json([
            'data' => $enrollments->items(),
            'total' => $enrollments->total(),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'total_pages' => $enrollments->lastPage(),
                'total_items' => $enrollments->total(),
            ]
        ]);
    }

    

    // Mengambil data mapel berdasarkan id
    public function getMapelById($id)
    {
        // Query ke tabel master_mapel
        $mapel = DB::table('master_mapel')->find($id);

        // Kembalikan data mapel
        return response()->json($mapel);
    }

    // Menambahkan show method untuk mengambil data enrollment berdasarkan id
    public function show($enrollmentId)
    {
        $enrollment = Enrollment::where('id', $enrollmentId)->first(); // Mengambil data tanpa paginasi
    
        if (!$enrollment) {
            Log::error("Enrollment with ID {$enrollmentId} not found.");
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
    
        return response()->json($enrollment);
    }
    
    /**
     * Helper function untuk mendapatkan elemen unik berdasarkan key.
     */
    private function uniqueByKey($collection, $key)
    {
        return $collection->unique($key)->values();
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:master_mapel,id',
            'teacher_id' => 'nullable|string',
            'description' => 'nullable|string',
        ]);
    
        $enrollment = Enrollment::find($id);
    
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    
        // Perbarui data enrollment
        $enrollment->update($validated);
    
        return response()->json(['enrollment' => $enrollment], 200);
    }
    
    
}
