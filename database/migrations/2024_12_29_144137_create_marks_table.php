<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarksTable extends Migration
{
    public function up()
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();  // Kolom 'id' untuk marks
            $table->unsignedBigInteger('student_id'); // Kolom 'student_id'
            $table->unsignedBigInteger('mapel_id');  // Kolom 'mapel_id' sebagai foreign key
            $table->integer('mark');
            $table->timestamps();
    
            // Menambahkan foreign key yang mengarah ke kolom 'id' di tabel 'students'
            $table->foreign('student_id')
                  ->references('id')  // Mengarah ke kolom 'id' di tabel 'students'
                  ->on('students')  // Tabel 'students'
                  ->onDelete('cascade');  // On delete cascade

            // Menambahkan foreign key yang mengarah ke kolom 'id_mapel' di tabel 'master_mapel'
            $table->foreign('mapel_id')
                  ->references('id_mapel')  // Mengarah ke kolom 'id_mapel' di tabel 'master_mapel'
                  ->on('master_mapel')  // Tabel 'master_mapel'
                  ->onDelete('cascade');  // On delete cascade
        });
    }

    public function down()
    {
        Schema::dropIfExists('marks');
    }
}
