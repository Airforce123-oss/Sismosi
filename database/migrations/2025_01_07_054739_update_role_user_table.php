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
            // Drop the existing primary key (if it includes `user_type`)
            $table->dropPrimary();
    
            // Modify the `user_type` column to allow NULL
            $table->string('user_type')->nullable()->change();
    
            // Optionally, add the primary key back (excluding `user_type` if necessary)
            // $table->primary(['role_id', 'user_id']);
        });
    }
    
    public function down()
    {
        Schema::table('role_user', function (Blueprint $table) {
            // Restore the `user_type` column to NOT NULL
            $table->string('user_type')->nullable(false)->change();
    
            // Optionally, restore the primary key
            // $table->primary(['role_id', 'user_id', 'user_type']);
        });
    }
    
};
