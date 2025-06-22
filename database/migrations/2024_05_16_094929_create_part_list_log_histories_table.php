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
        Schema::create('part_list_log_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_list_id');
            $table->foreign('part_list_id')->references('id')->on('part_lists')->onDelete('cascade');
            $table->string('asset_code_id');
            $table->string('part_name');
            $table->string('category');
            $table->string('UoM');
            $table->string('type');
            $table->string('action'); // 'created', 'updated', 'deleted'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_list_log_histories');
    }
};
