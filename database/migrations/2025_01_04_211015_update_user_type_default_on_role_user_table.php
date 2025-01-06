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
        Schema::table('role_user', function (Blueprint $table) {
            $table->string('user_type')->default('default_value')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('role_user', function (Blueprint $table) {
            $table->string('user_type')->nullable()->change();
        });
    }
};
