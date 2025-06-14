<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PerbaikiRelasiUserIdSeeder extends Seeder
{
    public function run()
    {
        $students = Student::with('noInduk')
            ->where('user_id', 18) // Hanya data lama yang pakai user_id=18
            ->get()
            ->groupBy(function ($student) {
                return $student->noInduk->no_induk;
            });

        foreach ($students as $noInduk => $groupedStudents) {
            // Cek apakah user dengan username = no_induk sudah ada
            $existingUser = User::where('username', $noInduk)->first();

            if (!$existingUser) {
                // Jika belum ada, buat akun baru
                $user = User::create([
                    'name' => 'Student',
                    'username' => $noInduk,
                    'email' => $noInduk . '@gmail.com', 
                    'password' => Hash::make($noInduk),
                    'role_name' => 'student',
                    'remember_token' => Str::random(10),
                ]);
            } else {
                $user = $existingUser;
            }

            // Update semua siswa dengan no_induk ini agar user_id-nya sesuai
            foreach ($groupedStudents as $student) {
                $student->user_id = $user->id;
                $student->save();
            }
        }
    }
}