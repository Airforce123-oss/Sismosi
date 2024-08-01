<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('wali_kelas')->insert([
            ['name' => 'Bambang Nurdhiari D, S.Pd', 'class' => 'X-1'],
            ['name' => 'Rully Ardiansyah, S.Pd., Gr', 'class' => 'X-2'],
            ['name' => 'Dra. Darmi Sri Astuti', 'class' => 'X-3'],
            ['name' => 'Dwi Rofikoh, S.Hum', 'class' => 'X-4'],
            ['name' => 'Sri Puji Lestari, S.Pd', 'class' => 'X-5'],
            ['name' => 'Aflah Aghniawan, S.Pd', 'class' => 'X-6'],
            ['name' => 'Ernanda Dhimas, S.Pd', 'class' => 'XI-1'],
            ['name' => 'Hariyati Prasetyorini, M.Pd', 'class' => 'XI-2'],
            ['name' => 'Ratna Wijayanti, S.S', 'class' => 'XI-3'],
            ['name' => 'Adjib Hidayat, S.Pd', 'class' => 'XI-4'],
            ['name' => 'Suryandari Valentina Hapsariputri, S.Psi', 'class' => 'XI-5'],
            ['name' => 'Yosi Trisa, M.Pd', 'class' => 'XI-6'],
            ['name' => 'Slamet Santoso, S.Pd', 'class' => 'XII-1'],
            ['name' => 'Herry Poerwanto, S.Pd', 'class' => 'XII-2'],
            ['name' => 'Diana Ardianti, S.Pd', 'class' => 'XII-3'],
            ['name' => 'Dwi Purnama, S.Si', 'class' => 'XII-4'],
        ]);
    }
}
