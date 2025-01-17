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
            // Tambahkan kolom jika belum ada
            if (!Schema::hasColumn('enrollments', 'cognitive_1')) {
                $table->decimal('cognitive_1', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'cognitive_2')) {
                $table->decimal('cognitive_2', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'cognitive_pas')) {
                $table->decimal('cognitive_pas', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'cognitive_average')) {
                $table->decimal('cognitive_average', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'skill_1')) {
                $table->decimal('skill_1', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'skill_2')) {
                $table->decimal('skill_2', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'skill_pas')) {
                $table->decimal('skill_pas', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'skill_average')) {
                $table->decimal('skill_average', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('enrollments', 'final_mark')) {
                $table->decimal('final_mark', 5, 2)->nullable();
            }
        });
    }
    
    
    
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'cognitive_1', 'cognitive_2', 'cognitive_pas', 'cognitive_average',
                'skill_1', 'skill_2', 'skill_pas', 'skill_average', 'final_mark'
            ]);
        });
    }
    
};
