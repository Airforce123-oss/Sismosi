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
        Schema::table('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->string('jurusan')->nullable();  // Menambahkan kolom jurusan
            $table->string('tingkat')->nullable();  // Menambahkan kolom tingkat
        });
    }
    
    public function down()
    {
        Schema::table('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->dropColumn(['jurusan', 'tingkat']);  // Menghapus kolom jurusan dan tingkat jika rollback
        });
    }
    
};
