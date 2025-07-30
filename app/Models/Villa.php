<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Villa extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'ownership_status',
        'price_idr',
        'price_usd',
        'description',
    ];

    /**
     * Mendefinisikan koleksi media untuk Villa.
     */
    public function registerMediaCollections(): void
    {
        // Gambar utama (hanya satu file)
        $this->addMediaCollection('image')
            ->singleFile();

        // Galeri (bisa banyak file)
        $this->addMediaCollection('gallery');

        // Video (hanya satu file)
        $this->addMediaCollection('video')
            ->singleFile();
    }
}
