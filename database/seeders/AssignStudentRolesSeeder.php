<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignStudentRolesSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua user dengan id antara 20 dan 29
        $students = User::whereBetween('id', [20, 29])->get();

        foreach ($students as $student) {
            // Assign peran 'Student' ke setiap user
            $student->assignRole('student');
        }
    }
}