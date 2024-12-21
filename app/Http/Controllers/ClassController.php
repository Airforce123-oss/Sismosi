<?php

namespace App\Http\Controllers;

use App\Models\ClassesForStudent;
use App\Http\Requests\StoreClassesRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->input('itemsPerPage', 10); // Default to 10 items per page
        $currentPage = $request->input('currentPage', 1); // Default to the first page

        $classesQuery = ClassesForStudent::query();
        $classes_for_student = $classesQuery->paginate($itemsPerPage, ['*'], 'page', $currentPage)
                               ->appends($request->only('search', 'itemsPerPage', 'currentPage'));

        return inertia('Kelas/index', [
            'classes_for_student' => $classes_for_student,
        ]);
    }

    public function indexApi(Request $request)
    {
        $classesQuery = ClassesForStudent::query();

        // Terapkan filter pencarian jika ada
        $this->applySearch($classesQuery, $request->search);

        // Urutkan berdasarkan ID kelas
        $classesQuery->orderBy('id');

        // Pagination, dengan jumlah per halaman 5
        $classes = $classesQuery->paginate(5)->appends($request->only('search'));

        return response()->json($classes);
    }

    public function create()
    {
        return inertia('Kelas/create');
    }

    public function store(StoreClassesRequest $request)
    {
        ClassesForStudent::create($request->validated());
        return redirect()->route('kelas.index');
    }

    public function show($id)
    {
        $classForStudent = ClassesForStudent::findOrFail($id);
        return inertia('Kelas/show', [
            'classForStudent' => $classForStudent
        ]);
    }

    public function edit($id)
    {
        $classForStudent = ClassesForStudent::findOrFail($id);

        return inertia('Kelas/edit', [
        'classForStudent' => $classForStudent
        ]);
    }

    public function update(StoreClassesRequest $request, $id)
    {
        $classForStudent = ClassesForStudent::findOrFail($id);
        $classForStudent->update($request->validated());
        return redirect()->route('kelas.index');
    }

    public function destroy($id)
    {
        $classForStudent = ClassesForStudent::findOrFail($id);
        $classForStudent->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
