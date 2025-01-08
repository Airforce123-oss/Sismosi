<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTugasTable extends Migration
{
    public function up()
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('mapel_id')->unsigned();
            $table->foreign('mapel_id')->references('id_mapel')->on('master_mapel')->onDelete('cascade');
            // kolom lainnya...
        });
    }

    public function down()
    {
        Schema::dropIfExists('tugas');
    }
}
