<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $table = 'testimoni';

    public $timestamps = false; // karena tidak pakai created_at & updated_at default

    protected $fillable = [
        'nama',
        'angkatan_beswan',
        'sekarang_dimana',
        'isi_testimoni',
        'foto_testimoni',
        'status',
        'tanggal_input',
    ];

    protected $casts = [
        'tanggal_input' => 'datetime',
    ];

    // Accessor untuk foto testimoni agar bisa menampilkan URL lengkap
    public function getFotoTestimoniUrlAttribute()
    {
        // Use the correct API URL for production
        $baseUrl = env('APP_ENV') === 'production' || str_contains(env('APP_URL', ''), 'hostingersite')
            ? 'https://api.bersekolah.com'
            : 'http://localhost:8000';

        // If no image path provided, return default
        if (!$this->foto_testimoni || $this->foto_testimoni === 'null' || $this->foto_testimoni === '') {
            return $baseUrl . '/storage/defaults/testimoni-default.jpg';
        }

        // If it's already 'default.jpg', return the correct path
        if ($this->foto_testimoni === 'default.jpg') {
            return $baseUrl . '/storage/defaults/testimoni-default.jpg';
        }

        // If the path is just filename, construct full Laravel storage URL
        if (!str_starts_with($this->foto_testimoni, 'http') && !str_starts_with($this->foto_testimoni, '/storage')) {
            return $baseUrl . '/storage/testimoni/' . $this->foto_testimoni;
        }

        // If the path already starts with /storage, convert to full URL
        if (str_starts_with($this->foto_testimoni, '/storage')) {
            return $baseUrl . $this->foto_testimoni;
        }

        // If the path already includes the domain, return as is
        if (str_starts_with($this->foto_testimoni, 'http')) {
            return $this->foto_testimoni;
        }

        // Extract filename from any path structure
        $filename = $this->foto_testimoni;
        if (str_contains($filename, '/')) {
            $parts = explode('/', $filename);
            $filename = end($parts) ?: 'default.jpg';
        }

        // Return full Laravel storage URL for testimoni
        return $baseUrl . '/storage/testimoni/' . $filename;
    }
}
