<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            //UsersTableSeeder::class,
            StudentUserSeeder::class,
            GenderSeeder::class,
            ReligionSeeder::class,
            LaratrustSeeder::class,
            NoIndukSeeder::class,
            AttendanceSeeder::class,
            ClassesSeeder::class,
            TeacherSeeder::class,
            PerbaikiRelasiUserIdSeeder::class,
            AssignStudentRolesSeeder::class,
        ]);
        $user = User::find(2); // Cari pengguna dengan ID 2
        if ($user) {
            $user->assignRole('teacher'); // Menetapkan role 'teacher' ke pengguna
        }
    }
}