<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('master_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan'); // Kolom untuk nama jabatan
            $table->text('deskripsi')->nullable(); // Kolom deskripsi, bisa kosong
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('master_jabatan');
    }
    
};
