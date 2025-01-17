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
        try {
            $data = $request->validate([
                'student_id' => 'required|exists:students,id',
                'mapel_id' => 'required|exists:master_mapel,id',
                'enrollment_date' => 'required|date',
                'status' => 'required|in:active,inactive',
                'description' => 'nullable|string',
            ]);
    
            // Buat enrollment baru
            $enrollment = Enrollment::create($data);
    
            // Kembalikan data yang baru dibuat
            return response()->json($enrollment, 201);
    
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Error creating enrollment: ' . $e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
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

        Log::info('Total Enrollments:', ['total' => $enrollments->total()]);
        Log::info('Total Pages:', ['lastPage' => $enrollments->lastPage()]);


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
        //$mapel = DB::table('master_mapel')->find($id);
        $mapel = Mapel::find($id);


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
        // Log untuk mencatat data request yang diterima
        Log::info('Request data:', $request->all());
    
        try {
            // Validasi request
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'mapel_id' => 'required|exists:master_mapel,id',
                'status' => 'required|in:active,inactive',
                'noKd' => 'nullable|string',
                'cognitive1' => 'nullable|numeric',
                'cognitive2' => 'nullable|numeric',
                'cognitivePAS' => 'nullable|numeric',
                'cognitiveAverage' => 'nullable|numeric',
                'skill1' => 'nullable|numeric',
                'skill2' => 'nullable|numeric',
                'skillPAS' => 'nullable|numeric',
                'skillAverage' => 'nullable|numeric',
                'finalMark' => 'nullable|numeric',
                //'teacher_id' => 'required|exists:wali_kelas,id',
            ]);
    
            // Log data setelah validasi
            Log::info('Validated data:', $validated);
    
            // Cari data enrollment
            $enrollment = Enrollment::find($id);
    
            if (!$enrollment) {
                Log::warning("Enrollment with ID $id not found.");
                return response()->json(['message' => 'Enrollment not found'], 404);
            }
    
            // Menggunakan nilai default jika kolom nullable tidak diberikan
            $dataToUpdate = [
                'student_id' => $validated['student_id'],
                'mapel_id' => $validated['mapel_id'],
                'status' => $validated['status'],
                'no_kd' => $validated['noKd'] ?? '', // Default empty string jika tidak ada
                'cognitive_1' => $validated['cognitive1'] ?? 0, // Default 0 jika tidak ada
                'cognitive_2' => $validated['cognitive2'] ?? 0, // Default 0 jika tidak ada
                'cognitive_pas' => $validated['cognitivePAS'] ?? 0, // Default 0 jika tidak ada
                'cognitive_average' => $validated['cognitiveAverage'] ?? 0, // Default 0 jika tidak ada
                'skill_1' => $validated['skill1'] ?? 0, // Default 0 jika tidak ada
                'skill_2' => $validated['skill2'] ?? 0, // Default 0 jika tidak ada
                'skill_pas' => $validated['skillPAS'] ?? 0, // Default 0 jika tidak ada
                'skill_average' => $validated['skillAverage'] ?? 0, // Default 0 jika tidak ada
                'final_mark' => $validated['finalMark'] ?? 0, // Default 0 jika tidak ada
                //'teacher_id' => $validated['teacher_id'], // Jika diperlukan
            ];
    
            // Update data enrollment
            $enrollment->update($dataToUpdate);
    
            // Log data setelah berhasil diperbarui
            Log::info('Updated enrollment:', $enrollment->toArray());
    
            // Ambil data terkait
            $student = Student::find($validated['student_id']);
            $course = Mapel::find($validated['mapel_id']);
            //$waliKelas = Teacher::find($validated['wali_kelas_id']); // Model Teacher digunakan
    
            return response()->json([
                'student' => $student,
                'course' => $course,
                //'wali_kelas' => $waliKelas,
                'enrollment' => $enrollment,
            ], 200);
        } catch (\Exception $e) {
            // Log error jika terjadi kesalahan
            Log::error('Error updating enrollment:', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(), // Menampilkan stack trace untuk debug lebih lanjut
            ]);
    
            // Mengembalikan respon error
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    
    
}
