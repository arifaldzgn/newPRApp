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
        Schema::create('room_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->bigInteger('approved_user_id')->nullable()->default(1);
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('room');
            $table->date('date');
            $table->time('time_from');
            $table->time('time_to');
            $table->string('requested_by');
            $table->string('is_started')->default('No');
            $table->string('remark')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_events');
    }
};
