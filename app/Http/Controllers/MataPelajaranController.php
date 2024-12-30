<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function show($id_mapel)
{
    $mapel = Mapel::find($id_mapel);  // Mengambil data berdasarkan id_mapel
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
            'id_mapel' => $newMapel->id_mapel,
            'kode_mapel' => $newMapel->kode_mapel,
            'mapel' => $newMapel->mapel,
        ]);

        return redirect()->route('matapelajaran.index')->with('success', 'Data berhasil disimpan!');
    }

    // Mengupdate mata pelajaran yang sudah ada
    public function update(Request $request, $id_mapel)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:40|unique:master_mapel,kode_mapel,' . $id_mapel . ',id_mapel', // Validasi unik
            'mapel' => 'required|string|max:60',
        ]);
    
        $mapel = Mapel::findOrFail($id_mapel); // Cari berdasarkan id_mapel
        $mapel->update($validated); // Perbarui data
    
        Log::info('Successfully updated mata pelajaran', [
            'id_mapel' => $id_mapel,
            'updated_data' => $validated,
        ]);
    
        return redirect()->route('matapelajaran.index')->with('success', 'Data berhasil diperbarui!');
    }
    
    
    // Menampilkan form untuk edit mata pelajaran
    public function edit($id_mapel)
    {
        $mapel = Mapel::find($id_mapel); // Mengambil data berdasarkan id_mapel
        if (!$mapel) {
            return redirect()->route('matapelajaran.index')->withErrors(['message' => 'Mata Pelajaran tidak ditemukan']);
        }
    
        return inertia('MataPelajaran/edit', [
            'mapel' => new MapelResource($mapel),
        ]);
    }
    

    // Menghapus mata pelajaran
    public function destroy($id_mapel)
    {
        $mapel = Mapel::findOrFail($id_mapel); // Mengambil data berdasarkan id_mapel
    
        Log::info('Deleting mata pelajaran', [
            'id_mapel' => $id_mapel,
            'kode_mapel' => $mapel->kode_mapel,
            'mapel' => $mapel->mapel,
        ]);
    
        $mapel->delete();
    
        return redirect()->route('matapelajaran.index');
    }
    
}
