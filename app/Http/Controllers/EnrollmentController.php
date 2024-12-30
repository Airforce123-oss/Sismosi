<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Student;
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
        $courses = $this->uniqueByKey(Mapel::all(), 'id_mapel');

        // Ambil data enrollments dengan pagination dari request
        $page = $request->input('page', 1); // Default ke halaman 1 jika tidak ada
        $perPage = $request->input('per_page', 10); // Default ke 10 item per halaman jika tidak ada
    
        $enrollments = Enrollment::with(['student', 'course'])
            ->paginate($perPage, ['*'], 'page', $page);
    
        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/Enrollment/membuatEnrollment', [
            'students' => $students,
            'courses' => $courses,
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
        // Validasi input
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'mapel_id' => 'required|exists:master_mapel,id_mapel', // pastikan validasi menggunakan mapel_id
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'mark' => 'nullable|integer',
        ]);
        
        // Menyimpan data enrollment baru
        $enrollment = Enrollment::create([
            'student_id' => $validated['student_id'],
            'mapel_id' => $validated['mapel_id'], // Gunakan mapel_id, bukan course_id
            'enrollment_date' => $validated['enrollment_date'],
            'status' => $validated['status'],
            'mark' => $validated['mark'] ?? 0,
        ]);
        
        // Ambil data student dan course berdasarkan ID
        $student = Student::find($validated['student_id']);
        $course = Mapel::find($validated['mapel_id']); // Pastikan mapel_id digunakan di sini
        
        // Kembalikan response dengan data enrollment, student, dan course
        return response()->json([
            'student' => $student,
            'course' => $course,
            'enrollment' => $enrollment,
        ], 201);
    }
    
    public function getEnrollments(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        // Hitung total data dan halaman yang diperlukan
        $totalItems = Enrollment::count(); 
        $totalPages = ceil($totalItems / $perPage);  // Pastikan perhitungan ini benar
    
        // Ambil data sesuai halaman yang diminta
        $enrollments = Enrollment::with(['student', 'course'])
            ->skip(($page - 1) * $perPage)  // Mengatur OFFSET sesuai halaman
            ->take($perPage)
            ->get();
    
        // Kirimkan response dengan pagination yang benar
        return response()->json([
            'data' => $enrollments,
            'total' => $totalItems,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,  // Total halaman berdasarkan perhitungan yang benar
                'total_items' => $totalItems,
            ],
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
}
