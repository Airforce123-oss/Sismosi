<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMarkToEnrollmentsTable extends Migration
{
    public function up()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Menambahkan kolom mark jika belum ada
            if (!Schema::hasColumn('enrollments', 'mark')) {
                $table->decimal('mark', 8, 2)->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Menghapus kolom mark jika rollback
            $table->dropColumn('mark');
        });
    }
}
