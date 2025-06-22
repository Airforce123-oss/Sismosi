<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Tugas;
use Illuminate\Support\Collection;
use App\Models\Classes;
use Illuminate\Support\Facades\Schema;
use App\Models\JadwalMataPelajaran;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Teacher;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;


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
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->roles->first()?->name ?? 'guest';

        // Ambil parameter pagination dari request
        $perPage = $request->input('itemsPerPage', 5);
        $currentPage = $request->input('page', 1);

        $query = Mapel::query();

        // Optional: pencarian jika diperlukan (sesuaikan field pencarian jika ada)
        if ($search = $request->input('search')) {
            $query->where('nama_mapel', 'like', '%' . $search . '%');
        }

        // Paginate dengan append query string agar link pagination sesuai
        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage)
            ->appends($request->only('search', 'itemsPerPage', 'currentPage'));

        // Transformasi pakai Resource dan convert jadi array
        $mapelData = MapelResource::collection($paginator->items())->resolve();

        return inertia('MataPelajaran/index', [
            'master_mapel' => [
                'data' => $mapelData,
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'role_type' => $role,
            'auth' => ['user' => $user],
        ]);
    }

    public function laporanJadwalMataPelajaran(Request $request)
    {
        $filter = $request->only(['kelas']);
        $kelasId = $request->input('kelas_id');

        \Log::info('🔎 Kelas ID:', ['kelas_id' => $kelasId]);

        // Ambil semua guru yang mungkin jadi wali_kelas
        $teachers = Teacher::all(['id', 'name', 'class_id']);

        $classes = $this->getClasses($request);

        // Ambil data mapel sesuai filter
        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);

        \Log::info('🧪 mapelQuery class:', ['type' => get_class($mapelQuery)]);
        $master_mapel = $allMapel;

        // Generate jadwal mingguan dari semua mapel
        $jadwal = $this->generateJadwalMingguan($allMapel);

        // Ambil jadwal per kelas lengkap dengan relasi wali_kelas
        $schedule = $kelasId ? $this->getScheduleByKelas($kelasId) : [];

        // Tidak perlu indexing wali_kelas dari guru, karena sudah diambil lewat relasi kelas

        return inertia('MataPelajaran/laporanJadwalMataPelajaran', [
            'master_mapel' => MapelResource::collection($master_mapel),
            'jadwal' => $jadwal,
            'kelas_id' => (int) $kelasId,
            'schedule' => $schedule,
            'filter' => $filter,
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
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $currentPage = $request->input('currentPage', 1);
        $filter = $request->only(['kelas_id']);
        $kelasId = $request->input('kelas_id');
        \Log::info('🔎 Kelas ID:', ['kelas_id' => $kelasId]);
        //dd(Mapel::with('guru')->find(11));

        $teachers = Teacher::all(['id', 'name']);
        $classes = $this->getClasses($request);

        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);

        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only(array_merge(['search'], array_keys($filter), ['itemsPerPage', 'currentPage'])));

        $jadwal = $this->generateJadwalMingguan($allMapel);

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
            'master_mapel' => $master_mapel,
            'jadwal' => $jadwal,
            'kelas_id' => (int) $kelasId,
            'schedule' => $schedule,
            'filter' => $filter,
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

    private function getClasses(Request $request)
    {
        return Classes::with('waliKelas')
            ->paginate(5)
            ->appends($request->only('search'));
    }

    private function getFilteredMapel(array $filter, $kelasId)
    {
        $query = Mapel::query();

        // ✅ Jangan gunakan 'kelas', hanya filter berdasarkan key yang valid di kolom
        foreach ($filter as $key => $value) {
            if (!empty($value) && Schema::hasColumn('mapels', $key)) {
                $query->where($key, $value);
            }
        }

        if (!empty($kelasId)) {
            $query->where('kelas_id', $kelasId);
        }

        $mapelData = (clone $query)->get();

        return [$query, $mapelData];
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

        // Ambil data wali kelas dan index berdasarkan class_id
        $waliKelasByClassId = Teacher::whereNotNull('class_id')->get()->keyBy('class_id');

        \Log::info('📘 Daftar wali kelas by class_id:', $waliKelasByClassId->map->only(['id', 'name', 'class_id'])->toArray());

        return collect(range(1, 9))->map(function ($i) use ($days, $mapels, $jamRange, $waliKelasByClassId) {
            return [
                'jam_ke' => $i,
                'jam' => $jamRange[$i] ?? '',
                'jadwal' => collect($days)->mapWithKeys(function ($day) use ($mapels, $i, $waliKelasByClassId) {
                    $found = $mapels->first(fn($item) => strtolower($item->hari) === $day && $item->jam_ke == $i);

                    $kelasId = $found?->kelas_id;

                    $waliKelas = $kelasId ? $waliKelasByClassId[$kelasId]->name ?? '-' : '-';

                    return [
                        $day => $found ? [
                            'mapel' => $found->mapel,
                            'mapel_id' => $found->id,
                            'kelas' => $found->kelas->name ?? '-',
                            'guru' => $found->teachers->pluck('name')->join(', ') ?? '-',
                            'hari' => $found->hari,
                            'jam_ke' => $found->jam_ke,
                            'tahun' => $found->tahun_ajaran ?? null,
                            'wali_kelas' => $waliKelas,
                        ] : null
                    ];
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
                    'jam' => $group->first()->jam,
                    'jadwal' => [],
                ];
                foreach ($days as $day) {
                    $jadwalHari = $group->filter(fn($item) => strtolower($item->hari) === $day);

                    if ($jadwalHari->isNotEmpty()) {
                        $firstItem = $jadwalHari->first();
                        $mapel = $firstItem->mapel;

                        // Logging untuk debugging wali_kelas dan data terkait
                        \Log::info('📋 Jadwal Hari:', [
                            'jam_ke' => $jam_ke,
                            'hari' => $day,
                            'mapel' => $mapel->mapel ?? null,
                            'kelas' => $firstItem->kelas->name ?? null,
                            'kelas_id' => $firstItem->kelas_id,
                            'wali_kelas_id' => $firstItem->kelas->wali_kelas_id ?? null,
                            'wali_kelas_nama' => optional($firstItem->kelas->waliKelas)->name,
                        ]);

                        $entry['jadwal'][$day] = [
                            'mapel' => $mapel->mapel ?? null,
                            'mapel_id' => $firstItem->mapel_id,
                            'kelas' => $firstItem->kelas->name ?? null,
                            'kelas_id' => $firstItem->kelas_id,
                            'guru' => $mapel->teachers->pluck('name')->join(', ') ?? '-',
                            'guru_id' => $mapel->teachers->pluck('id')->join(', ') ?? '-',
                            'tahun' => $firstItem->tahun ?? null,
                            'wali_kelas' => optional($firstItem->kelas->waliKelas)->name,
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
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'links' => $this->buildPaginationLinks($paginator),
        ];
    }

    private function buildPaginationLinks($paginator)
    {
        return array_merge(
            [
                [
                    'url' => $paginator->url(1),
                    'label' => 'First',
                    'active' => $paginator->currentPage() == 1,
                ]
            ],
            collect(range(1, $paginator->lastPage()))->map(fn($page) => [
                'url' => $paginator->url($page),
                'label' => $page,
                'active' => $paginator->currentPage() == $page,
            ])->toArray(),
            [
                [
                    'url' => $paginator->previousPageUrl(),
                    'label' => 'Previous',
                    'active' => $paginator->previousPageUrl() !== null,
                ],
                [
                    'url' => $paginator->nextPageUrl(),
                    'label' => 'Next',
                    'active' => $paginator->nextPageUrl() !== null,
                ],
                [
                    'url' => $paginator->url($paginator->lastPage()),
                    'label' => 'Last',
                    'active' => $paginator->currentPage() == $paginator->lastPage(),
                ]
            ]
        );
    }

    public function apiJadwal(Request $request)
    {
        // Tambahkan relasi mapel, kelas, dan guru
        $query = JadwalMataPelajaran::with([
            'mapel',         // relasi ke tabel mapel
            'kelas',         // relasi ke tabel kelas
            'guru',          // relasi ke tabel guru
            'kelas.waliKelas'
        ]);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->boolean('no_paginate')) {
            $data = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'hari' => $item->hari,
                    'jam_ke' => $item->jam_ke,
                    'jam' => $item->jam,
                    'mapel_id' => $item->mapel_id,
                    'mapel_nama' => $item->mapel->mapel ?? '-', // ambil nama mapel
                    'guru_id' => $item->guru_id,
                    'guru_nama' => $item->guru?->name ?? '-', // ambil nama guru
                    'kelas_id' => $item->kelas_id,
                    'kelas_nama' => $item->kelas?->name ?? '-', // ambil nama kelas
                    'wali_kelas' => optional(optional($item->kelas)->waliKelas)->name,
                    'tahun' => $item->tahun,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            });
            return response()->json([
                'data' => $data,
            ]);
        }

        $paginator = $query->paginate(5);

        $data = collect($paginator->items())->map(function ($item) {
            return [
                'id' => $item->id,
                'hari' => $item->hari,
                'jam_ke' => $item->jam_ke,
                'jam' => $item->jam,
                'mapel_id' => $item->mapel_id,
                'mapel_nama' => $item->mapel->mapel ?? '-',
                'guru_id' => $item->guru_id,
                'guru_nama' => $item->guru?->name ?? '-',
                'kelas_id' => $item->kelas_id,
                'kelas_nama' => $item->kelas?->name ?? '-',
                'wali_kelas' => optional(optional($item->kelas)->waliKelas)->name,
                'tahun' => $item->tahun,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    public function createJadwalMataPelajaran(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 20);
        $currentPage = $request->input('currentPage', 1);
        $kelas = $request->input('kelas');
        $kelasId = $request->input('kelas_id');
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
        $days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $jadwal = [];

        for ($i = 1; $i <= 10; $i++) {
            $jamInfo = $this->getJamRangeInline($i);

            $row = [
                'jam_ke' => $jamInfo['jam_ke'],
                'jam' => $jamInfo['jam'],
                'jadwal' => [],
            ];

            // Ganti `first()` dengan `filter()` untuk mendapatkan semua mapel yang cocok dengan hari dan jam
            foreach ($days as $day) {
                $foundMapels = $master_mapel->filter(function ($item) use ($day, $i) {
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

        $classes_for_student_with_wali_kelas = collect($classes_for_student->items())->map(function ($class) {
            return [
                'id' => $class->id,
                'name' => $class->name,
                'wali_kelas' => $class->waliKelas?->name ?? 'Tidak Diketahui',
                'tahun_ajaran' => $class->tahun_ajaran ?? 'Tidak Diketahui',
            ];
        });

        $filterData = compact('kelas');

        /// \Log::info("🔍 Jadwal Data: ", $jadwal);

        return inertia('MataPelajaran/createJadwalMataPelajaran', [
            'master_mapel' => MapelResource::collection($master_mapel),
            'jadwal' => $jadwal,
            'teachers' => $teachers,
            'filter' => $filterData,
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
            'kelas_id' => 'required|integer|exists:classes,id',
            'entries' => 'required|array',
            'entries.*.hari' => 'required|string|in:senin,selasa,rabu,kamis,jumat,sabtu',
            'entries.*.jam_ke' => 'required|integer',
            'entries.*.jam' => 'required|string',
            'entries.*.mapel_id' => 'required|integer|exists:master_mapel,id',
            'entries.*.wali_kelas' => 'nullable|string|max:255',
            'entries.*guru' => 'nullable|string|max:255',
            'entries.*.guru_id' => 'required|numeric|exists:teachers,id',
            'tahun_ajaran' => 'nullable|string',
        ]);

        try {
            $kelas = Classes::with('waliKelas')->find($validatedData['kelas_id']);
            $waliKelas = $kelas?->waliKelas?->name ?? 'Tidak Diketahui';
            $tahunAjaran = $validatedData['tahun_ajaran'] ?? now()->year;

            foreach ($validatedData['entries'] as $entry) {
                $jamKe = (string) $entry['jam_ke'];
                $hari = strtolower($entry['hari']);

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
                                'mapel_id' => $existing->mapel_id,
                                'guru_id' => $existing->guru_id,
                                'guru_name' => $guruName,
                                'mapel_name' => $mapelName,
                            ]
                        ], 422);
                    }
                }

                JadwalMataPelajaran::updateOrCreate(
                    [
                        'kelas_id' => $validatedData['kelas_id'],
                        'hari' => $hari,
                        'jam_ke' => $jamKe,
                    ],
                    [
                        'jam' => $entry['jam'],
                        'mapel_id' => $entry['mapel_id'],
                        'wali_kelas' => $entry['wali_kelas'] ?? $waliKelas,
                        'tahun' => $tahunAjaran,
                        'guru_id' => $entry['guru_id'] ?? $request->guru_id ?? null,
                        'guru' => $entry['guru'] ?? $request->guru ?? null,
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
                        'jam' => $jadwal->jam,
                        'jadwal' => [],
                    ];
                    foreach ($hariList as $h) {
                        $struktur[$jamKe]['jadwal'][$h] = null;
                    }
                }

                $struktur[$jamKe]['jadwal'][$hari] = [
                    'mapel' => $jadwal->mapel->mapel ?? '-',
                    'mapel_id' => $jadwal->mapel_id,
                    'kelas' => $jadwal->kelas->name ?? '-',
                    'guru' => optional($jadwal->guru)->name ?? '-',
                    'guru_id' => $jadwal->guru_id ?? null,
                    'wali_kelas' => $jadwal->wali_kelas ?? '-',
                    'tahun' => $jadwal->tahun ?? '-',
                ];
            }

            ksort($struktur);

            return response()->json([
                'message' => '✅ Jadwal berhasil disimpan!',
                'schedule' => array_values($struktur),
                'kelas_id' => $validatedData['kelas_id'],
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'message' => '❌ Gagal menyimpan jadwal (database error).',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '❌ Gagal menyimpan jadwal.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getJadwal(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|integer|exists:classes,id',
        ]);

        $kelasId = $validated['kelas_id'];
        $itemsPerPage = $request->input('itemsPerPage', 5);
        $page = $request->input('page', 1);

        // Ambil semua jadwal, relasi guru tunggal (guru_id), dan wali_kelas
        $jadwals = JadwalMataPelajaran::with([
            'mapel.guru',        // belongsTo
            'kelas.waliKelas',    // belongsTo
            'guru',
        ])
            ->where('kelas_id', $kelasId)
            ->paginate(1000);

        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
        $entries = [];

        foreach ($jadwals as $jadwal) {
            $jamKe = $jadwal->jam_ke;
            $hari = strtolower($jadwal->hari);
            $guru = $jadwal->mapel->guru;

            if (!isset($entries[$jamKe])) {
                $entries[$jamKe] = [
                    'jam_ke' => $jamKe,
                    'jam' => $jadwal->jam,
                    'jadwal' => [],
                ];

                foreach ($hariList as $h) {
                    $entries[$jamKe]['jadwal'][$h] = null;
                }
            }

            $entries[$jamKe]['jadwal'][$hari] = [
                'mapel' => $jadwal->mapel->mapel ?? '-',
                'mapel_id' => $jadwal->mapel_id,
                'kelas' => $jadwal->kelas->name ?? '-',
                'guru' => optional($jadwal->guru)->name ?? '-',
                'guru_id' => $jadwal->guru_id ?? null,
                'wali_kelas' => $jadwal->kelas->waliKelas->name ?? '-',
                'tahun' => $jadwal->tahun ?? '-',
            ];
        }
        // Ubah ke collection dan sort
        $entriesCollection = collect($entries)->sortKeys();

        // Ambil slice untuk halaman sekarang
        $paginated = new LengthAwarePaginator(
            $entriesCollection->forPage($page, $itemsPerPage)->values(),
            $entriesCollection->count(),
            $itemsPerPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json($paginated);
    }

    public function getAllJadwal(Request $request)
    {

        $perPage = $request->input('per_page', 5);
        $page = $request->input('page', 1);

        $allKelasIds = JadwalMataPelajaran::distinct('kelas_id')->pluck('kelas_id');

        $kelasIds = $allKelasIds->slice(($page - 1) * $perPage, $perPage)->values();

        if ($kelasIds->isEmpty()) {
            return response()->json([
                'meta' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $allKelasIds->count(),
                    'last_page' => ceil($allKelasIds->count() / $perPage),
                    'from' => 0,
                    'to' => 0,
                ],
                'links' => [
                    'first' => url()->current() . '?page=1',
                    'last' => url()->current() . '?page=' . ceil($allKelasIds->count() / $perPage),
                    'prev' => $page > 1 ? url()->current() . '?page=' . ($page - 1) : null,
                    'next' => $page < ceil($allKelasIds->count() / $perPage) ? url()->current() . '?page=' . ($page + 1) : null,
                ],
                'data' => [],
            ]);
        }
        $jadwals = JadwalMataPelajaran::with([
            'mapel.teachers',
            'kelas.waliKelas',
            'guru',
        ])->whereIn('kelas_id', $kelasIds)->get();


        $hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];

        $flatEntries = [];

        foreach ($jadwals as $jadwal) {
            $jamKe = $jadwal->jam_ke;
            $hari = strtolower($jadwal->hari);
            $kelasId = $jadwal->kelas_id;
            $guru = $jadwal->mapel->teachers->firstWhere('id', $jadwal->guru_id);

            // Cari existing entry berdasarkan kelas_id dan jam_ke
            $index = null;
            foreach ($flatEntries as $i => $entry) {
                if ($entry['kelas_id'] === $kelasId && $entry['jam_ke'] === $jamKe) {
                    $index = $i;
                    break;
                }
            }

            if ($index === null) {
                // Buat entry baru dengan jadwal semua hari null dulu
                $jadwalHari = [];
                foreach ($hariList as $h) {
                    $jadwalHari[$h] = null;
                }

                $flatEntries[] = [
                    'kelas_id' => $kelasId,
                    'jam_ke' => $jamKe,
                    'jam' => $jadwal->jam,
                    'jadwal' => $jadwalHari,
                ];

                $index = count($flatEntries) - 1;
            }

            // Set jadwal hari yang sesuai
            $flatEntries[$index]['jadwal'][$hari] = [
                'mapel' => $jadwal->mapel->mapel ?? '-',
                'mapel_id' => $jadwal->mapel_id,
                'kelas' => $jadwal->kelas->name ?? '-',
                'kelas_id' => $kelasId,
                'guru' => $guru?->name ?? '-',
                'guru_id' => $guru?->id ?? '-',
                'wali_kelas' => $jadwal->kelas->waliKelas->name ?? '-',
                'tahun' => $jadwal->tahun ?? '-',
            ];
        }

        // Urutkan berdasarkan kelas_id dan jam_ke
        usort($flatEntries, function ($a, $b) {
            if ($a['kelas_id'] === $b['kelas_id']) {
                return $a['jam_ke'] <=> $b['jam_ke'];
            }
            return $a['kelas_id'] <=> $b['kelas_id'];
        });
        return response()->json([
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $allKelasIds->count(),
                'last_page' => ceil($allKelasIds->count() / $perPage),
                'from' => $kelasIds->isEmpty() ? 0 : ($page - 1) * $perPage + 1,
                'to' => $kelasIds->isEmpty() ? 0 : ($page - 1) * $perPage + $kelasIds->count(),
                'links' => [
                    'first' => url()->current() . '?page=1',
                    'last' => url()->current() . '?page=' . ceil($allKelasIds->count() / $perPage),
                    'prev' => $page > 1 ? url()->current() . '?page=' . ($page - 1) : null,
                    'next' => $page < ceil($allKelasIds->count() / $perPage) ? url()->current() . '?page=' . ($page + 1) : null,
                ],
            ],
            'data' => $flatEntries,
        ]);
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

    // Menampilkan form untuk edit mata pelajaran
    public function edit($id, Request $request)
    {
        $mapel = Mapel::find($id);
        if (!$mapel) {
            return inertia('MataPelajaran/Error', ['message' => 'Mata Pelajaran tidak ditemukan']);
        }

        // Ambil data tambahan
        $teachers = Teacher::all(['id', 'name']);
        $classes = $this->getClasses($request);
        $filter = $request->only(['kelas']);
        $kelasId = $request->input('kelas_id');
        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);

        $itemsPerPage = $request->input('itemsPerPage', 5);
        $currentPage = $request->input('currentPage', 1);
        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only(array_merge(['search'], array_keys($filter), ['itemsPerPage', 'currentPage'])));

        $jadwal = $this->generateJadwalMingguan($allMapel);
        $schedule = $kelasId ? $this->getScheduleByKelas($kelasId) : [];

        return inertia('MataPelajaran/edit', [
            'mapel' => new MapelResource($mapel),
            'master_mapel' => $master_mapel,
            'jadwal' => $jadwal,
            'kelas_id' => (int) $kelasId,
            'schedule' => $schedule,
            'filter' => $filter,
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

    public function editJadwal($id, Request $request)
    {
        $mapel = Mapel::find($id);
        if (!$mapel) {
            return inertia('MataPelajaran/Error', ['message' => 'Mata Pelajaran tidak ditemukan']);
        }

        $teachers = Teacher::all(['id', 'name']);
        $classes = Classes::all();

        $kelasId = (int) $request->kelas_id;
        if (!$kelasId && $request->filled('kelas')) {
            $kelas = Classes::where('name', $request->kelas)->first();
            $kelasId = $kelas?->id ?? 0;
        }


        if ($kelasId === 0 && $request->filled('kelas')) {
            $kelasName = $request->input('kelas');
            $kelas = Classes::where('name', $kelasName)->first();

            if ($kelas) {
                $kelasId = $kelas->id;
            } else {
                // Jika tidak ketemu, kamu bisa log untuk debug
                \Log::warning("❌ Kelas dengan nama '{$kelasName}' tidak ditemukan di tabel classes.");
            }
        }

        $filter = ['kelas_id' => $kelasId];
        [$mapelQuery, $allMapel] = $this->getFilteredMapel($filter, $kelasId);

        $itemsPerPage = $request->input('itemsPerPage', 5);
        $currentPage = $request->input('currentPage', 1);

        $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
            ->appends($request->only(['search', 'kelas_id', 'itemsPerPage', 'currentPage']));

        $jadwal = $this->generateJadwalMingguan($allMapel);
        $schedule = $kelasId ? $this->getScheduleByKelas($kelasId) : [];

        // Cari prefill berdasarkan kombinasi unik
        $prefillJadwal = null;
        if ($kelasId && $request->filled('jam_ke') && $request->filled('hari')) {
            $prefillJadwal = JadwalMataPelajaran::where([
                ['mapel_id', $mapel->id],
                ['kelas_id', $kelasId],
                ['jam_ke', $request->jam_ke],
                ['hari', $request->hari],
            ])->first();
        }

        // Data prefill awal dari request
        $prefill = [
            'jam_ke' => $request->input('jam_ke'),
            'hari' => $request->input('hari'),
            'guru_id' => $request->input('guru_id'),
            'wali_kelas' => $request->input('wali_kelas'),
            'kelas' => $request->input('kelas'),
            'jam' => $request->input('jam'),
            'tahun' => $request->input('tahun'),
            'kelas_id' => $kelasId,
            'id' => null, // default null
        ];

        // Jika ditemukan di DB, timpa dengan data dari DB
        if ($prefillJadwal) {
            $prefill['guru_id'] = $prefillJadwal->guru_id;
            $prefill['jam'] = $prefillJadwal->jam;
            $prefill['tahun_ajaran'] = $prefillJadwal->tahun;
            $prefill['id'] = $prefillJadwal->id; // ⬅️ ID ini penting untuk update
        }

        return inertia('MataPelajaran/editJadwalMapel', [
            'mapel' => new MapelResource($mapel),
            'master_mapel' => $master_mapel,
            'jadwal' => $jadwal,
            'kelas_id' => $kelasId,
            'schedule' => $schedule,
            'filter' => $filter,
            'classes_for_student' => ['data' => $classes],
            'wali_kelas' => ['data' => $teachers],
            'teachers' => $teachers,
            'jabatans' => \App\Models\MasterJabatan::all(['id', 'nama_jabatan']),
            'teacher' => $mapel->teacher ?? null,
            'prefill' => $prefill,
        ]);
    }

    public function updateJadwal(Request $request, $id)
    {
        \Log::info('🛠 UPDATE REQUEST DITERIMA', [
            'route_id' => $id,
            'request_all' => $request->all(),
        ]);

        try {
            $validated = Validator::make($request->all(), [
                'mapel_id' => 'required|exists:master_mapel,id',
                'hari' => 'required|string|max:20',
                'jam_ke' => 'required|integer|min:1|max:9',
                'kelas_id' => 'required|exists:classes,id',
                'teacher_id' => 'required|exists:teachers,id',
                'tahun_ajaran' => 'required|string|max:50',
            ])->validate();

            $jadwal = JadwalMataPelajaran::find($id);

            if (!$jadwal) {
                \Log::warning('❌ Jadwal tidak ditemukan untuk update.', ['id' => $id]);
                return response()->json(['message' => 'Jadwal tidak ditemukan.'], 404);
            }

            // ✅ Cek bentrok
            $jadwalBentrok = JadwalMataPelajaran::where('id', '!=', $id)
                ->where('hari', $validated['hari'])
                ->where('jam_ke', $validated['jam_ke'])
                ->where('kelas_id', $validated['kelas_id'])
                ->first();

            if ($jadwalBentrok) {
                \Log::warning('❌ Jadwal bentrok ditemukan.', ['bentrok_id' => $jadwalBentrok->id]);
                return response()->json([
                    'message' => '❌ Jadwal bentrok! Sudah ada jadwal lain di jam & hari tersebut untuk kelas ini.',
                    'bentrok_id' => $jadwalBentrok->id,
                ], 409);
            }

            // ✅ Update data
            $jadwal->update([
                'mapel_id' => $validated['mapel_id'],
                'hari' => $validated['hari'],
                'jam_ke' => $validated['jam_ke'],
                'kelas_id' => $validated['kelas_id'],
                'guru_id' => $validated['teacher_id'],
                'tahun' => $validated['tahun_ajaran'],
                'jam' => $request->input('jam', $jadwal->jam),
            ]);

            return response()->json([
                'message' => '✅ Jadwal berhasil diperbarui.',
                'data' => $jadwal->fresh()
            ]);
        } catch (\Throwable $e) {
            \Log::error('❌ Gagal update jadwal:', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Internal Server Error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteJadwal($id)
    {
        $jadwal = JadwalMataPelajaran::find($id);
        if (!$jadwal) {
            return response()->json(['message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal berhasil dihapus.');

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
