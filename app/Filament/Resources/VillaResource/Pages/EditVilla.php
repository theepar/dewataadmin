<?php

namespace App\Filament\Resources\VillaResource\Pages;

use Filament\Actions;
use App\Models\VillaUnit;
use Illuminate\Support\Facades\Storage;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\VillaResource;

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
        // Ambil semua gambar lama milik property ini
        $oldImages = $record->media()->where('type', 'image')->pluck('file_path')->toArray();
        $newImages = $data['images'] ?? [];
        $cover     = $data['cover_image'][0] ?? null;

        // Ambil cover lama (gambar pertama)
        $oldCover = $record->media()->where('type', 'image')->pluck('file_path')->first();

        // Gabungkan cover dan images baru untuk membandingkan dengan gambar lama
        $allNewImages = $newImages;
        if ($cover) {
            array_unshift($allNewImages, $cover);
        }

        // Update/replace cover jika diganti
        if ($cover && $oldCover && $cover !== $oldCover) {
            // Hapus cover lama
            Storage::disk('public')->delete($oldCover);
            \App\Models\VillaMedia::where('villa_id', $record->id)->where('file_path', $oldCover)->delete();

            // Simpan cover baru jika upload baru
            if ($cover instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $fileName = $cover->getClientOriginalName();
                $path     = 'villa-images/' . $fileName;
                $cover->storeAs('villa-images', $fileName, 'public');
                \App\Models\VillaMedia::create([
                    'villa_id'  => $record->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'type'      => 'image',
                ]);
            } elseif (is_string($cover) && str_contains($cover, '/')) {
                if (! in_array($cover, $oldImages) && $cover) {
                    $fileName = $cover;
                    \App\Models\VillaMedia::create([
                        'villa_id'  => $record->id,
                        'file_path' => $cover,
                        'file_name' => $fileName,
                        'type'      => 'image',
                    ]);
                }
            }
        }

        // Simpan gambar lain (hanya yang baru diupload)
        foreach ($newImages as $img) {
            if ($img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $fileName = $img->getClientOriginalName();
                $path     = 'villa-images/' . $fileName;
                $img->storeAs('villa-images', $fileName, 'public');
                \App\Models\VillaMedia::create([
                    'villa_id'  => $record->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'type'      => 'image',
                ]);
            } elseif (is_string($img) && str_contains($img, '/')) {
                if (! in_array($img, $oldImages) && $img) {
                    $fileName = $img;
                    \App\Models\VillaMedia::create([
                        'villa_id'  => $record->id,
                        'file_path' => $img,
                        'file_name' => $fileName,
                        'type'      => 'image',
                    ]);
                }
            }
        }

        unset($data['cover_image'], $data['images'], $data['video']);

        // Update data utama villa
        $record->update($data);

        // Sinkronisasi: Hapus record di database jika file tidak ada di local
        $dbFiles = \App\Models\VillaMedia::where('villa_id', $record->id)->pluck('file_path')->toArray();
        foreach ($dbFiles as $file) {
            if (!Storage::disk('public')->exists($file)) {
                \App\Models\VillaMedia::where('villa_id', $record->id)->where('file_path', $file)->delete();
            }
        }

        return $record;
    }

    protected function afterSave(): void
    {
        $villa     = $this->record;
        $unitCount = $this->form->getRawState()['unit_count'] ?? 1;

        $currentCount = $villa->units()->count();

        if ($unitCount > $currentCount) {
            for ($i = $currentCount + 1; $i <= $unitCount; $i++) {
                VillaUnit::create([
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
