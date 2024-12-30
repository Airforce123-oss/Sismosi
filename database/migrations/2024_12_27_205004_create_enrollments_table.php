<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id(); // Kolom ID utama
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // Relasi ke tabel students
            $table->foreignId('mapel_id')->constrained('master_mapel', 'id_mapel')->onDelete('cascade'); // Relasi ke tabel master_mapel
            $table->date('enrollment_date'); // Tanggal pendaftaran
            $table->enum('status', ['active', 'inactive']); // Status enrollment

            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->integer('mark')->nullable(); // Menambahkan kolom mark kembali jika rollback
        });
    }
}
