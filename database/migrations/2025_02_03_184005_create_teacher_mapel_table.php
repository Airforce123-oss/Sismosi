<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teacher_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wali_kelas_id') // Use wali_kelas_id as the foreign key
                  ->constrained('wali_kelas') // References the wali_kelas table
                  ->onDelete('cascade');
            $table->foreignId('mapel_id')
                  ->constrained('master_mapel') // References the master_mapel table
                  ->onDelete('cascade');
            $table->string('kode_mapel');
            $table->string('mapel');
            $table->timestamps();
        });
    }
    
    
    public function down()
    {
        Schema::dropIfExists('teacher_mapel');
    }
    
    
};
