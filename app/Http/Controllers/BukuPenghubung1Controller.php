<?php

namespace App\Http\Controllers;

use App\Models\BukuPenghubung1;
use Illuminate\Http\Request;
use App\Http\Resources\ClassesResource;
use App\Models\Classes;

class BukuPenghubung1Controller extends Controller
{
    public function index()
    {
        $entries = BukuPenghubung1::all();
        $classes_for_student = Classes::all();
        return inertia('Teachers/BukuPenghubung1/index', ['entries' => $entries,
        'classes_for_student' => $classes_for_student,
    ]);
    }

    public function store(Request $request)
    {

        $classes = ClassesResource::collection(Classes::all());
        $validated = $request->validate([
            'date' => 'required|date',
            'parentName' => 'required|string|max:255',
            'studentName' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'class' => 'required|string|max:255',
            'issue' => 'required|string',
            'action' => 'required|string',
            'note' => 'nullable|string',
        ]);

        BukuPenghubung1::create($validated);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, BukuPenghubung1 $bukuPenghubung1)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'parentName' => 'required|string|max:255',
            'studentName' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'class' => 'required|string|max:255',
            'issue' => 'required|string',
            'action' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $bukuPenghubung1->update($validated);

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(BukuPenghubung1 $bukuPenghubung1)
    {
        $bukuPenghubung1->delete();
        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
