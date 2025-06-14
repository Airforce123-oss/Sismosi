<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Mapel;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\EnrollmentResource;


class EnrollmentController extends Controller
{
    public function membuatEnrollment(Request $request)
    {
        $user = auth()->user();
        $waliKelas = $user->waliKelas;

        if (!$waliKelas || !$waliKelas->classes) {
            return response()->json([
                'message' => 'Wali kelas tidak ditemukan atau tidak memiliki kelas yang diampu.'
            ], 404);
        }

        $classId = $request->query('class_id');

        if ($classId) {
            $kelas = Classes::with('students')->find($classId);

    if (!$kelas) {
        return response()->json([
            'message' => 'Kelas tidak ditemukan.'
        ], 404);
    }  

        } else {
            // fallback ke kelas default wali kelas
            $kelas = $waliKelas->classes;

            if (!$kelas) {
                return response()->json([
                    'message' => 'class_id harus disertakan atau wali kelas tidak memiliki kelas.'
                ], 400);
            }
        }


        // Ambil semua siswa dari kelas tersebut tanpa pagination
        $students = $kelas->students()
            ->with('class') 
            ->select('students.id', 'students.name', 'students.class_id')
            ->orderBy('students.id', 'asc')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada siswa yang terdaftar di kelas ini.'
            ], 404);
        }

        // Ambil data guru
        $teacher = Teacher::where('user_id', $user->id)->firstOrFail();

        // Ambil mapel yang diajar oleh guru ini
        $teacherCourses = $teacher->masterMapel()
            ->select('master_mapel.id as id', 'master_mapel.mapel as mapel')
            ->get();

        if ($teacherCourses->isEmpty()) {
            return response()->json([
                'message' => 'Guru ini belum memiliki mata pelajaran yang diajar.'
            ], 404);
        }

        // Ambil semua guru unik
        $teachers = $this->uniqueByKey(Teacher::all(), 'id');

        // Paginate enrollments milik guru login (tetap dipaginasi kalau kamu masih ingin ini)
        $enrollments = Enrollment::with(['student.class', 'mapel', 'teacher']) // ← tambahkan student.class
            ->where('teacher_id', $teacher->id)
            ->paginate(
                $request->input('per_page', 10),
                ['*'],
                'page',
                $request->input('page', 1)
            );

        return Inertia::render('Teachers/Enrollment/membuatEnrollment', [
            'students'    => $students, // Sudah tidak dipaginasi
            'courses'     => $teacherCourses,
            'teachers'    => $teachers,
            'enrollments' => [
            'data' => EnrollmentResource::collection($enrollments),
            'meta' => [
                'current_page' => $enrollments->currentPage(),
                'total_pages'  => $enrollments->lastPage(),
                'total_items'  => $enrollments->total(),
            ],
            'links' => [
                'first' => $enrollments->url(1),
                'last'  => $enrollments->url($enrollments->lastPage()),
                'prev'  => $enrollments->previousPageUrl(),
                'next'  => $enrollments->nextPageUrl(),
            ]
        ],
        ]);
    }

        private function uniqueByKey($collection, $key)
    {
        return $collection->unique($key)->values();
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
        $user = auth()->user();

        // Validasi input
        $validated = $request->validate([
            'student_local_id' => 'sometimes|exists:students,id',
            'mapel_id' => 'required|exists:master_mapel,id',
            'enrollment_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive',
            'class_id' => 'required|exists:classes,id',
        ]);

        $classId = $validated['class_id'];
        $kelas = Classes::with('students')->find($classId);

        if (!$kelas) {
            return response()->json([
                'message' => 'Kelas tidak ditemukan.'
            ], 404);
        }

        $mapelId = $validated['mapel_id'];
        $enrollmentDate = $validated['enrollment_date'] ?? now()->toDateString();
        $status = $validated['status'] ?? 'active';

        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            return response()->json([
                'message' => 'Guru tidak ditemukan di tabel teachers.'
            ], 422);
        }

        // =======================
        // ✅ MODE 1: Enroll satu siswa
        // =======================
        if (!empty($validated['student_local_id'])) {
            $studentId = $validated['student_local_id'];

            // Pastikan siswa memang bagian dari kelas tersebut
            $isStudentInClass = $kelas->students()->where('id', $studentId)->exists();
            if (!$isStudentInClass) {
                return response()->json([
                    'message' => 'Siswa tidak ditemukan dalam kelas yang dimaksud.'
                ], 403);
            }

            $exists = Enrollment::where('student_id', $studentId)
                ->where('mapel_id', $mapelId)
                ->where('enrollment_date', $enrollmentDate)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Siswa sudah pernah di-enroll pada mapel ini.'
                ], 409);
            }

            $enrollment = Enrollment::create([
                'student_id' => $studentId,
                'mapel_id' => $mapelId,
                'enrollment_date' => $enrollmentDate,
                'status' => $status,
                'teacher_id' => $teacher->id,
                'class_id' => $kelas->id,
            ]);

            $enrollment->load(['mapel', 'student', 'teacher']);

            return response()->json([
                'message' => 'Enrollment berhasil.',
                'data' => $enrollment,
            ], 201);
        }

        // =======================
        // ✅ MODE 2: Enroll semua siswa dalam kelas
        // =======================
        $students = $kelas->students;
        $createdEnrollments = [];

        foreach ($students as $student) {
            $exists = Enrollment::where('student_id', $student->id)
                ->where('mapel_id', $mapelId)
                ->where('enrollment_date', $enrollmentDate)
                ->exists();

            if (!$exists) {
                $created = Enrollment::create([
                    'student_id' => $student->id,
                    'mapel_id' => $mapelId,
                    'enrollment_date' => $enrollmentDate,
                    'status' => $status,
                    'teacher_id' => $teacher->id,
                    'class_id' => $kelas->id,
                ]);

                $createdEnrollments[] = $created;
            }
        }

        foreach ($createdEnrollments as $e) {
            $e->load(['mapel', 'student', 'teacher']);
        }

        return response()->json([
            'message' => 'Enrollment massal berhasil.',
            'data' => $createdEnrollments,
        ], 201);
    }

    public function updateEnrollment(Request $request)
    {
        $user = auth()->user();
        $teacher = $user->waliKelas;

        if (!$teacher || !$teacher->classes) {
            return response()->json(['message' => 'Guru tidak memiliki kelas'], 403);
        }

        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'mapel_id' => 'required|exists:master_mapel,id',
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

        // Pastikan siswa tersebut termasuk dalam kelas guru yang login
        $student = $teacher->classes->students()->where('id', $data['student_id'])->first();
        if (!$student) {
            return response()->json(['message' => 'Siswa bukan dari kelas yang diampu'], 403);
        }

        $enrollment = Enrollment::where('student_id', $data['student_id'])
            ->where('mapel_id', $data['mapel_id'])
            ->first();

        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment tidak ditemukan'], 404);
        }

        // Tambahkan teacher_id secara otomatis
        $data['teacher_id'] = $teacher->id;

        $enrollment->update($data);

        return response()->json([
            'message' => 'Enrollment berhasil diperbarui',
            'data' => $enrollment
        ]);
    }
        
        public function getEnrollments(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 5);

        // Mengambil data enrollments dengan paginasi
        $enrollments = Enrollment::with(['student.class', 'mapel', 'teacher'])
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
        $classId = $request->input('class_id'); // ✅ Ambil class_id dari request

        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.',
            ], 401);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json([
                'message' => 'Teacher not found.'
            ], 404);
        }

        $query = Enrollment::with(['student', 'mapel', 'teacher'])
            ->where('teacher_id', $teacher->id);

        // ✅ Tambahkan filter class_id jika tersedia
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        $enrollments = $query->paginate($perPage, ['*'], 'page', $page);

        Log::info('Total Enrollments for Teacher:', ['total' => $enrollments->total()]);
        Log::info('Teacher ID:', ['id' => $teacher->id]);

        return response()->json([
            'data' => $enrollments->items(),
            'total' => $enrollments->total(),
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'total_pages' => $enrollments->lastPage(),
                'total_items' => $enrollments->total(),
                'per_page' => $enrollments->perPage(),
            ],
            'links' => [
                'first' => $enrollments->url(1),
                'last' => $enrollments->url($enrollments->lastPage()),
                'prev' => $enrollments->previousPageUrl(),
                'next' => $enrollments->nextPageUrl(),
            ]
        ]);
    }


    public function getStudentsByClass(Request $request)
    {
        $classId = $request->query('class_id');

        if (!$classId) {
            return response()->json(['message' => 'Class ID wajib diisi.'], 400);
        }

        $class = Classes::find($classId);

        if (!$class) {
            return response()->json(['message' => 'Kelas tidak ditemukan.'], 404);
        }

        $students = $class->students()->select('id', 'name')->get();

        return response()->json(['data' => $students]);
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

        public function getOnlyClasses()
    {
        $classes = Classes::all();

        return response()->json([
            'data' => $classes,
        ]);
    }

}
