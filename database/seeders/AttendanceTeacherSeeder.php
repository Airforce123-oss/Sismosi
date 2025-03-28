<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceTeacher;

class AttendanceTeacherSeeder extends Seeder
{
    public function run()
    {
        AttendanceTeacher::create([
            'id' => 1,
            'class_id' => 2,
            'attendance_date' => now(),
            'is_present' => true,
        ]);
    }
    
}