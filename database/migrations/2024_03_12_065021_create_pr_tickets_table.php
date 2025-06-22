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
        Schema::create('pr_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->bigInteger('approved_user_id')->nullable();
            $table->date('date_approval')->nullable();
            $table->date('date_checked')->nullable();
            $table->string('ticketCode')->unique();
            $table->string('status');
            $table->string('reason_reject')->nullable();
            $table->bigInteger('advance_cash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pr_tickets');
    }
};
