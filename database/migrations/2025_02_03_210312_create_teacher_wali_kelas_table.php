<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeacherWaliKelasTable extends Migration
{
    public function up()
    {
        Schema::create('teacher_wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wali_kelas_id'); // Wali Kelas
            $table->unsignedBigInteger('teacher_id'); // ID Teacher, tetap merujuk ke wali_kelas
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('wali_kelas_id')->references('id')->on('wali_kelas')->onDelete('cascade');
            $table->foreign('teacher_id')->references('id')->on('wali_kelas')->onDelete('cascade'); // Teacher ada di wali_kelas
        });
    }

    /**
     * Membalikkan migrasi.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_wali_kelas');
    }
}
