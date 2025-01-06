<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->id();
            $table->morphs('model'); // Menambahkan kolom model_id dan model_type
            $table->foreignId('permission_id')->constrained('permissions'); // Menghubungkan ke tabel permissions
            $table->timestamps();
            
            // Index untuk mempercepat query
            $table->unique(['model_id', 'model_type', 'permission_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_has_permissions');
    }
}
