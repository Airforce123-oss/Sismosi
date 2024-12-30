<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Teacher;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TugasController extends Controller
{
    public function index(Request $request)
    {
        // Ambil data guru dan mata pelajaran
        $courses = $this->uniqueByKey(Mapel::all(), 'id_mapel');
        $teachers = $this->uniqueByKey(Teacher::all(), 'id');
    
        // Ambil semua data tugas untuk debugging
        $tugas = Tugas::with(['mapel', 'teacher'])->get();
        dd($tugas->toJson());
    
        // Ambil data tugas dengan pagination
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
    
        $tugas = Tugas::with(['mapel', 'teacher'])
            ->paginate($perPage, ['*'], 'page', $page);
    
        // Kirim data ke Vue.js melalui Inertia
        return Inertia::render('Teachers/TugasSiswa/membuatTugasSiswa', [
            'courses' => $courses,
            'teachers' => $teachers,
            'tugas' => $tugas->items(),
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
            'mapel_id' => 'required|exists:master_mapel,id_mapel',
            'teacher_id' => 'required|exists:wali_kelas,id',
            'description' => 'required|string',
        ]);

        // Simpan data tugas
        $tugas = Tugas::create([
            'mapel_id' => $validated['mapel_id'],
            'teacher_id' => $validated['teacher_id'],
            'description' => $validated['description'],
        ]);

        // Ambil data terkait untuk dikembalikan
        $mapel = Mapel::find($validated['mapel_id']);
        $teacher = Teacher::find($validated['teacher_id']);

        return response()->json([
            'tugas' => $tugas,
            'mapel' => $mapel,
            'teacher' => $teacher,
        ], 201);
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

    /**
     * Helper function untuk mendapatkan elemen unik berdasarkan key.
     */
    private function uniqueByKey($collection, $key)
    {
        return $collection->unique($key)->values();
    }
}
