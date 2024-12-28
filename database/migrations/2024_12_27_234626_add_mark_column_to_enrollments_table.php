<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('enrollments', 'mark')) {
                $table->integer('mark')->nullable()->default(0); // Menambahkan kolom mark dengan default 0
            }
        });
    }
    
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('mark'); // Menghapus kolom mark jika rollback
        });
    }
    
};
