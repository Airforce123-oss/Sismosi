<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Section;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMapelRequest;
use Illuminate\Support\Facades\Log;


class MataPelajaranController extends Controller
{
    public function mataPelajaran(Request $request)
    {

        $itemsPerPage = $request->input('itemsPerPage', 10); // Default to 10 items per page
        $currentPage = $request->input('currentPage', 1); // Default to the first page

       $mapelQuery = Mapel::query();
       $master_mapel = $mapelQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
                               ->appends($request->only('search', 'itemsPerPage', 'currentPage'));


        return inertia('MataPelajaran/index', [
            'master_mapel' => MapelResource::collection($master_mapel),
        ]);
    }

    public function create(){
        $mapel = MapelResource::collection(Mapel::all());

        return inertia('MataPelajaran/create', [
            'kode_mapel' => $mapel,
        ]);
    }
    public function store(Request $request)
    {
        // Validasi data input
        $validated = $request->validate([
            'kode_mapel' => 'required|string|max:40',
            'mapel' => 'required|string|max:60',
        ]);
    
        // Cek apakah kode_mapel sudah ada
        $existingMapel = Mapel::where('kode_mapel', $validated['kode_mapel'])->first();
        if ($existingMapel) {
            // Jika sudah ada, kembalikan dengan pesan error menggunakan Inertia
            return redirect()->back()->withErrors(['kode_mapel' => 'Kode Mapel sudah ada.'])->withInput();
        }
    
        // Jika belum ada, simpan data baru
        Mapel::create($validated);
    
        // Kembalikan respons sukses dengan Inertia
        return redirect()->route('matapelajaran.index')->with('success', 'Data berhasil disimpan!');
    }
    

    public function update(StoreMapelRequest $request, $id_mapel)
    {
        $mapel = Mapel::findOrFail(id: $id_mapel);
        $mapel->update($request->validated());
        return redirect()->route('matapelajaran.index');
    }

    public function destroy($id_mapel)
    {
        $mapel = Mapel::findOrFail($id_mapel);
        $mapel->delete();
        return redirect()->route('matapelajaran.index');
    }
}

