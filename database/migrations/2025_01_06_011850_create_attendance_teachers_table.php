<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('no_kd')->nullable();  // Kolom untuk no_kd
            $table->decimal('cognitive_1', 8, 2)->nullable();  // Kolom untuk cognitive_1
            $table->decimal('cognitive_2', 8, 2)->nullable();  // Kolom untuk cognitive_2
            $table->decimal('cognitive_pas', 8, 2)->nullable();  // Kolom untuk cognitive_pas
            $table->decimal('cognitive_average', 8, 2)->nullable();  // Kolom untuk cognitive_average
            $table->decimal('skill_1', 8, 2)->nullable();  // Kolom untuk skill_1
            $table->decimal('skill_2', 8, 2)->nullable();  // Kolom untuk skill_2
            $table->decimal('skill_pas', 8, 2)->nullable();  // Kolom untuk skill_pas
            $table->decimal('skill_average', 8, 2)->nullable();  // Kolom untuk skill_average
            $table->decimal('final_mark', 8, 2)->nullable();  // Kolom untuk final_mark
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'no_kd',
                'cognitive_1',
                'cognitive_2',
                'cognitive_pas',
                'cognitive_average',
                'skill_1',
                'skill_2',
                'skill_pas',
                'skill_average',
                'final_mark',
            ]);
        });
    }
};
