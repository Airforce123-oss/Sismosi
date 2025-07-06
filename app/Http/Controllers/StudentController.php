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
use App\Helpers\StudentHelper;
use App\Models\DetailStudent;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Religion;
use Inertia\Inertia;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
use App\Models\Gender;
use App\Models\NoInduk;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;


class StudentController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data siswa dengan relasi
        $studentQuery = Student::query()->with(['noInduk', 'religion', 'gender', 'class', 'attendances']);

        // Terapkan filter pencarian jika ada
        $this->applySearch($studentQuery, $request->search);
        $studentQuery->orderBy('id');

        // Pagination
        $students = $studentQuery->paginate(5)->appends($request->only('search'));

        // Ambil data untuk classes, genders, no_induks, dan religions
        $classes = Classes::all(); 
        $genders = Gender::all();   
        $noInduks = NoInduk::all();   
        $religions = Religion::all();  

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
    public function fetchAllStudents()
    {
        try {
            // Ambil semua data siswa dengan relasi (tanpa filter, tanpa pagination)
            $students = Student::with(['noInduk', 'religion', 'gender', 'class', 'attendances'])
                ->orderBy('id')
                ->get();

            // Ambil data lain seperti di index()
            $classes = Classes::all();
            $genders = Gender::all();
            $noInduks = NoInduk::all();
            $religions = Religion::all();

            // Kirim dalam bentuk JSON (tanpa info user/roles karena public)
            return response()->json([
                'students' => StudentResource::collection($students),
                'classes' => $classes,
                'genders' => $genders,
                'no_induks' => $noInduks,
                'religions' => $religions
            ]);
        } catch (\Exception $e) {
            // Kembalikan error dalam bentuk JSON untuk debugging
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }


    public function getLoggedInStudent(Request $request)
    {
        $user = Auth::user();



        if (!$user || $user->role_name !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ambil student yang berelasi dengan user
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json([
            'id' => $student->id,
            'name' => $student->name,
        ]);
    }
    public function melihatDataAbsensiSiswa(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->roles->first()?->name;
        if ($role !== 'student') {
            return Inertia::render('ErrorPage', [
                'message' => 'Hanya siswa yang dapat melihat data absensi.',
            ]);
        }

        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return Inertia::render('ErrorPage', [
                'message' => 'Data siswa tidak ditemukan.',
            ]);
        }

        $studentId = $student->id;
        $studentName = $student->name;
        $currentDate = now()->format('Y-m-d');

        // ✅ Ambil semua absensi siswa hari ini beserta course-nya
        $attendanceRecords = Attendance::whereDate('tanggal_kehadiran', $currentDate)
            ->where('student_id', $studentId)
            ->with(['course', 'student'])
            ->get();

        // ✅ Ambil semua mapel dari attendance
        $subjects = $attendanceRecords
            ->pluck('course')
            ->filter() // hilangkan null
            ->unique('id') // hanya mapel unik
            ->values(); // reset index
            

        return Inertia::render('Students/melihatDataAbsensiSiswa', [
            'attendanceRecords' => $attendanceRecords,
            'currentDate' => $currentDate,
            'student_id' => $studentId,
            'student_name' => $studentName,
            'student' => $student,
            'subjects' => $subjects, 
        ]);
    }

    public function indexApi(Request $request)
    {
        $studentQuery = Student::query()->with('noInduk', 'religion', 'gender', 'class');
        $this->applySearch($studentQuery, $request->search);
        $detailStudents = $studentQuery->paginate(5)->appends($request->only('search'));

        // Query untuk data yang digunakan di modal (students)

        $studentQuery->orderBy('id');

        // Pagination
        $students = $studentQuery->paginate(5)->appends($request->only('search'));

        return response()->json($students);
    }

    public function indexDetailStudentApi(Request $request)
    {
        // Query untuk DetailStudent dengan relasi yang sesuai
        $detailStudentQuery = DetailStudent::query()->with('student'); // Jika DetailStudent punya relasi ke student
        $this->applySearch($detailStudentQuery, $request->search);

        // Pagination untuk detail students
        $detailStudents = $detailStudentQuery->paginate(10)->appends($request->only('search'));

        return response()->json($detailStudents);
    }




    protected function applySearch(Builder $query, $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        });
    }

    public function create(Request $request)
    {
        $no_induk_id = null;
        $nomor_induk = null;

        if ($request->filled('student_id')) {
            $student = Student::with('noInduk')->find($request->student_id);
            if ($student && $student->noInduk) {
                $no_induk_id = $student->no_induk_id;
                $nomor_induk = $student->noInduk->no_induk; // Ambil nilai no_induk dari relasi
            }
        }

        return inertia('Students/create', [
            'no_induk_id' => $no_induk_id,
            'nomor_induk' => $nomor_induk,
            'classes' => ClassesResource::collection(Classes::all()),
            'genders' => GenderResource::collection(Gender::all()),
            'religions' => ReligionResource::collection(Religion::all()),
        ]);
    }

    public function store(StoreStudentRequest $request)
    {
        // Log awal: semua input masuk
        StudentHelper::logReceivedData($request->all());

        try {
            $validated = StudentHelper::validateStudentData($request);

            // Log hasil validasi
            Log::info('Data tervalidasi:', $validated);

            // Cek apakah perlu membuat NoInduk baru
            if (empty($validated['no_induk_id'])) {
                Log::info('Membuat NoInduk baru dengan no_induk:', [
                    'no_induk' => $validated['no_induk'] ?? null,
                ]);

                $noInduk = NoInduk::create([
                    'no_induk' => $validated['no_induk'], // wajib sudah tervalidasi
                ]);

                $validated['no_induk_id'] = $noInduk->id;
            }

            // Jangan ikut simpan ke table students
            unset($validated['no_induk']);

            // Simpan ke tabel students
            $student = Student::create($validated);

            Log::info('Siswa berhasil disimpan:', $student->toArray());

            return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan siswa', [
                'request_data' => $request->all(),
                'validated_data' => $validated ?? [],
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Gagal menambahkan siswa. Detail kesalahan telah dicatat.');
        }
    }

    public function edit(Student $student)
    {
        // Eager load relasi
        $student->load(['class', 'gender', 'religion', 'noInduk']);

        // Ambil data relasi yang diperlukan
        $classes = Classes::select(['id', 'name'])->get();
        $genders = Gender::select(['id', 'name'])->get();
        $religions = Religion::select(['id', 'name'])->get();
        $no_induks = NoInduk::select(['id', 'no_induk'])->get();

        return inertia('Students/edit', [
            'student' => $student,
            'classes' => ['data' => $classes],
            'genders' => ['data' => $genders],
            'religions' => ['data' => $religions],
            'no_induks' => ['data' => $no_induks], // <- disamakan dengan struktur lainnya
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student)
    { 
        try {
            // Validasi dan update data
            $student->update($request->validated());

            // Redirect ke index dengan pesan sukses
            return redirect()->route('students.index')
                ->with('success', 'Data siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            \Log::error('Gagal memperbarui siswa', ['error' => $e->getMessage()]);

            // Redirect kembali dengan pesan error
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui data siswa.');
        }
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

    public function showStudent($id)
    {
        try {
            // Ambil data siswa berdasarkan ID dari tabel 'detailstudents'
            $student = DetailStudent::findOrFail($id);  // Menggunakan findOrFail agar memunculkan error jika ID tidak ditemukan

            return response()->json([
                'student' => $student,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Siswa tidak ditemukan',
            ], 404);  // Status 404 jika siswa tidak ditemukan
        }
    }

    public function show()
    {
        $student = auth()->user();

        // Cek role manual, misal kolom 'role'
        logger('User role:', ['role' => $student->role]);

        return response()->json($student);
    }

    public function getByNomorInduk($nomor_induk)
    {
        $student = Student::whereHas('noInduk', function ($query) use ($nomor_induk) {
            $query->where('no_induk', $nomor_induk);
        })->with('noInduk')->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json($student);
    }

    public function indexApiDetailStudent(Request $request)
    {
        // Ambil data dari tabel detailstudents, misalnya dengan pagination
        $students = DetailStudent::paginate(10);  // Sesuaikan pagination sesuai kebutuhan

        return response()->json($students);
    }

public function storeStudent(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'student_id' => 'required|string|unique:students,student_id|max:255',
        // Hapus validasi no_induk_id jika tidak digunakan lagi
        'class_id' => 'required|exists:classes,id',
        'gender_id' => 'required|exists:genders,id',
        'religion_id' => 'required|exists:religions,id',
    ]);

    try {
        $student = Student::create([
            'name' => $validated['name'],
            'student_id' => $validated['student_id'], // ← sekarang ini dianggap sebagai NIS
            'class_id' => $validated['class_id'],
            'gender_id' => $validated['gender_id'],
            'religion_id' => $validated['religion_id'],
            // Jangan isi no_induk_id kalau tidak dipakai
        ]);

        return response()->json([
            'message' => 'Siswa berhasil ditambahkan!',
            'student' => $student,
        ], 201);
    } catch (\Exception $e) {
        Log::error('Error creating student:', ['error' => $e->getMessage()]);
        return response()->json([
            'error' => 'Terjadi kesalahan saat menyimpan data siswa',
            'detail' => $e->getMessage(),
        ], 500);
    }
}


}
