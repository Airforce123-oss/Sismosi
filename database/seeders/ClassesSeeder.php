<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\Student;
use App\Models\Gender;
use App\Models\Religion;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ClassesSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua nomor induk yang ada di tabel no_induks
        $noInduks = DB::table('no_induks')->pluck('id'); // Ambil semua ID dari no_induks

        if ($noInduks->isEmpty()) {
            $this->command->error('NoInduks data is missing.');
            return;
        }

        // Definisikan nama kelas dan jumlahnya
        $classNames = [
            'X' => 6,
            'XI' => 6,
            'XII' => 4,
        ];

        $genders = Gender::all();
        $religions = Religion::all();

        if ($genders->isEmpty() || $religions->isEmpty()) {
            $this->command->error('Genders or religions data is missing.');
            return;
        }

        foreach ($classNames as $prefix => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $className = "{$prefix}-{$i}";
                $class = Classes::updateOrCreate(['name' => $className]);

                for ($j = 0; $j < 5; $j++) {
                    // Pilih ID no_induk yang valid
                    $noIndukId = $noInduks->random();

                    // Buat atau update student
                    Student::updateOrCreate(
                        [
                            'no_induk_id' => $noIndukId,
                            'class_id' => $class->id,
                            'gender_id' => $genders->random()->id,
                            'religion_id' => $religions->random()->id,
                            'name' => $this->faker->name,
                        ]
                    );
                }
            }
        }
    }
}
