<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWaliKelasIdToClassesTable extends Migration
{
    public function up()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->unsignedBigInteger('wali_kelas_id')->nullable()->after('name'); // Menambahkan kolom wali_kelas_id
        });
    }

    public function down()
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn('wali_kelas_id'); // Menghapus kolom jika migrasi dibatalkan
        });
    }
}
