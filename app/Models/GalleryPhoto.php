<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class GalleryPhoto extends Model
{
    protected $fillable = [
        'gallery_id',
        'file_path',
        'file_name',
        'caption',
        'urutan',
    ];

    protected $appends = ['photo_url'];

    /**
     * Relasi ke album galeri
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Accessor untuk URL foto
     */
    public function getPhotoUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return Storage::url($this->file_path);
    }
}
