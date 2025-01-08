<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('no_kd')->nullable();
            $table->integer('cognitive_1')->nullable();
            $table->integer('cognitive_2')->nullable();
            $table->integer('cognitive_pas')->nullable();
            $table->decimal('cognitive_average', 8, 2)->nullable();
            $table->integer('skill_1')->nullable();
            $table->integer('skill_2')->nullable();
            $table->integer('skill_pas')->nullable();
            $table->decimal('skill_average', 8, 2)->nullable();
            $table->decimal('final_mark', 8, 2)->nullable();

            $table->decimal('mark', 8, 2)->nullable();  
    
            // Jika perlu memperbarui kolom yang sudah ada
            $table->string('status')->nullable()->change();  // Mengubah status menjadi nullable jika diperlukan
        });
    }
    
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
            
            // Jika kamu mengubah kolom status, kembalikan ke status semula jika rollback
            $table->string('status')->nullable(false)->change();  // Mengubah kolom 'status' kembali ke tidak nullable
        });
    }
    
}
