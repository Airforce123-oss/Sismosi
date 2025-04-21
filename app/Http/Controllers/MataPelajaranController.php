<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Tugas;
use App\Models\Classes;
use App\Models\JadwalMataPelajaran;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class MataPelajaranController extends Controller
{
    // Endpoint untuk mengambil mata pelajaran dengan pagination
    public function apiCourses(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 20);
        $currentPage = $request->input('currentPage', 1);
        $search = $request->input('search', '');
    
        $mapelQuery = Mapel::query();
    
        if ($search) {
            $mapelQuery->where('name', 'like', '%' . $search . '%'); // Filter berdasarkan nama mapel
        }
    
        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
                                   ->appends($request->only('search', 'itemsPerPage', 'currentPage'));
    
        return response()->json($master_mapel);
    }
    
    // Menampilkan daftar mata pelajaran
    public function mataPelajaran(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 20); // Default ke 10 item per halaman
        $currentPage = $request->input('currentPage', 1); // Default ke halaman pertama

        $mapelQuery = Mapel::query(); // Menggunakan model Mapel
        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
                                   ->appends($request->only('search', 'itemsPerPage', 'currentPage'));

        Log::info('Fetching mata pelajaran list', [
            'itemsPerPage' => $itemsPerPage,
            'currentPage' => $currentPage,
        ]);

        return inertia('MataPelajaran/index', [
            'master_mapel' => MapelResource::collection($master_mapel),
        ]);
    }

    public function settingJadwalMataPelajaran(Request $request)
    {
        // 1. Ambil pagination & filter params
        $itemsPerPage  = $request->input('itemsPerPage', 20);
        $currentPage   = $request->input('currentPage', 1);
        $jurusan       = $request->input('jurusan');
        $tingkat       = $request->input('tingkat');
        $kelas         = $request->input('kelas');
    
        // 2. Build query dasar untuk Mapel
        $mapelQuery = Mapel::query();
        if ($jurusan) { $mapelQuery->where('jurusan', $jurusan); }
        if ($tingkat) { $mapelQuery->where('tingkat', $tingkat); }
        if ($kelas)   { $mapelQuery->where('kelas',   $kelas);   }
    
        // 3. Clone query untuk jadwal (tanpa paginate)
        $allMapel = (clone $mapelQuery)->get();
        $classesQuery = Classes::with('waliKelas');
        $classes_for_student = $classesQuery->paginate(20)->appends($request->only('search'));
        // 4. Paginate untuk master_mapel
        $master_mapel = $mapelQuery
            ->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only('search','jurusan','tingkat','kelas','itemsPerPage','currentPage'));
    
        // 5. Susun struktur jadwal per hari & jam_ke
        $days   = ['senin','selasa','rabu','kamis','jumat','sabtu'];
        $jadwal = [];
    
        for ($i = 1; $i <= 10; $i++) {
            $row = [
                'jam_ke' => $i,
                'jam'    => $this->getJamRangeInline($i),
                'jadwal' => [],
            ];
            foreach ($days as $day) {
                $found = $allMapel->first(function($item) use($day,$i) {
                    return strtolower($item->hari) === $day && $item->jam_ke == $i;
                });
                $row['jadwal'][$day] = $found ? $found->nama_mapel : '';
            }
            $jadwal[] = $row;
        }
    
        // 6. Return ke Inertia dengan dua payload
        return inertia('MataPelajaran/settingJadwalMataPelajaran', [
            'master_mapel' => MapelResource::collection($master_mapel),
            'jadwal'       => $jadwal,
            'filter'       => compact('jurusan','tingkat','kelas'),
            'classes_for_student' => [
                'data' => $classes_for_student->items(), // Mengambil koleksi item dari paginator
                'meta' => [
                    'total' => $classes_for_student->total(),
                    'per_page' => $classes_for_student->perPage(),
                    'current_page' => $classes_for_student->currentPage(),
                    'last_page' => $classes_for_student->lastPage(),
                    'links' => array_merge(
                        [[
                            'url' => $classes_for_student->url(1),
                            'label' => 'First',
                            'active' => $classes_for_student->currentPage() == 1,
                        ]],
                        collect(range(1, $classes_for_student->lastPage()))->map(function ($page) use ($classes_for_student) {
                            return [
                                'url' => $classes_for_student->url($page),
                                'label' => $page,
                                'active' => $classes_for_student->currentPage() == $page,
                            ];
                        })->toArray(),
                        [[
                            'url' => $classes_for_student->previousPageUrl(),
                            'label' => 'Previous',
                            'active' => $classes_for_student->previousPageUrl() !== null,
                        ],
                        [
                            'url' => $classes_for_student->nextPageUrl(),
                            'label' => 'Next',
                            'active' => $classes_for_student->nextPageUrl() !== null,
                        ],
                        [
                            'url' => $classes_for_student->url($classes_for_student->lastPage()),
                            'label' => 'Last',
                            'active' => $classes_for_student->currentPage() == $classes_for_student->lastPage(),
                        ]]
                    ),
                ],
            ],
        ]);
    }

    
    /**
     * Fungsi helper di-inline saja (private)
     */
    private function getJamRangeInline($jamKe)
    {
        $start    = strtotime("07:00");
        $offset   = 60 * ($jamKe - 1);
        $begin    = date('H:i', $start + $offset);
        $end      = date('H:i', $start + $offset + 60);
        return "$begin - $end";
    }
    

    public function getMapel()
    {
        // Mengambil semua data dari tabel master_mapel
        $mapel = Mapel::all();

        // Mengembalikan data dalam format JSON
        return response()->json($mapel);
    }
    public function storeJadwal(Request $request)
    {
        $validatedData = $request->validate([
            'kelas_id'          => 'required|integer',
            'entries'           => 'required|array',
            'entries.*.hari'    => 'required|string|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'entries.*.jam_ke'  => 'required|integer',
            'entries.*.jam'     => 'required|string', // ✅ validasi jam ditambahkan
            'entries.*.mapel_id'=> 'required|integer|exists:master_mapel,id',
        ]);
    
        try {
            foreach ($validatedData['entries'] as $entry) {
                JadwalMataPelajaran::create([
                    'hari'     => $entry['hari'],
                    'jam_ke'   => $entry['jam_ke'],
                    'jam'      => $entry['jam'],
                    'mapel_id' => $entry['mapel_id'],
                    'kelas_id' => $validatedData['kelas_id'],
                ]);
            }
    
            return response()->json(['message' => 'Jadwal berhasil disimpan!'], 200);
        } catch (\Exception $e) {
            \Log::error('Error saving jadwal:', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal menyimpan jadwal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
    
    public function getJadwal(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer',
        ], [], [
            'kelas_id' => 'kelas_id', // 🛠️ override agar tidak berubah jadi 'kelas id'
        ]);
    
        $jadwals = JadwalMataPelajaran::with('mapel')
            ->where('kelas_id', $request->kelas_id)
            ->get();
    
        $struktur = [];
    
        foreach ($jadwals as $jadwal) {
            $jamKe = $jadwal->jam_ke;
            $hari = strtolower($jadwal->hari);
    
            if (!isset($struktur[$jamKe])) {
                $struktur[$jamKe] = [
                    'jam_ke' => $jamKe,
                    'jam' => $jadwal->jam ?? '',
                    'jadwal' => [],
                ];
            }
    
            $struktur[$jamKe]['jadwal'][$hari] = [
                'mapel' => $jadwal->mapel->mapel ?? '-',
                'mapel_id' => $jadwal->mapel_id,
            ];
        }
    
        ksort($struktur);
    
        return response()->json(array_values($struktur));
    }
    
    

    // Menampilkan form untuk membuat mata pelajaran baru
    public function create()
    {
        $mapel = MapelResource::collection(Mapel::all()); // Menggunakan model Mapel

        Log::info('Fetching data for mata pelajaran creation', [
            'total_mapel' => $mapel->count(),
        ]);

        return inertia('MataPelajaran/create', [
            'kode_mapel' => $mapel,
        ]);
    }

    public function show($id)
{
    $mapel = Mapel::find($id);  // Mengambil data berdasarkan id
    if (!$mapel) {
        return response()->json(['message' => 'Mata Pelajaran tidak ditemukan'], 404);
    }
    return response()->json(new MapelResource($mapel));
}


    // Menyimpan mata pelajaran baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:40',
            'mapel' => 'required|string|max:60',
        ]);

        $existingMapel = Mapel::where('kode_mapel', $validated['kode_mapel'])->first(); // Menggunakan model Mapel
        if ($existingMapel) {
            Log::info('Failed to store mata pelajaran: Duplicate kode_mapel', [
                'kode_mapel' => $validated['kode_mapel'],
            ]);

            return redirect()->back()->withErrors(['kode_mapel' => 'Kode Mapel sudah ada.'])->withInput();
        }

        $newMapel = Mapel::create($validated); // Menggunakan model Mapel

        Log::info('Successfully stored new mata pelajaran', [
            'id' => $newMapel->id,
            'kode_mapel' => $newMapel->kode_mapel,
            'mapel' => $newMapel->mapel,
        ]);

        return redirect()->route('matapelajaran.index')->with('success', 'Data berhasil disimpan!');
    }

    // Mengupdate mata pelajaran yang sudah ada
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:40|unique:master_mapel,kode_mapel,' . $id . ',id', // Validasi unik
            'mapel' => 'required|string|max:60',
        ]);
    
        $mapel = Mapel::findOrFail($id); // Cari berdasarkan id
        $mapel->update($validated); // Perbarui data
    
        Log::info('Successfully updated mata pelajaran', [
            'id' => $id,
            'updated_data' => $validated,
        ]);
    
        return redirect()->route('matapelajaran.index')->with('success', 'Data berhasil diperbarui!');
    }
    
    
    // Menampilkan form untuk edit mata pelajaran
    public function edit($id)
    {
        $mapel = Mapel::find($id); // Mengambil data berdasarkan id
        if (!$mapel) {
            return redirect()->route('matapelajaran.index')->withErrors(['message' => 'Mata Pelajaran tidak ditemukan']);
        }
    
        return inertia('MataPelajaran/edit', [
            'mapel' => new MapelResource($mapel),
        ]);
    }
    

    // Menghapus mata pelajaran
    public function destroy($id)
    {
        $mapel = Mapel::findOrFail($id); // Mengambil data berdasarkan id
    
        Log::info('Deleting mata pelajaran', [
            'id' => $id,
            'kode_mapel' => $mapel->kode_mapel,
            'mapel' => $mapel->mapel,
        ]);
    
        $mapel->delete();
    
        return redirect()->route('matapelajaran.index');
    }
    
}
