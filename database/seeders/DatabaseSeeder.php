<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsersTableSeeder::class,
            GenderSeeder::class,
            ReligionSeeder::class,
            LaratrustSeeder::class,
            NoIndukSeeder::class,
            AttendanceSeeder::class,
            ClassesSeeder::class,
            TeacherSeeder::class,
        ]);
    }
}