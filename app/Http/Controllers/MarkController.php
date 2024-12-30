<?php
namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Enrollment; 
use Illuminate\Http\Request;


class MarkController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'mark' => 'required|numeric',
        ]);
    
        // Menyimpan mark dengan menggunakan enrollment_id
        $enrollment = Enrollment::find($validated['enrollment_id']);
    
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    
        // Menambahkan mark pada enrollment
        $enrollment->mark = $validated['mark'];
        $enrollment->save();
    
        // Kembalikan response dengan data yang sudah diperbarui
        return response()->json($enrollment, 200);
    }
    
}
