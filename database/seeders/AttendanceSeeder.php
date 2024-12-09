<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $absensi = [
            [
                'student_id' => 1,
                'status_kehadiran' => 'P',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
            [
                'student_id' => 2,
                'status_kehadiran' => 'P',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0) 
            ],
            [
                'student_id' => 3,
                'status_kehadiran' => 'A',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
            [
                'student_id' => 1,
                'status_kehadiran' => 'A',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
            [
                'student_id' => 2,
                'status_kehadiran' => 'P',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
            [
                'student_id' => 3,
                'status_kehadiran' => 'A',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
            [
                'student_id' => 4,
                'status_kehadiran' => 'A',
                'tanggal_kehadiran' => Carbon::create(2024, 2, 1, 0, 0, 0)
            ],
        ];

        Attendance::insert($absensi);
    }
}
