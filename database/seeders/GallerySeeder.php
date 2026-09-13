<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use Carbon\Carbon;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $album1 = Gallery::create([
            'nama_kegiatan' => 'Bersekolah Mentoring Session 2026',
            'deskripsi' => 'Sesi mentoring tatap muka antara kakak mentor dan adik asuh (beswan) angkatan 2026.',
            'tanggal_kegiatan' => Carbon::now()->subDays(15),
            'cover_image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=800&auto=format&fit=crop',
            'status' => 'published',
            'created_by' => 1, 
        ]);

        $photosAlbum1 = [
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=800&auto=format&fit=crop'
        ];

        foreach ($photosAlbum1 as $index => $photo) {
            GalleryPhoto::create([
                'gallery_id' => $album1->id,
                'file_path' => $photo,
                'file_name' => 'mentoring-photo-' . ($index + 1) . '.jpg',
                'caption' => 'Keseruan sesi mentoring kelompok ' . ($index + 1),
                'urutan' => $index + 1,
            ]);
        }

        $album2 = Gallery::create([
            'nama_kegiatan' => 'Kunjungan Edukatif ke SMA 1',
            'deskripsi' => 'Tim Bersekolah melakukan kunjungan edukatif dan sosialisasi program beasiswa.',
            'tanggal_kegiatan' => Carbon::now()->subMonths(2),
            'cover_image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=800&auto=format&fit=crop',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $photosAlbum2 = [
            'https://images.unsplash.com/photo-1580582932707-520aed937b7b?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1588072432836-e10032774350?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?q=80&w=800&auto=format&fit=crop'
        ];

        foreach ($photosAlbum2 as $index => $photo) {
            GalleryPhoto::create([
                'gallery_id' => $album2->id,
                'file_path' => $photo,
                'file_name' => 'school-visit-' . ($index + 1) . '.jpg',
                'caption' => 'Dokumentasi kunjungan edukatif',
                'urutan' => $index + 1,
            ]);
        }

        $album3 = Gallery::create([
            'nama_kegiatan' => 'Malam Penganugerahan Beasiswa',
            'deskripsi' => 'Puncak acara penyerahan sertifikat beasiswa kepada para penerima manfaat.',
            'tanggal_kegiatan' => Carbon::now()->subMonths(5),
            'cover_image' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=800&auto=format&fit=crop',
            'status' => 'published',
            'created_by' => 1,
        ]);

        $photosAlbum3 = [
            'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1540575467063-178a50c2df87?q=80&w=800&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1523580494112-071d16940d14?q=80&w=800&auto=format&fit=crop'
        ];

        foreach ($photosAlbum3 as $index => $photo) {
            GalleryPhoto::create([
                'gallery_id' => $album3->id,
                'file_path' => $photo,
                'file_name' => 'awarding-night-' . ($index + 1) . '.jpg',
                'caption' => 'Momen penganugerahan beasiswa',
                'urutan' => $index + 1,
            ]);
        }
        echo "✅ Data dummy galeri dan foto berhasil ditambahkan!\n";
    }
}

