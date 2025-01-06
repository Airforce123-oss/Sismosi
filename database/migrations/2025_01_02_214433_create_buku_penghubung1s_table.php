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
        Schema::create('buku_penghubung1s', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('parentName');
            $table->string('studentName');
            $table->string('gender');
            $table->string('class');
            $table->text('issue');
            $table->text('action');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku_penghubung1s');
    }
};
