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
        Schema::create('komentar_siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('komentar');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            // $table->foreign('parent_id')->references('id')->on('parents')->onDelete('set null');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komentar_siswas');
    }
};
