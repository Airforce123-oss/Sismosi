<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNipToWaliKelasTable extends Migration
{
    /**
     * Jalankan migration untuk menambah kolom nip.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wali_kelas', function (Blueprint $table) {
            $table->string('nip')->nullable(); // Menambahkan kolom nip dengan tipe string
        });
    }

    /**
     * Membalikkan migration untuk menghapus kolom nip.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wali_kelas', function (Blueprint $table) {
            $table->dropColumn('nip'); // Menghapus kolom nip jika migration di rollback
        });
    }
}
