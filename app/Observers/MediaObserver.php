<?php

namespace App\Observers;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManagerStatic;

class MediaObserver
{
    public function created(Media $media)
    {
        // Hanya proses jika koleksi gambar (gallery, images, dst)
        $imageCollections = ['gallery', 'images'];
        if (!in_array($media->collection_name, $imageCollections)) {
            return;
        }
        $path = $media->getPath();
        if (!file_exists($path)) {
            return;
        }
        $sizeMb = filesize($path) / 1024 / 1024;
        // Jika file <= 10MB, tidak perlu kompres, langsung return
        if ($sizeMb <= 10) {
            return;
        }
        // Hanya kompres jika format didukung Intervention Image
        $mime = mime_content_type($path);
        $supported = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif'
        ];
        if (!in_array($mime, $supported)) {
            // HEIC/HEIF dan format lain di-skip agar tidak error
            return;
        }
        try {
            $img = Image::make($path);
            // Kompresi dengan kualitas tinggi (80-90)
            $img->save($path, 85);
            $media->refresh();
        } catch (\Exception $e) {
            Log::error('Gagal kompres gambar media: ' . $e->getMessage());
        }
    }
}