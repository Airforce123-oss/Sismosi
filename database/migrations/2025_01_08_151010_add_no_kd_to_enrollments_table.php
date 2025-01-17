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
        Schema::table('enrollments', function (Blueprint $table) {
            // Mengubah kolom no_kd jika diperlukan
            $table->string('no_kd')->nullable()->change(); // Pastikan tipe data dan nullable sesuai
        });
    }
    
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Rollback perubahan
            $table->dropColumn('no_kd');
        });
    }
    
    
};
