<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MataPelajaranController extends Controller
{
    public function mataPelajaran(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 10); // Default to 10 items per page
        $currentPage = $request->input('currentPage', 1); // Default to the first page

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

    public function update(Request $request, $id_mapel)
    {
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:40|unique:master_mapel,kode_mapel,' . $id_mapel . ',id_mapel', // Pastikan validasi unik
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
    
    public function edit(Mapel $mapel) // Menggunakan model Mapel
    {
        Log::info('Fetching data for mata pelajaran edit', [
            'id_mapel' => $mapel->id_mapel,
            'kode_mapel' => $mapel->kode_mapel,
            'mapel' => $mapel->mapel,
        ]);

        return inertia('MataPelajaran/edit', [
            'mapel' => new MapelResource($mapel),
        ]);
    }

    public function destroy($id_mapel)
    {
        $mapel = Mapel::findOrFail($id_mapel); // Menggunakan model Mapel

        Log::info('Deleting mata pelajaran', [
            'id_mapel' => $id_mapel,
            'kode_mapel' => $mapel->kode_mapel,
            'mapel' => $mapel->mapel,
        ]);

        $mapel->delete();

        return redirect()->route('matapelajaran.index');
    }
}
