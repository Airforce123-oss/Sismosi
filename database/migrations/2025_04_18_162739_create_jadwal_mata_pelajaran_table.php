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
        Schema::create('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('hari');
            $table->integer('jam_ke');
            $table->string('jam')->nullable();
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->timestamps();

            // FK ke master_mapel
            $table->foreign('mapel_id')
                  ->references('id')->on('master_mapel')
                  ->onDelete('cascade');

            // FK ke classes (bukan kelas)
            $table->foreign('kelas_id')
                  ->references('id')->on('classes')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_mata_pelajaran');
    }
};
