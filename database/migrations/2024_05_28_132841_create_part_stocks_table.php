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
        Schema::create('part_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('part_list_id');
            $table->string('operations');
            $table->integer('quantity');
            $table->string('source')->nullable();
            $table->timestamps();

            // Define foreign keys
            $table->foreign('part_list_id')->references('id')->on('part_lists')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_stocks');
    }
};
