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
        Schema::table('master_mapel', function (Blueprint $table) {
            $table->renameColumn('id', 'id');  // Mengganti nama kolom 'id' menjadi 'id'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('id_mapel_on_master_mapel', function (Blueprint $table) {
            $table->renameColumn('id', 'id');
        });
    }
};
