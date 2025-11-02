<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_lists', function (Blueprint $table) {
            // Tambah kolom baru TANPA menghapus atau ubah data lama
            if (!Schema::hasColumn('part_lists', 'current_stock')) {
                $table->integer('current_stock')
                      ->default(0)
                      ->after('requires_stock_reduction')
                      ->comment('Jumlah stok aktual dari part_stocks');
            }

            if (!Schema::hasColumn('part_lists', 'last_synced_at')) {
                $table->timestamp('last_synced_at')
                      ->nullable()
                      ->after('current_stock')
                      ->comment('Waktu terakhir sinkronisasi stok otomatis');
            }
        });
    }

    public function down(): void
    {
        Schema::table('part_lists', function (Blueprint $table) {
            if (Schema::hasColumn('part_lists', 'current_stock')) {
                $table->dropColumn('current_stock');
            }
            if (Schema::hasColumn('part_lists', 'last_synced_at')) {
                $table->dropColumn('last_synced_at');
            }
        });
    }
};
