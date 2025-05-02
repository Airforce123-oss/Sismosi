<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('teacher_mapel', function (Blueprint $table) {
            $table->unsignedBigInteger('wali_kelas_id')->nullable();
    
            // Jika ingin menambahkan relasi:
            // $table->foreign('wali_kelas_id')->references('id')->on('wali_kelas')->onDelete('cascade');
        });
    }
    
    public function down()
    {
        Schema::table('teacher_mapel', function (Blueprint $table) {
            $table->dropColumn('wali_kelas_id');
        });
    }
    
};
