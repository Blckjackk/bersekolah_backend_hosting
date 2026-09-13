<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tambah document type 'Bukti Share Poster' sebagai persyaratan wajib
     */
    public function up(): void
    {
        // Only insert if not already exists
        $exists = DB::table('document_types')->where('code', 'poster_share')->exists();
        if (!$exists) {
            DB::table('document_types')->insert([
                'code'           => 'poster_share',
                'name'           => 'Bukti Share Poster',
                'description'    => 'Upload bukti share poster pendaftaran beasiswa ke grup (screenshot atau foto bukti share)',
                'category'       => 'wajib',
                'is_required'    => true,
                'allowed_formats'=> json_encode(['jpg', 'jpeg', 'png', 'pdf']),
                'max_file_size'  => 5242880, // 5MB in bytes
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('document_types')->where('code', 'poster_share')->delete();
    }
};
