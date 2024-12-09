<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NoIndukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $noInduks = [];

        for ($i = 0; $i < 10; $i++) { // Sesuaikan jumlahnya jika perlu
            $randomNumber = '12' . str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $noInduks[] = ['no_induk' => $randomNumber];
        }

        DB::table('no_induks')->insert($noInduks);
    }
}
