<?php
namespace App\Filament\Resources\VillaResource\Pages;

use App\Filament\Resources\VillaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditVilla extends EditRecord
{
    protected static string $resource = VillaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $oldImages     = $record->media()->where('type', 'image')->pluck('file_path')->toArray();
        $newImages     = $data['images'] ?? [];
        $deletedImages = array_diff($oldImages, $newImages);

        foreach ($deletedImages as $img) {
            Storage::disk('public')->delete($img);
            \App\Models\VillaMedia::where('villa_id', $record->id)->where('file_path', $img)->delete();
        }

        $images = $data['images'] ?? [];
        $video  = $data['video'][0] ?? null;
        unset($data['images'], $data['video']);

        $record->update($data);

        // Hapus media lama (opsional, jika ingin replace)
        \App\Models\VillaMedia::where('villa_id', $record->id)->delete();

        // Simpan gambar baru ke database
        foreach ($images as $img) {
            if ($img && str_contains($img, '/')) { // pastikan path valid
                \App\Models\VillaMedia::create([
                    'villa_id'  => $record->id,
                    'file_path' => $img,
                    'file_name' => basename($img),
                    'type'      => 'image',
                    'is_cover'  => false,
                ]);
            }
        }

        if ($video && str_contains($video, '/')) { // pastikan path valid
            \App\Models\VillaMedia::create([
                'villa_id'  => $record->id,
                'file_path' => $video,
                'file_name' => basename($video),
                'type'      => 'video',
                'is_cover'  => false,
            ]);
        }

        return $record;
    }
}
