<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Gallery extends Model
{
    protected $fillable = [
        'nama_kegiatan',
        'deskripsi',
        'tanggal_kegiatan',
        'cover_image',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
    ];

    protected $appends = ['cover_image_url', 'photos_count'];

    /**
     * Relasi ke foto-foto dalam album
     */
    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('urutan')->orderBy('created_at');
    }

    /**
     * Relasi ke user yang membuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessor untuk URL cover image
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (!$this->cover_image) {
            return null;
        }
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }
        return Storage::url($this->cover_image);
    }

    /**
     * Accessor untuk jumlah foto
     */
    public function getPhotosCountAttribute(): int
    {
        return $this->photos()->count();
    }

    /**
     * Scope untuk hanya tampilkan yang published
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
