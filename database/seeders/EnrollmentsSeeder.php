<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('enrollments')->insert([
            'student_id' => 1,
            'mapel_id' => 1,
            'enrollment_date' => Carbon::now(),
            'status' => 'active',
            'description' => 'Description text',
            'no_kd' => 'KD001',
            'mark' => 75,
            'cognitive_1' => 85.50,
            'cognitive_2' => 90.75,
            'cognitive_pas' => 88.13,
            'cognitive_average' => 87.13,
            'skill_1' => 80.25,
            'skill_2' => 88.50,
            'skill_pas' => 84.38,
            'skill_average' => 84.13,
            'final_mark' => 86.25,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}