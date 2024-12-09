<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
{
    public function run()
    {
        DB::table('wali_kelas')->insert([
            ['class_id' => 1, 'name' => 'Bambang Nurdhiari, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 2, 'name' => 'Rully Ardiansyah, S.Pd., Gr', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 3, 'name' => 'Dra. Darmi Sri Astuti', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 4, 'name' => 'Dwi Rofikoh, S.Hum', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 5, 'name' => 'Sri Puji Lestari, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 6, 'name' => 'Aflah Aghniawan, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 7, 'name' => 'Ernanda Dhimas, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 8, 'name' => 'Hariyati Prasetyorini, M.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 9, 'name' => 'Ratna Wijayanti, S.S', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 10, 'name' => 'Adjib Hidayat, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 11, 'name' => 'Suryandari Valentina Hapsariputri, S.Psi', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 12, 'name' => 'Yosi Trisa, M.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 13, 'name' => 'Slamet Santoso, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 14, 'name' => 'Herry Poerwanto, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 15, 'name' => 'Diana Ardianti, S.Pd', 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => 16, 'name' => 'Dwi Purnama, S.Si', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
