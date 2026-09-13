<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel jadwal wawancara peserta beasiswa
     */
    public function up(): void
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beswan_id')->constrained('beswan')->onDelete('cascade');
            $table->foreignId('application_id')->nullable()->constrained('beasiswa_applications')->onDelete('set null');
            $table->date('tanggal_wawancara');         // Tanggal wawancara
            $table->time('jam_mulai');                 // Jam mulai wawancara
            $table->time('jam_selesai')->nullable();   // Jam selesai (optional)
            $table->string('lokasi_atau_link')->nullable(); // Lokasi offline atau link online
            $table->text('catatan')->nullable();       // Catatan untuk peserta
            $table->enum('status', ['scheduled', 'done', 'cancelled', 'rescheduled'])->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index(['beswan_id', 'tanggal_wawancara']);
            $table->index(['tanggal_wawancara', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interview_schedules');
    }
};
