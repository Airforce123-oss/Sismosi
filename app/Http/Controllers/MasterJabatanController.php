<?php

namespace App\Http\Controllers;

use App\Models\MasterJabatan;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Resources\MasterJabatanResource;
use Illuminate\Support\Facades\Log; 
class MasterJabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexMasterJabatan(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->roles->first()?->name ?? 'guest';

        // Ambil pagination dan query
        $perPage = $request->input('perPage', 2);
        $currentPage = $request->input('page', 1);

        $query = MasterJabatan::query();

        // Optional: pencarian jika ingin nanti
        if ($search = $request->input('search')) {
            $query->where('nama_jabatan', 'like', '%' . $search . '%');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $currentPage)
                        ->appends($request->only('search', 'perPage', 'page'));

        // Transformasi pakai Resource + resolve agar jadi array
        $jabatanData = MasterJabatanResource::collection($paginator->items())->resolve();

        return Inertia::render('MasterJabatan/index', [
            'jabatan' => [
                'data' => $jabatanData,
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

    public function create()
    {
         return Inertia::render('MasterJabatan/create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi'    => 'nullable|string|max:1000',
        ]);

        // Simpan ke database
        MasterJabatan::create($validated);

        // Redirect kembali ke index dengan pesan sukses (optional)
        return redirect()->route('master-jabatan.index')->with('success', 'Data jabatan berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterJabatan $master_jabatan)
    {
        return Inertia::render('MasterJabatan/edit', [
          'jabatan' => new MasterJabatanResource($master_jabatan),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterJabatan $master_jabatan)
    {
        $request->validate([
            'nama_jabatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        $master_jabatan->update([
            'nama_jabatan' => $request->nama_jabatan,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('master-jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterJabatan $masterJabatan)
    {
        //
    }
}
