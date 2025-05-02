<?php
namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\Enrollment; 
use Illuminate\Http\Request;


class MarkController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'cognitive_1' => 'nullable|numeric',
            'cognitive_2' => 'nullable|numeric',
            'cognitive_pas' => 'nullable|numeric',
            'cognitive_average' => 'nullable|numeric',
            'skill_1' => 'nullable|numeric',
            'skill_2' => 'nullable|numeric',
            'skill_pas' => 'nullable|numeric',
            'skill_average' => 'nullable|numeric',
            'final_mark' => 'nullable|numeric',
            'mark' => 'required|numeric',
            'status' => 'nullable|string',
            'no_kd' => 'nullable|string',
            'enrollment_date' => 'nullable|date',
        ]);
    
        $enrollment = Enrollment::find($validated['enrollment_id']);
    
        if (!$enrollment) {
            return response()->json(['message' => 'Enrollment not found'], 404);
        }
    
        // Update semua nilai penilaian
        $enrollment->update($validated);
    
        return response()->json($enrollment, 200);
    }
    
    
}
