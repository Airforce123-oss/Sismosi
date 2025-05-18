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
        // Ambil data guru yang login
        $user = auth()->user();
        
        // Ambil wali kelas yang diampu oleh guru
        $waliKelas = $user->waliKelas;

        Log::info('Wali Kelas:', ['waliKelas' => $waliKelas]);

        
        // Pastikan wali kelas ada dan memiliki kelas
        if (!$waliKelas || !$waliKelas->classes) {
            return response()->json([
                'message' => 'Wali kelas tidak ditemukan atau tidak memiliki kelas yang diampu.'
            ], 404);
        }
    
        // Ambil kelas yang diampu oleh wali kelas tersebut
        $classes = $waliKelas->classes;

        Log::info('Classes:', ['classes' => $classes]);

        
        // Pastikan kelas ditemukan dan memiliki siswa
        if (!$classes->students) {
            return response()->json([
                'message' => 'Tidak ada siswa yang terdaftar di kelas ini.'
            ], 404);
        }
    
        // Ambil data siswa yang terdaftar di kelas yang diampu oleh guru yang login
        $students = $classes->students;
        
        // Ambil data mata pelajaran (mapel)
        $courses = $this->uniqueByKey(Mapel::all(), 'id');
        
        // Ambil data guru (teachers)
        $teachers = $this->uniqueByKey(Teacher::all(), 'id');
        
        // Ambil data enrollments dengan pagination dari request
        $page = $request->input('page', 1); // Default ke halaman 1 jika tidak ada
        $perPage = $request->input('per_page', 10); // Default ke 10 item per halaman jika tidak ada
        
        $enrollments = Enrollment::with(['student', 'mapel', 'teacher'])  // Menambahkan relasi teacher
            ->paginate($perPage, ['*'], 'page', $page);
    
        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/Enrollment/membuatEnrollment', [
            'students' => $students, // Siswa yang diambil berdasarkan kelas yang diampu oleh guru yang login
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
    
    
    public function getMarks(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        try {
            $marks = Enrollment::select(
                    'id',
                    'student_id',
                    'mapel_id',
                    'cognitive_1',
                    'cognitive_2',
                    'cognitive_pas',
                    'cognitive_average',
                    'skill_1',
                    'skill_2',
                    'skill_pas',
                    'skill_average',
                    'final_mark'
                )
                ->with(['student', 'mapel'])
                ->paginate($perPage, ['*'], 'page', $page);
    
            // Log berhasil
            Log::info('Successfully fetched marks data', [
                'total_items' => $marks->total(),
                'current_page' => $marks->currentPage()
            ]);
    
            return response()->json([
                'data' => $marks->items(),
                'pagination' => [
                    'current_page' => $marks->currentPage(),
                    'total_pages' => $marks->lastPage(),
                    'total_items' => $marks->total(),
                ],
            ]);
        } catch (\Exception $e) {
            // Log error
            Log::error('Error fetching marks data', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString()
            ]);
    
            return response()->json(['error' => 'Failed to fetch marks data'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validasi data yang dikirim dari frontend
            $data = $request->validate([
                'student_id' => 'required|exists:students,id',
                'mapel_id' => 'required|exists:master_mapel,id',
                'teacher_id' => 'required|exists:teachers,id',
                'enrollment_date' => 'required|date',
                'status' => 'required|in:active,inactive',
                'description' => 'nullable|string',
                'no_kd' => 'nullable|string',
                'cognitive_1' => 'nullable|numeric',
                'cognitive_2' => 'nullable|numeric',
                'cognitive_pas' => 'nullable|numeric',
                'cognitive_average' => 'nullable|numeric',
                'skill_1' => 'nullable|numeric',
                'skill_2' => 'nullable|numeric',
                'skill_pas' => 'nullable|numeric',
                'skill_average' => 'nullable|numeric',
                'final_mark' => 'nullable|numeric',
            ]);
    
            // Jika nilai cognitive_average tidak dikirim, hitung dari cognitive_1, cognitive_2, cognitive_pas
            if (!isset($data['cognitive_average']) && (isset($data['cognitive_1']) || isset($data['cognitive_2']) || isset($data['cognitive_pas']))) {
                $data['cognitive_average'] = round(
                    ( ($data['cognitive_1'] ?? 0) + ($data['cognitive_2'] ?? 0) + ($data['cognitive_pas'] ?? 0) ) / 3,
                    2
                );
            }
    
            // Jika nilai skill_average tidak dikirim, hitung dari skill_1, skill_2, skill_pas
            if (!isset($data['skill_average']) && (isset($data['skill_1']) || isset($data['skill_2']) || isset($data['skill_pas']))) {
                $data['skill_average'] = round(
                    ( ($data['skill_1'] ?? 0) + ($data['skill_2'] ?? 0) + ($data['skill_pas'] ?? 0) ) / 3,
                    2
                );
            }
    
            // Jika final_mark tidak dikirim, hitung rata-rata dari cognitive_average dan skill_average
            if (!isset($data['final_mark']) && isset($data['cognitive_average']) && isset($data['skill_average'])) {
                $data['final_mark'] = round(
                    ( $data['cognitive_average'] + $data['skill_average'] ) / 2,
                    2
                );
            }
    
            // Simpan ke database
            $enrollment = Enrollment::create($data);
            $enrollment->load(['student', 'mapel', 'teacher']);

            Log::info('Enrollment Data:', $request->all());
    
            return response()->json([
                'message' => 'Enrollment created successfully',
                'data' => $enrollment
            ], 201);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creating enrollment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Internal Server Error'
            ], 500);
        }
    }

    public function updateEnrollment(Request $request)
    {
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'mapel_id' => 'required|exists:master_mapel,id',
            'teacher_id' => 'required|exists:teachers,id',
            'enrollment_date' => 'required|date',
            'status' => 'required|in:active,inactive',
            'cognitive_1' => 'nullable|numeric',
            'cognitive_2' => 'nullable|numeric',
            'cognitive_pas' => 'nullable|numeric',
            'cognitive_average' => 'nullable|numeric',
            'skill_1' => 'nullable|numeric',
            'skill_2' => 'nullable|numeric',
            'skill_pas' => 'nullable|numeric',
            'skill_average' => 'nullable|numeric',
            'final_mark' => 'nullable|numeric',
            'no_kd' => 'nullable|string',
        ]);
        
    
        // Misal Anda ingin cari berdasarkan student_id + mapel_id
        $enrollment = Enrollment::where('student_id', $data['student_id'])
            ->where('mapel_id', $data['mapel_id'])
            ->first();
    
        if (!$enrollment) {
            return response()->json([
                'message' => 'Enrollment not found'
            ], 404);
        }
    
        $enrollment->update($data);
    
        return response()->json([
            'message' => 'Enrollment updated successfully',
            'data' => $enrollment
        ]);
    }
    
    
    public function getEnrollments(Request $request)
{
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 5);

    // Mengambil data enrollments dengan paginasi
    $enrollments = Enrollment::with(['student', 'mapel', 'teacher'])
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
    $perPage = $request->input('per_page', 5);
    
    $enrollments = Enrollment::with(['student', 'mapel', 'teacher'])
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
    public function getMapelByTeacherId(Request $request)
    {
        $teacherId = $request->input('id');
        $teacher = Teacher::with('mapel')->find($teacherId); // Ambil data guru beserta mapel terkait
    
        if ($teacher && $teacher->mapel) {
            return response()->json(['mapel' => $teacher->mapel->mapel]); // Mengirim nama mapel
        } else {
            return response()->json(['message' => 'Mapel tidak ditemukan'], 404);
        }
    }
    // Menambahkan show method untuk mengambil data enrollment berdasarkan id
    public function show($enrollmentId)
    {
        Log::info("Memulai pencarian enrollment", [
            'requested_id' => $enrollmentId
        ]);
    
        $enrollment = Enrollment::with('student')->find($enrollmentId);
    
        if (!$enrollment) {
            Log::warning("Enrollment tidak ditemukan", [
                'enrollment_id' => $enrollmentId,
                'total_enrollments' => Enrollment::count(),
                'ids_terkait' => Enrollment::pluck('id')->toArray(),
            ]);
            return response()->json(['error' => 'Enrollment not found'], 404);
        }
    
        Log::info("Enrollment ditemukan", [
            'enrollment_id' => $enrollment->id,
            'student_name' => optional($enrollment->student)->name,
        ]);
    
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
                'enrollment_date'   => 'required|date',
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
                'mapel' => $course,
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
