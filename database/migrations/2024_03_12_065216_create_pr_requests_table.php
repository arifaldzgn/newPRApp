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
        Schema::create('pr_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id');
            $table->foreignId('partlist_id');
            $table->bigInteger('qty');
            $table->bigInteger('amount')->nullable();
            $table->bigInteger('other_cost')->nullable();
            $table->string('vendor');
            $table->string('remark')->nullable();
            $table->string('category');
            $table->string('tag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_requests');
    }
};
