<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mapel_murid', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('mapel_id');
            $table->timestamps();

            // Menambahkan foreign key
            $table->foreign('siswa_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('master_mapel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mapel_murid', function (Blueprint $table) {
            // Drop foreign keys terlebih dahulu sebelum menghapus tabel
            $table->dropForeign(['siswa_id']);
            $table->dropForeign(['mapel_id']);
        });

        Schema::dropIfExists('mapel_murid');
    }
};
