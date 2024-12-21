<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Section;
use App\Http\Resources\MapelResource;
use Illuminate\Http\Request;
use App\Http\Requests\StoreMapelRequest;

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

    /*
     'id_mapel' => $this->id_mapel,
            'kode_mapel' => $this->kode_mapel,
            'mapel' => $this->mapel,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
    */

    public function store(StoreMapelRequest $request)
    {
        Mapel::create($request->validated());
        return redirect()->route('matapelajaran.index');
    }

    public function getSections(Request $request)
    {
        $mapelId = $request->query('class_id');
        if (!$mapelId) {
            return response()->json(['error' => 'Class ID is required'], 400);
        }

        $sections = Section::where('class_id', $mapelId)->get();

        return response()->json($sections);
    }

    public function update(StoreMapelRequest $request, $id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->update($request->validated());
        return redirect()->route('matapelajaran.index');
    }

    public function destroy($id)
    {
        $mapel = Mapel::findOrFail($id);
        $mapel->delete();
        return redirect()->route('matapelajaran.index');
    }
}
