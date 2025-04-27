<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;

class StudentUserSeeder extends Seeder
{
    public function run()
    {
        // Temukan user berdasarkan email
        $user = User::where('email', 'student@gmail.com')->first();
        
        // Temukan student berdasarkan nama
        $student = Student::where('name', 'Student!')->first();

        // Jika ditemukan, hubungkan student ke user
        if ($user && $student) {
            $student->user_id = $user->id; // Atur user_id di tabel students
            $student->save(); // Simpan perubahan
        }
    }
}
