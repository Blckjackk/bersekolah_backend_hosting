<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel galleries (album kegiatan) dan gallery_photos (foto dalam album)
     */
    public function up(): void
    {
        // Tabel album kegiatan
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');          // Nama kegiatan, misal "Bermain 2026"
            $table->text('deskripsi')->nullable();     // Deskripsi kegiatan
            $table->date('tanggal_kegiatan')->nullable(); // Tanggal kegiatan berlangsung
            $table->string('cover_image')->nullable(); // Path gambar cover album
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'tanggal_kegiatan']);
        });

        // Tabel foto dalam album
        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->onDelete('cascade');
            $table->string('file_path');               // Path file foto
            $table->string('file_name');               // Nama file asli
            $table->string('caption')->nullable();     // Keterangan foto
            $table->integer('urutan')->default(0);     // Urutan tampil dalam album
            $table->timestamps();

            $table->index(['gallery_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('galleries');
    }
};
