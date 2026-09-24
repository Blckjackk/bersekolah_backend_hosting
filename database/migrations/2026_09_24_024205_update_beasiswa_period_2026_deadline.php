<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FIX BUG: Periode berstatus 'active' memiliki is_active = 0 (false).
     * finalizeApplication() mensyaratkan KEDUA kondisi terpenuhi:
     *   ->where('status', 'active')
     *   ->where('is_active', true)  <-- ini yang menyebabkan gagal
     *   ->where('akhir_pendaftaran', '>=', now())
     *
     * Fix: Sync is_active dengan field status untuk semua periode.
     */
    public function up(): void
    {
        // FIX: Set is_active = 1 untuk semua periode yang berstatus 'active'
        DB::table('beasiswa_periods')
            ->where('status', 'active')
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);

        // Pastikan periode 'closed'/'draft' tidak is_active
        DB::table('beasiswa_periods')
            ->whereIn('status', ['closed', 'draft'])
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan is_active = 0 untuk semua (kondisi sebelumnya yang buggy)
        DB::table('beasiswa_periods')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }
};
