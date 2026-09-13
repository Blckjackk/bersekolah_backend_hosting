<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    // =========================================================
    // PUBLIC ENDPOINTS
    // =========================================================

    /**
     * GET /gallery – List semua album yang published (public)
     */
    public function index(Request $request)
    {
        try {
            $query = Gallery::published()
                ->withCount('photos')
                ->orderByDesc('tanggal_kegiatan')
                ->orderByDesc('created_at');

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kegiatan', 'LIKE', "%{$search}%")
                      ->orWhere('deskripsi', 'LIKE', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 12);
            $galleries = $query->paginate($perPage);

            // Add cover_image_url to each gallery
            $galleries->getCollection()->transform(function ($gallery) {
                $gallery->cover_image = $gallery->cover_image_url;
                return $gallery;
            });

            return response()->json([
                'success' => true,
                'message' => 'Galeri berhasil diambil',
                'data'    => $galleries->items(),
                'meta'    => [
                    'current_page' => $galleries->currentPage(),
                    'last_page'    => $galleries->lastPage(),
                    'per_page'     => $galleries->perPage(),
                    'total'        => $galleries->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery index error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data galeri'], 500);
        }
    }

    /**
     * GET /gallery/{id} – Detail album beserta semua fotonya (public)
     */
    public function show($id)
    {
        try {
            $gallery = Gallery::published()->with(['photos'])->findOrFail($id);

            // Transform photo URLs
            $gallery->cover_image = $gallery->cover_image_url;
            $gallery->photos->transform(function ($photo) {
                $photo->photo_url = $photo->photo_url;
                return $photo;
            });

            return response()->json([
                'success' => true,
                'data'    => $gallery,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Galeri tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error('Gallery show error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data galeri'], 500);
        }
    }

    // =========================================================
    // ADMIN ENDPOINTS
    // =========================================================

    /**
     * GET /admin/gallery – List semua album (admin, termasuk draft)
     */
    public function adminIndex(Request $request)
    {
        try {
            $query = Gallery::withCount('photos')->orderByDesc('created_at');

            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nama_kegiatan', 'LIKE', "%{$search}%")
                      ->orWhere('deskripsi', 'LIKE', "%{$search}%");
                });
            }

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $perPage = $request->get('per_page', 15);
            $galleries = $query->paginate($perPage);

            $galleries->getCollection()->transform(function ($gallery) {
                $gallery->cover_image = $gallery->cover_image_url;
                return $gallery;
            });

            return response()->json([
                'success' => true,
                'data'    => $galleries->items(),
                'meta'    => [
                    'current_page' => $galleries->currentPage(),
                    'last_page'    => $galleries->lastPage(),
                    'per_page'     => $galleries->perPage(),
                    'total'        => $galleries->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery adminIndex error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data galeri'], 500);
        }
    }

    /**
     * POST /admin/gallery – Buat album baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan'    => 'required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'status'           => 'nullable|in:draft,published',
            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['created_by'] = Auth::id();

            // Upload cover image if provided
            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('gallery/covers', 'public');
                $data['cover_image'] = $path;
            }

            $gallery = Gallery::create($data);
            $gallery->cover_image = $gallery->cover_image_url;

            return response()->json([
                'success' => true,
                'message' => 'Album galeri berhasil dibuat',
                'data'    => $gallery,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gallery store error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal membuat album galeri'], 500);
        }
    }

    /**
     * PUT /admin/gallery/{id} – Update album
     */
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nama_kegiatan'    => 'sometimes|required|string|max:255',
            'deskripsi'        => 'nullable|string',
            'tanggal_kegiatan' => 'nullable|date',
            'status'           => 'nullable|in:draft,published',
            'cover_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('cover_image')) {
                // Hapus cover lama jika ada
                if ($gallery->cover_image && !str_starts_with($gallery->cover_image, 'http')) {
                    Storage::disk('public')->delete($gallery->cover_image);
                }
                $path = $request->file('cover_image')->store('gallery/covers', 'public');
                $data['cover_image'] = $path;
            }

            $gallery->update($data);
            $gallery->cover_image = $gallery->cover_image_url;

            return response()->json([
                'success' => true,
                'message' => 'Album galeri berhasil diperbarui',
                'data'    => $gallery,
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery update error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui album galeri'], 500);
        }
    }

    /**
     * DELETE /admin/gallery/{id} – Hapus album beserta semua fotonya
     */
    public function destroy($id)
    {
        try {
            $gallery = Gallery::with('photos')->findOrFail($id);

            // Hapus file cover
            if ($gallery->cover_image && !str_starts_with($gallery->cover_image, 'http')) {
                Storage::disk('public')->delete($gallery->cover_image);
            }

            // Hapus semua file foto
            foreach ($gallery->photos as $photo) {
                if ($photo->file_path && !str_starts_with($photo->file_path, 'http')) {
                    Storage::disk('public')->delete($photo->file_path);
                }
            }

            $gallery->delete();

            return response()->json([
                'success' => true,
                'message' => 'Album galeri berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery destroy error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus album galeri'], 500);
        }
    }

    /**
     * POST /admin/gallery/{id}/photos – Upload foto ke album
     */
    public function uploadPhotos(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'photos'          => 'required|array|min:1|max:20',
            'photos.*'        => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'captions'        => 'nullable|array',
            'captions.*'      => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $uploaded = [];
            $lastUrutan = $gallery->photos()->max('urutan') ?? 0;

            foreach ($request->file('photos') as $index => $photoFile) {
                $path = $photoFile->store("gallery/{$gallery->id}/photos", 'public');
                $caption = $request->captions[$index] ?? null;

                $photo = GalleryPhoto::create([
                    'gallery_id' => $gallery->id,
                    'file_path'  => $path,
                    'file_name'  => $photoFile->getClientOriginalName(),
                    'caption'    => $caption,
                    'urutan'     => $lastUrutan + $index + 1,
                ]);

                $photo->photo_url = $photo->photo_url;
                $uploaded[] = $photo;
            }

            return response()->json([
                'success' => true,
                'message' => count($uploaded) . ' foto berhasil diupload',
                'data'    => $uploaded,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Gallery uploadPhotos error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengupload foto'], 500);
        }
    }

    /**
     * DELETE /admin/gallery/photos/{photoId} – Hapus satu foto
     */
    public function deletePhoto($photoId)
    {
        try {
            $photo = GalleryPhoto::findOrFail($photoId);

            if ($photo->file_path && !str_starts_with($photo->file_path, 'http')) {
                Storage::disk('public')->delete($photo->file_path);
            }

            $photo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Foto berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery deletePhoto error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus foto'], 500);
        }
    }

    /**
     * GET /admin/gallery/{id}/photos – Semua foto dalam album (admin)
     */
    public function getPhotos($id)
    {
        try {
            $gallery = Gallery::findOrFail($id);
            $photos = $gallery->photos()->get();

            $photos->transform(function ($photo) {
                $photo->photo_url = $photo->photo_url;
                return $photo;
            });

            return response()->json([
                'success' => true,
                'data'    => $photos,
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery getPhotos error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengambil foto'], 500);
        }
    }
}
