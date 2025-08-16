<?php

namespace App\Filament\Resources\VillaResource\Pages;

use App\Filament\Resources\VillaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVilla extends CreateRecord
{
    protected static string $resource = VillaResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $cover  = $data['cover_image'][0] ?? null;
        $images = $data['images'] ?? [];
        unset($data['cover_image'], $data['images'], $data['video']);

        // Pastikan ownership_status tetap array (untuk kolom json)
        if (isset($data['ownership_status']) && is_array($data['ownership_status'])) {
            $data['ownership_status'] = array_values($data['ownership_status']);
        }

        // Buat villa baru
        $villa = static::getModel()::create($data);

        // Simpan cover image (jika ada)
        if ($cover instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            $path     = $cover->store('villa-images', 'public');
            $fileName = $cover->getClientOriginalName();
            \App\Models\VillaMedia::create([
                'villa_id'  => $villa->id,
                'file_path' => $path,
                'file_name' => $fileName,
                'type'      => 'image',
            ]);
        } elseif (is_string($cover) && str_contains($cover, '/')) {
            \App\Models\VillaMedia::create([
                'villa_id'  => $villa->id,
                'file_path' => $cover,
                'file_name' => basename($cover),
                'type'      => 'image',
            ]);
        }

        // Simpan gambar lain (bisa banyak)
        foreach ($images as $img) {
            if ($img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path     = $img->store('villa-images', 'public');
                $fileName = $img->getClientOriginalName();
                \App\Models\VillaMedia::create([
                    'villa_id'  => $villa->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'type'      => 'image',
                ]);
            } elseif (is_string($img) && str_contains($img, '/')) {
                \App\Models\VillaMedia::create([
                    'villa_id'  => $villa->id,
                    'file_path' => $img,
                    'file_name' => basename($img),
                    'type'      => 'image',
                ]);
            }
        }

        return $villa;
    }

    protected function afterCreate(): void
    {
        $villa     = $this->record;
        $unitCount = $this->form->getRawState()['unit_count'] ?? 1;

        $currentCount = $villa->units()->count();

        if ($unitCount > $currentCount) {
            for ($i = $currentCount + 1; $i <= $unitCount; $i++) {
                \App\Models\VillaUnit::create([
                    'villa_id'    => $villa->id,
                    'unit_number' => (string) $i,
                    'ical_link'   => null,
                ]);
            }
        } elseif ($unitCount < $currentCount) {
            $unitsToDelete = $villa->units()->orderByDesc('unit_number')->take($currentCount - $unitCount)->get();
            foreach ($unitsToDelete as $unit) {
                $unit->delete();
            }
        }
    }
}
