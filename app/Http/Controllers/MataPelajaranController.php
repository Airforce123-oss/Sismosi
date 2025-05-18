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
use Inertia\Inertia;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Database\QueryException;


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

    public function laporanJadwalMataPelajaran(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 20);
        $currentPage  = $request->input('currentPage', 1);
        $filter       = $request->only(['kelas']);
        $kelasId      = $request->input('kelas_id');
        \Log::info('🔎 Kelas ID:', ['kelas_id' => $kelasId]);
        //dd(Mapel::with('guru')->find(11));

        $teachers = Teacher::all(['id', 'name']);
        $classes  = $this->getClasses($request);
    
        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);
    
        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only(array_merge(['search'], array_keys($filter), ['itemsPerPage', 'currentPage'])));
    
        $jadwal   = $this->generateJadwalMingguan($allMapel);

        foreach ($jadwal as $jadwalItem) {
            // $jadwalItem adalah array ['jam_ke' => ..., 'jam' => ..., 'jadwal' => [...]]
            \Log::info("Jadwal jam ke-{$jadwalItem['jam_ke']}, jam: {$jadwalItem['jam']}");
        
            // 'jadwal' adalah array hari => data jadwal (mapel, guru, kelas, dll)
            foreach ($jadwalItem['jadwal'] as $hari => $detailJadwal) {
                if ($detailJadwal) {
                    \Log::info("Hari: $hari, Mapel: {$detailJadwal['mapel']}, Guru: {$detailJadwal['guru']}, Kelas: {$detailJadwal['kelas']}");
                } else {
                    \Log::info("Hari: $hari, tidak ada jadwal");
                }
            }
        }
        

        $schedule = $kelasId ? $this->getScheduleByKelas($kelasId) : [];
    
        return inertia('MataPelajaran/laporanJadwalMataPelajaran', [
            'master_mapel' => MapelResource::collection($master_mapel),
            'jadwal'       => $jadwal,
            'kelas_id' => (int) $kelasId,
            'schedule'     => $schedule,
            'filter'       => $filter,
            'classes_for_student' => [
                'data' => $classes->items(),
                'meta' => $this->buildPaginationMeta($classes),
            ],
        'wali_kelas' => [
                'data' => $teachers,
            ],
            'teachers' => $teachers,
        ]);
    }

    public function settingJadwalMataPelajaran(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 20);
        $currentPage  = $request->input('currentPage', 1);
        $filter       = $request->only(['kelas']);
        $kelasId      = $request->input('kelas_id');
        \Log::info('🔎 Kelas ID:', ['kelas_id' => $kelasId]);
        //dd(Mapel::with('guru')->find(11));

        $teachers = Teacher::all(['id', 'name']);
        $classes  = $this->getClasses($request);
    
        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);
    
        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only(array_merge(['search'], array_keys($filter), ['itemsPerPage', 'currentPage'])));
    
        $jadwal   = $this->generateJadwalMingguan($allMapel);

        foreach ($jadwal as $jadwalItem) {
            // $jadwalItem adalah array ['jam_ke' => ..., 'jam' => ..., 'jadwal' => [...]]
            \Log::info("Jadwal jam ke-{$jadwalItem['jam_ke']}, jam: {$jadwalItem['jam']}");
        
            // 'jadwal' adalah array hari => data jadwal (mapel, guru, kelas, dll)
            foreach ($jadwalItem['jadwal'] as $hari => $detailJadwal) {
                if ($detailJadwal) {
                    \Log::info("Hari: $hari, Mapel: {$detailJadwal['mapel']}, Guru: {$detailJadwal['guru']}, Kelas: {$detailJadwal['kelas']}");
                } else {
                    \Log::info("Hari: $hari, tidak ada jadwal");
                }
            }
        }
        

        $schedule = $kelasId ? $this->getScheduleByKelas($kelasId) : [];
    
        return inertia('MataPelajaran/settingJadwalMataPelajaran', [
            'master_mapel' => MapelResource::collection($master_mapel),
            'jadwal'       => $jadwal,
            'kelas_id' => (int) $kelasId,
            'schedule'     => $schedule,
            'filter'       => $filter,
            'classes_for_student' => [
                'data' => $classes->items(),
                'meta' => $this->buildPaginationMeta($classes),
            ],
        'wali_kelas' => [
                'data' => $teachers,
            ],
            'teachers' => $teachers,
        ]);
    }
    
    private function getTeachers(Request $request)
    {
        return Teacher::with('class')
            ->orderBy('id')
            ->paginate(20)
            ->appends($request->only('search'));
    }
    
    private function getClasses(Request $request)
    {
        return Classes::with('waliKelas')
            ->paginate(20)
            ->appends($request->only('search'));
    }
    
    private function getFilteredMapel(array $filter, $kelasId)
    {
        $query = Mapel::query();
    
        foreach ($filter as $key => $value) {
            if ($value) $query->where($key, $value);
        }
    
        if ($kelasId) {
            $query->where('master_mapel.kelas_id', $kelasId);
        }
    
        return [$query, (clone $query)->get()];
    }
    
    private function generateJadwalMingguan($mapels)
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    
        // Menentukan rentang jam secara manual
        $jamRange = [
            1 => '07:00 - 07:45',
            2 => '08:00 - 08:45',
            3 => '09:00 - 09:45',
            4 => '10:00 - 10:45',
            5 => '11:00 - 11:45',
            6 => '12:00 - 12:45',
            7 => '13:00 - 13:45',
            8 => '14:00 - 14:45',
            9 => '15:00 - 15:45',
        ];
    
        return collect(range(1, 9))->map(function ($i) use ($days, $mapels, $jamRange) {
            return [
                'jam_ke' => $i,
                'jam'    => $jamRange[$i] ?? '',
                'jadwal' => collect($days)->mapWithKeys(function ($day) use ($mapels, $i) {
                    $found = $mapels->first(fn($item) => strtolower($item->hari) === $day && $item->jam_ke == $i);
                    return [$day => $found ? [
                        'mapel'    => $found->mapel,
                        'mapel_id' => $found->id,
                        'kelas'    => $found->kelas->name ?? '-',
                        'guru'     => $found->teachers->pluck('name')->join(', ') ?? '-',
                        'hari'     => $found->hari,
                        'jam_ke'   => $found->jam_ke,
                        'tahun'    => $found->tahun_ajaran ?? null,
                    ] : null];
                }),
            ];
        });
    }
    
    private function getScheduleByKelas($kelasId)
    {
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    
        return JadwalMataPelajaran::with(['mapel' => fn($q) => $q->with('teachers'), 'kelas.waliKelas', 'guru'])
            ->where('kelas_id', $kelasId)
            ->get()
            ->groupBy('jam_ke')
            ->map(function ($group, $jam_ke) use ($days) {
                $entry = [
                    'jam_ke' => (int) $jam_ke,
                    'jam'    => $group->first()->jam,
                    'jadwal' => [],
                ];
                foreach ($days as $day) {
                    $jadwalHari = $group->filter(fn($item) => strtolower($item->hari) === $day);
                
                    if ($jadwalHari->isNotEmpty()) {
                        $firstItem = $jadwalHari->first();
                        $mapel = $firstItem->mapel;
                
                        $entry['jadwal'][$day] = [
                            'mapel'       => $mapel->mapel ?? null,
                            'mapel_id'    => $firstItem->mapel_id,
                            'kelas'       => $firstItem->kelas->name ?? null,
                            'kelas_id'    => $firstItem->kelas_id,
                            'guru'        => $mapel->teachers->pluck('name')->join(', ') ?? '-',
                            'guru_id'     => $mapel->teachers->pluck('id')->join(', ') ?? '-',
                            'tahun'       => $firstItem->tahun ?? null,
                            'wali_kelas'  => optional($firstItem->kelas->waliKelas)->name,
                        ];
                    } else {
                        $entry['jadwal'][$day] = null;
                    }
                }
                
                return $entry;
            })->values();
    }
    
    
    private function buildPaginationMeta($paginator)
    {
        return [
            'total'         => $paginator->total(),
            'per_page'      => $paginator->perPage(),
            'current_page'  => $paginator->currentPage(),
            'last_page'     => $paginator->lastPage(),
            'links'         => $this->buildPaginationLinks($paginator),
        ];
    }

    private function buildPaginationLinks($paginator)
{
    return array_merge(
        [[
            'url'    => $paginator->url(1),
            'label'  => 'First',
            'active' => $paginator->currentPage() == 1,
        ]],
        collect(range(1, $paginator->lastPage()))->map(fn($page) => [
            'url'    => $paginator->url($page),
            'label'  => $page,
            'active' => $paginator->currentPage() == $page,
        ])->toArray(),
        [[
            'url'    => $paginator->previousPageUrl(),
            'label'  => 'Previous',
            'active' => $paginator->previousPageUrl() !== null,
        ], [
            'url'    => $paginator->nextPageUrl(),
            'label'  => 'Next',
            'active' => $paginator->nextPageUrl() !== null,
        ], [
            'url'    => $paginator->url($paginator->lastPage()),
            'label'  => 'Last',
            'active' => $paginator->currentPage() == $paginator->lastPage(),
        ]]
    );
}
public function createJadwalMataPelajaran(Request $request)
{
    $itemsPerPage  = $request->input('itemsPerPage', 20);
    $currentPage   = $request->input('currentPage', 1);
    $kelas         = $request->input('kelas');
    $kelasId      = $request->input('kelas_id');
    \Log::info('🔎 Kelas ID:', ['kelas_id' => $kelasId]);

    // Ambil data guru
    $teacherQuery = Teacher::query()->with('class');
    $teacherQuery->orderBy('id');

    $mapelQuery = Mapel::with(['kelas.waliKelas', 'teachers']);

    if ($kelas) {
        $mapelQuery->where('kelas', $kelas);
    }
    

    $classesQuery = Classes::with('waliKelas');
    $teachers = Teacher::all(['id', 'name']);

    $classes_for_student = $classesQuery->paginate(20)->appends($request->only('search'));

    $master_mapel = $mapelQuery->get();


    // Susun jadwal
    $days = ['senin','selasa','rabu','kamis','jumat','sabtu'];
    $jadwal = [];

    for ($i = 1; $i <= 10; $i++) {
        $jamInfo = $this->getJamRangeInline($i);

        $row = [
            'jam_ke' => $jamInfo['jam_ke'],
            'jam'    => $jamInfo['jam'],
            'jadwal' => [],
        ];

        // Ganti `first()` dengan `filter()` untuk mendapatkan semua mapel yang cocok dengan hari dan jam
        foreach ($days as $day) {
            $foundMapels = $master_mapel->filter(function($item) use($day, $i) {
                return strtolower($item->hari) === $day && $item->jam_ke == $i;
            });

            if ($foundMapels->isNotEmpty()) {
                // Menyusun data jadwal untuk semua mapel yang ditemukan pada hari dan jam tertentu
                foreach ($foundMapels as $found) {
                    $kelas = $found->kelas;
                    $row['jadwal'][$day][] = [
                        'mapel' => $found->nama_mapel,
                        'mapel_id' => $found->id,
                        'kelas' => $kelas?->name ?? '-',
                        'guru' => $found->guru?->name ?? '-',
                        'guru_id' => $found->teachers->pluck('id')->join(', ') ?: '-',
                        'wali_kelas' => $kelas?->waliKelas?->name ?? '-',
                        'tahun' => $kelas?->tahun_ajaran ?? '-',
                    ];
                }
            } else {
                $row['jadwal'][$day] = null;
            }
        }


        $jadwal[] = $row;
    }

    $classes_for_student_with_wali_kelas = collect($classes_for_student->items())->map(function($class) {
        return [
            'id' => $class->id,
            'name' => $class->name,
            'wali_kelas' => $class->waliKelas?->name ?? 'Tidak Diketahui',
            'tahun_ajaran' => $class->tahun_ajaran ?? 'Tidak Diketahui',
        ];
    });

    $filterData = compact( 'kelas');

    /// \Log::info("🔍 Jadwal Data: ", $jadwal);

    return inertia('MataPelajaran/createJadwalMataPelajaran', [
        'master_mapel' => MapelResource::collection($master_mapel),
        'jadwal'       => $jadwal,
        'teachers'     => $teachers,
        'filter'       => $filterData,
        'classes_for_student' => [
            'data' => $classes_for_student_with_wali_kelas,
        ],
    ]);
}

    private function getJamRangeInline($jamKe)
    {
        $durasi = 45;
        $start = Carbon::createFromTimeString('07:00')->addMinutes(($jamKe - 1) * $durasi);
    
        // Mencegah waktu melebihi 15:45
        if ($start->hour >= 17) {
            return [
                'jam_ke' => $jamKe,
                'jam' => '',
            ];
        }
    
        $end = $start->copy()->addMinutes($durasi);
    
        return [
            'jam_ke' => $jamKe,
            'jam' => $start->format('H:i') . ' - ' . $end->format('H:i'),
        ];
    }

    public function getTahunAjaran(Request $request)
    {
        // Mengambil data tahun ajaran
        $tahunAjaran = Classes::select('tahun_ajaran')->distinct()->get();

        // Mengembalikan data dalam format JSON
        return response()->json($tahunAjaran);
    }

    
    public function absensiSiswaJanuarigetMapel()
    {
        // Mengambil semua data dari tabel master_mapel
        $mapel = Mapel::all();

        // Mengembalikan data dalam format JSON
        return response()->json($mapel);
    }
    public function storeJadwal(Request $request)
    {
        // Validasi data yang diterima dari request
        $validatedData = $request->validate([
            'kelas_id'             => 'required|integer|exists:classes,id',
            'entries'              => 'required|array',
            'entries.*.hari'       => 'required|string|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'entries.*.jam_ke'     => 'required|integer',
            'entries.*.jam'        => 'required|string',
            'entries.*.mapel_id'   => 'required|integer|exists:master_mapel,id',
            'entries.*.wali_kelas' => 'nullable|string|max:255',
            'entries.*guru'        => 'nullable|string|max:255',
            'entries.*.guru_id'    => 'required|numeric|exists:teachers,id',
            'tahun_ajaran'         => 'nullable|string',
        ]);
    
        try {
            $kelas = Classes::with('waliKelas')->find($validatedData['kelas_id']);
            $waliKelas = $kelas?->waliKelas?->name ?? 'Tidak Diketahui';
            $tahunAjaran = $validatedData['tahun_ajaran'] ?? now()->year;
    
            foreach ($validatedData['entries'] as $entry) {
                $jamKe = (string) $entry['jam_ke'];
                $hari  = strtolower($entry['hari']);
    
                Log::info("🔍 Mengecek konflik jadwal untuk kelas_id={$validatedData['kelas_id']}, hari={$hari}, jam_ke={$jamKe}");
    
                $existing = JadwalMataPelajaran::where('kelas_id', $validatedData['kelas_id'])
                    ->where('hari', $hari)
                    ->where('jam_ke', $jamKe)
                    ->first();
    
                if ($existing) {
                    if (
                        $existing->mapel_id != $entry['mapel_id'] ||
                        ($entry['guru_id'] && $existing->guru_id != $entry['guru_id'])
                    ) {
                        // Ambil nama guru dan mapel dari jadwal existing
                        $guruName = 'Unknown Guru';
                        $mapelName = 'Unknown Mapel';
    
                        if ($existing->guru_id) {
                            $guru = Teacher::find($existing->guru_id);
                            $guruName = $guru?->name ?? "ID {$existing->guru_id} (tidak ditemukan)";
                        }
    
                        if ($existing->mapel_id) {
                            $mapel = Mapel::find($existing->mapel_id);
                            $mapelName = $mapel?->mapel ?? "ID {$existing->mapel_id} (tidak ditemukan)";
                        }
    
                        return response()->json([
                            'message' => "❌ Jadwal bentrok pada hari {$hari}, jam ke-{$jamKe}.",
                            'conflict_with' => [
                                'mapel_id'   => $existing->mapel_id,
                                'guru_id'    => $existing->guru_id,
                                'guru_name'  => $guruName,
                                'mapel_name' => $mapelName,
                            ]
                        ], 422);
                    }
                }
    
                JadwalMataPelajaran::updateOrCreate(
                    [
                        'kelas_id' => $validatedData['kelas_id'],
                        'hari'     => $hari,
                        'jam_ke'   => $jamKe,
                    ],
                    [
                        'jam'        => $entry['jam'],
                        'mapel_id'   => $entry['mapel_id'],
                        'wali_kelas' => $entry['wali_kelas'] ?? $waliKelas,
                        'tahun'      => $tahunAjaran,
                        'guru_id'    => $entry['guru_id'] ?? $request->guru_id ?? null,
                        'guru'       => $entry['guru'] ?? $request->guru ?? null,
                    ]
                );
            }
    
            $jadwals = JadwalMataPelajaran::with(['mapel.teachers', 'kelas', 'guru'])
                ->where('kelas_id', $validatedData['kelas_id'])
                ->get();
    
            $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
            $struktur = [];
    
            foreach ($jadwals as $jadwal) {
                $jamKe = (string) $jadwal->jam_ke;
                $hari = strtolower($jadwal->hari);
    
                if (!isset($struktur[$jamKe])) {
                    $struktur[$jamKe] = [
                        'jam_ke' => $jamKe,
                        'jam'    => $jadwal->jam,
                        'jadwal' => [],
                    ];
                    foreach ($hariList as $h) {
                        $struktur[$jamKe]['jadwal'][$h] = null;
                    }
                }
    
                $struktur[$jamKe]['jadwal'][$hari] = [
                    'mapel'      => $jadwal->mapel->mapel ?? '-',
                    'mapel_id'   => $jadwal->mapel_id,
                    'kelas'      => $jadwal->kelas->name ?? '-',
                    'guru'       => optional($jadwal->guru)->name ?? '-',
                    'guru_id'    => $jadwal->guru_id ?? null,
                    'wali_kelas' => $jadwal->wali_kelas ?? '-',
                    'tahun'      => $jadwal->tahun ?? '-',
                ];
            }
    
            ksort($struktur);
    
            return response()->json([
                'message'  => '✅ Jadwal berhasil disimpan!',
                'schedule' => array_values($struktur),
                'kelas_id' => $validatedData['kelas_id'],
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => '❌ Gagal menyimpan jadwal (database error).',
                'error'   => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '❌ Gagal menyimpan jadwal.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function getJadwal(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|integer|exists:classes,id',
        ]);
    
        $kelasId = $validated['kelas_id'];
    
        // Ambil semua jadwal, relasi guru tunggal (guru_id), dan wali_kelas
        $jadwals = JadwalMataPelajaran::with([
            'mapel.guru',        // belongsTo
            'kelas.waliKelas',    // belongsTo
            'guru',
        ])
        ->where('kelas_id', $kelasId)
        ->get();

        Log::info('Jadwals fetched:', $jadwals->toArray());
    
        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $entries = [];
    
        foreach ($jadwals as $jadwal) {
            $jamKe = $jadwal->jam_ke;
            $hari  = strtolower($jadwal->hari);
    
            // Gunakan relasi guru tunggal
            $guru = $jadwal->mapel->guru;
    
            // Logging ID guru untuk debugging
            \Log::info('Received guru_id:', [$guru?->id]);
    
            // Inisialisasi entries untuk jamKe jika belum ada
            if (!isset($entries[$jamKe])) {
                $entries[$jamKe] = [
                    'jam_ke' => $jamKe,
                    'jam'    => $jadwal->jam,
                    'jadwal' => [],
                ];
    
                // Mengisi setiap hari dengan null
                foreach ($hariList as $h) {
                    $entries[$jamKe]['jadwal'][$h] = null;
                }
            }
    
            // Menyimpan data jadwal untuk hari yang sesuai
            $entries[$jamKe]['jadwal'][$hari] = [
                'mapel'      => $jadwal->mapel->mapel ?? '-',
                'mapel_id'   => $jadwal->mapel_id,
                'kelas'      => $jadwal->kelas->name ?? '-',
                'guru' => optional($jadwal->guru)->name ?? '-',
                'guru_id'    => $jadwal->guru_id ?? null,
                'wali_kelas' => $jadwal->kelas->waliKelas->name ?? '-',
                'tahun'      => $jadwal->tahun ?? '-',
            ];
        }
    
        // Mengurutkan berdasarkan jam_ke dan memastikan data terstruktur
        ksort($entries);
    
        // Logging hasil entries untuk debugging
        \Log::info('Jadwal Entries:', $entries);
    
        // Mengirimkan response
        return response()->json(array_values($entries));
    }
    
    public function getAllJadwal()
{
    $jadwals = JadwalMataPelajaran::with([
        'mapel.teachers',
        'kelas',
        'guru'

    ])->get();

    $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    $grouped = [];

    foreach ($jadwals as $jadwal) {
        $jamKe = $jadwal->jam_ke;
        $hari  = strtolower($jadwal->hari);
        $kelasId = $jadwal->kelas_id;
        $guru = $jadwal->mapel->teachers->firstWhere('id', $jadwal->guru_id);

        \Log::info('Received guru_id:', [$guru?->id]);


        if (!isset($grouped[$kelasId])) {
            $grouped[$kelasId] = [];
        }

        if (!isset($grouped[$kelasId][$jamKe])) {
            $grouped[$kelasId][$jamKe] = [
                'jam_ke' => $jamKe,
                'jam'    => $jadwal->jam,
                'jadwal' => [],
            ];

            foreach ($hariList as $h) {
                $grouped[$kelasId][$jamKe]['jadwal'][$h] = null;
            }
        }

        $grouped[$kelasId][$jamKe]['jadwal'][$hari] = [
            'mapel'      => $jadwal->mapel->mapel ?? '-',
            'mapel_id'   => $jadwal->mapel_id,
            'kelas'      => $jadwal->kelas->name ?? '-',
            'kelas_id'   => $kelasId,
            'guru'       => $guru?->name ?? '-',
            'guru_id'    => $guru?->id ?? '-',
            'wali_kelas' => $jadwal->kelas->waliKelas->name ?? '-',
            'tahun'      => $jadwal->tahun ?? '-',
        ];
    }

    // Sortir setiap jam_ke dalam tiap kelas
    foreach ($grouped as $kelasId => &$jadwalKelas) {
        ksort($jadwalKelas);
        $jadwalKelas = array_values($jadwalKelas); // reset index ke angka biasa
    }

    return response()->json($grouped); // bentuk: {kelas_id: [array of jadwal]}
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
            return inertia('ErrorPage', ['message' => 'Mata Pelajaran tidak ditemukan']);
        }
        return inertia('MataPelajaran/Detail', [
            'mapel' => new MapelResource($mapel),
        ]);
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
        $mapel = Mapel::find($id);
        if (!$mapel) {
            return inertia('MataPelajaran/Error', ['message' => 'Mata Pelajaran tidak ditemukan']);
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
