<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Teacher;
use App\Models\Tugas;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Log;

class TugasController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data guru dan mata pelajaran, pastikan data unik
        /*
              $courses = $this->uniqueByKey(Mapel::all(), 'id');
        $teachers = $this->uniqueByKey(Teacher::all(), 'id');
        $students = $this->uniqueByKey(Student::all(), 'id');
        */

        $courses = Mapel::distinct()->get();
        $teachers = Teacher::distinct()->get();
        $students = Student::distinct()->get();
        // Logging query untuk debugging, jika perlu
        $tugasQuery = Tugas::with(['mapel', 'teacher']);
        Log::info("Query SQL: " . $tugasQuery->toSql()); // Log query untuk debugging

        // Mengambil data tugas dengan pagination
        $page = $request->input('page', 1); // Halaman yang diminta
        $perPage = $request->input('per_page', 10); // Jumlah data per halaman

        // Periksa apakah parameter pagination valid
        $perPage = is_numeric($perPage) && $perPage > 0 ? (int) $perPage : 10;

        // Ambil data tugas dengan pagination
        $tugas = Tugas::with(['mapel', 'teacher', 'student'])
            ->paginate($perPage, ['*'], 'page', $page);

        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/TugasSiswa/membuatTugasSiswa', [
            'students' => $students,
            'courses' => $courses,
            'teachers' => $teachers,
            'tugas' => $tugas->items(), // Kirim data tugas per halaman
            'pagination' => [
                'current_page' => $tugas->currentPage(),
                'total_pages' => $tugas->lastPage(),
                'total_items' => $tugas->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $tugas = Tugas::with(['mapel', 'teacher'])->findOrFail($id);

        if (!$tugas) {
            Log::error("Tugas dengan ID {$id} tidak ditemukan.");
            return response()->json(['error' => 'Tugas tidak ditemukan'], 404);
        }

        return response()->json($tugas);
    }
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'mapel_id' => 'required|exists:master_mapel,id',
            'teacher_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value)) {
                        $fail("The $attribute must be a valid number.");
                    }
                },
                'exists:wali_kelas,id',
            ],
            'description' => 'required|string',
            'student_id' => 'required|exists:students,id',
            'title' => 'nullable|string|max:255',
        ]);

        // Simpan data tugas
        $tugas = Tugas::create([
            'student_id' => $validated['student_id'],
            'mapel_id' => $validated['mapel_id'],
            'teacher_id' => $validated['teacher_id'],
            'description' => $validated['description'],
            'title' => $validated['title'] ?? null,
        ]);

        return response()->json($tugas, 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:master_mapel,id',
            'description' => 'required|string',
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
            'title' => 'nullable|string|max:255',
            // 'student_id' => 'required|exists:students,id', ← hapus jika tidak ada di tabel
        ]);

        $tugas = Tugas::findOrFail($id);
        $tugas->update($validated);

        return response()->json([
            'message' => 'Tugas berhasil diperbarui.',
            'data' => $tugas,
        ]);
    }


    public function getPaginatedTugas(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        $tugas = Tugas::with(['mapel', 'teacher'])
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $tugas->items(),
            'total' => $tugas->total(),
            'pagination' => [
                'current_page' => $tugas->currentPage(),
                'total_pages' => $tugas->lastPage(),
                'total_items' => $tugas->total(),
            ]
        ]);
    }

    public function destroy($id)
    {
        $tugas = Tugas::findOrFail($id);
        $tugas->delete();

        return response()->json(['message' => 'Tugas berhasil dihapus']);
    }


    /**
     * Helper function untuk mendapatkan elemen unik berdasarkan key.
     */
    private function uniqueByKey($collection, $key)
    {
        return $collection->unique($key)->values();
    }
}
