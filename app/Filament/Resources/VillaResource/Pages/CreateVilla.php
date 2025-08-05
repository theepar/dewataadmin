<?php
namespace App\Filament\Resources\VillaResource\Pages;

use App\Filament\Resources\VillaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVilla extends CreateRecord
{
    protected static string $resource = VillaResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $images = $data['images'] ?? [];
        $cover  = $data['cover_image'] ?? null;
        $video  = $data['video'][0] ?? null;
        unset($data['images'], $data['cover_image'], $data['video']);

        $villa = static::getModel()::create($data);

        foreach ($images as $img) {
            if ($img && str_contains($img, '/')) {
                \App\Models\VillaMedia::create([
                    'villa_id'  => $villa->id,
                    'file_path' => $img,
                    'file_name' => basename($img),
                    'type'      => 'image',
                    'is_cover'  => $img === $cover,
                ]);
            }
        }

        if ($video && str_contains($video, '/')) {
            \App\Models\VillaMedia::create([
                'villa_id'  => $villa->id,
                'file_path' => $video,
                'file_name' => basename($video),
                'type'      => 'video',
                'is_cover'  => false,
            ]);
        }

        return $villa;
    }
}
