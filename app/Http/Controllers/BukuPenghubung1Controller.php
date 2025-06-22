<?php

namespace App\Http\Controllers;

use App\Models\BukuPenghubung1;
use Illuminate\Http\Request;
use App\Models\Classes;

class BukuPenghubung1Controller extends Controller
{
    public function index()
    {
        $entries = BukuPenghubung1::with('class')->paginate(5);
        $classes_for_student = Classes::all();

        return inertia('Teachers/BukuPenghubung1/index', [
            'entries' => $entries,
            'classes_for_student' => $classes_for_student,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'parentName' => 'required|string|max:255',
            'studentName' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'class_id' => 'required|exists:classes,id',
            'issue' => 'required|string',
            'action' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $created = BukuPenghubung1::create($validated);

        return response()->json([
            'message' => 'Data berhasil ditambahkan.',
            'data' => $created
        ]);
    }

    public function update(Request $request, BukuPenghubung1 $bukuPenghubung1)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'parentName' => 'required|string|max:255',
            'studentName' => 'required|string|max:255',
            'gender' => 'required|in:L,P',
            'class_id' => 'required|exists:classes,id',
            'issue' => 'required|string',
            'action' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $bukuPenghubung1->update($validated);

        return response()->json([
            'message' => 'Data berhasil diperbarui.',
            'data' => $bukuPenghubung1
        ]);
    }


    public function destroy(BukuPenghubung1 $bukuPenghubung1)
    {
        $bukuPenghubung1->delete();

        return response()->json([
            'message' => 'Data berhasil dihapus.'
        ]);
    }

}
