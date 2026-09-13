<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
{
    protected $fillable = [
        'beswan_id',
        'application_id',
        'tanggal_wawancara',
        'jam_mulai',
        'jam_selesai',
        'lokasi_atau_link',
        'catatan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_wawancara' => 'date',
    ];

    /**
     * Relasi ke beswan (peserta)
     */
    public function beswan(): BelongsTo
    {
        return $this->belongsTo(Beswan::class);
    }

    /**
     * Relasi ke aplikasi beasiswa
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(BeasiswaApplication::class, 'application_id');
    }

    /**
     * Relasi ke user yang membuat jadwal
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk jadwal yang akan datang
     */
    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_wawancara', '>=', now()->toDateString())
                     ->where('status', 'scheduled');
    }
}
