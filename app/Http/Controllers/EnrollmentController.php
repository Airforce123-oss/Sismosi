<?php

namespace App\Http\Controllers;
use App\Models\Mapel;
use App\Models\Student;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function membuatEnrollment()
    {
        // Ambil data siswa dan mata pelajaran
        $students = Student::all();
        $courses = Mapel::all();

        // Kirim data ke frontend menggunakan Inertia
        return inertia('Teachers/Enrollment/membuatEnrollment', [
            'students' => $students,
            'courses' => $courses,
        ]);
    }
    /*
       public function membuatEnrollment()
    {
        return inertia('Teachers/Enrollment/membuatEnrollment');
    }
    */
}

