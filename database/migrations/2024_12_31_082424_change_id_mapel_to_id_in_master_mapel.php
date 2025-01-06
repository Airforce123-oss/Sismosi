<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIdMapelToIdInMasterMapel extends Migration
{
    /**
     * Jalankan migrasi untuk mengubah kolom.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('master_mapel', function (Blueprint $table) {
            // Mengganti nama kolom 'id' menjadi 'id'
            $table->renameColumn('id', 'id');
        });
    }

    /**
     * Rollback migrasi jika terjadi kesalahan.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('master_mapel', function (Blueprint $table) {
            // Mengembalikan perubahan jika rollback
            $table->renameColumn('id', 'id');
        });
    }
}
