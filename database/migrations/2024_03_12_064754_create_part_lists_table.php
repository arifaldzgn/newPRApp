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
        Schema::create('part_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_code_id');
            $table->string('part_name');
            $table->string('category');
            $table->string('UoM');
            $table->string('type')->nullable();
            $table->string('requires_stock_reduction');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_lists');
    }
};
