<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('beasiswa_periods')
            ->where('tahun', 2026)
            ->update([
                'akhir_pendaftaran' => '2026-10-31',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('beasiswa_periods')
            ->where('tahun', 2026)
            ->update([
                'akhir_pendaftaran' => '2026-09-30',
                'updated_at' => now(),
            ]);
    }
};
