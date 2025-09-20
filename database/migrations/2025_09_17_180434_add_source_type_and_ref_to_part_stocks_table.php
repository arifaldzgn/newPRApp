<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('part_stocks', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('operations'); // ex: system, pr_request, manual
            $table->string('source_ref')->nullable()->after('source_type'); // ex: PR No, ticketCode, etc
            $table->integer('before_quantity')->default(0);
            $table->integer('after_quantity')->default(0);
        });
    }

    public function down()
    {
        Schema::table('part_stocks', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_ref']);
        });
    }
};
