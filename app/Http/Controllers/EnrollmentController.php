<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    
    public function membuatEnrollment()
    {
        // Ambil data siswa, mata pelajaran, dan enrollments
        $students = $this->uniqueByKey(Student::all(), 'id');
        $courses = $this->uniqueByKey(Mapel::all(), 'id_mapel');
        $enrollments = Enrollment::with(['student', 'course'])->get();
    
        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/Enrollment/membuatEnrollment', [
            'students' => $students,
            'courses' => $courses,
            'enrollments' => $enrollments, // Menyertakan data enrollments
        ]);
    }

    
    /**
     * Helper function untuk mendapatkan elemen unik berdasarkan key.
     */
    private function uniqueByKey($collection, $key)
    {
        return $collection->unique($key)->values();
    }
    
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'course_id' => 'required|exists:master_mapel,id_mapel',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'mark' => 'nullable|integer',
        ]);
        
        // Menyimpan data enrollment baru
        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'course_id' => $validated['course_id'],
            'enrollment_date' => $validated['enrollment_date'],
            'status' => $validated['status'],
            'mark' => $validated['mark'] ?? 0,  // Set default mark jika null
        ]);
        
        // Ambil data student dan course berdasarkan ID
        $student = Student::find($validated['student_id']);
        $course = Mapel::find($validated['course_id']);
    
        // Kembalikan response dengan data enrollment, student, dan course
        return response()->json([
            'student' => $student,
            'course' => $course,
            'enrollment' => $enrollment,
        ], 201);
    }
    
     
    public function getEnrollments()
    {
        // Ambil data enrollments dengan relasi
        $enrollments = Enrollment::with(['student', 'course'])->get();
     
        // Kembalikan data dalam format JSON
        return response()->json($enrollments);
    }
    
    // Mengambil data mapel berdasarkan id
    public function getMapelById($id)
    {
        // Query ke tabel master_mapel
        $mapel = DB::table('master_mapel')->find($id);

        // Kembalikan data mapel
        return response()->json($mapel);
    }
}
