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
        Schema::table('pr_tickets', function (Blueprint $table) {
            $table->unsignedBigInteger('purchasing_approved_user_id')->nullable()->after('approved_user_id');
            $table->foreign('purchasing_approved_user_id')->references('id')->on('users')->onDelete('set null');
            $table->date('date_purchasing_approval')->nullable()->after('date_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pr_tickets', function (Blueprint $table) {
            $table->dropForeign(['purchasing_approved_user_id']);
            $table->dropColumn('purchasing_approved_user_id');
            $table->dropColumn('date_purchasing_approval');
        });
    }
};