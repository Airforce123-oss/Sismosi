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
                $table->integer('mark')->nullable()->default(0)->after('status');
            }
        });
    }
    
    public function down()
    {
        Schema::table('enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('enrollments', 'mark')) {
                $table->dropColumn('mark');
            }
        });
    }
    
};
