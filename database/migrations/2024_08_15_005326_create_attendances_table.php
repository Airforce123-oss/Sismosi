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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mapel_murid_id');
            $table->date('tanggal_kehadiran');
            $table->string('status_kehadiran', 1);
            $table->timestamps();

            $table->foreign('mapel_murid_id')->references('id')->on('mapel_murid')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};


/*
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
        $table->foreignId('mapel_id')->constrained('master_mapel', 'id')->onDelete('cascade'); // Menambahkan mapel_id sebagai foreign key
            $table->date('enrollment_date'); // Tanggal pendaftaran
            $table->enum('status', ['active', 'inactive']); // Status enrollment
            $table->string('mark')->nullable(); // Kolom untuk mark (nilai)
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollments');
    }
}

*/