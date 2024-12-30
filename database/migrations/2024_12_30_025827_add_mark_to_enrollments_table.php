<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarkToEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Menambahkan kolom mark
            $table->integer('mark')->nullable(); // Kolom mark dapat bernilai null
        });
    }

    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Menghapus kolom mark jika migrasi di-rollback
            $table->dropColumn('mark');
        });
    }
}
