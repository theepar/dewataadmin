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
        // Ambil gambar lama
        $oldImages = $record->media()->where('type', 'image')->pluck('file_path')->toArray();
        $newImages = $data['images'] ?? [];
        $cover     = $data['cover_image'][0] ?? null;

        // Jika tidak ada input gambar baru, jangan hapus gambar lama
        if (empty($newImages) && !$cover) {
            unset($data['cover_image'], $data['images'], $data['video']);
            $record->update($data);
            return $record;
        }

        // Ambil cover lama (gambar pertama)
        $oldCover = $record->media()->where('type', 'image')->pluck('file_path')->first();

        // Gabungkan cover dan images baru untuk membandingkan dengan gambar lama
        $allNewImages = $newImages;
        if ($cover) {
            array_unshift($allNewImages, $cover);
        }

        // Hapus gambar yang dihapus user (tidak ada di input baru)
        $deletedImages = array_diff($oldImages, $allNewImages);
        foreach ($deletedImages as $img) {
            Storage::disk('public')->delete($img);
            \App\Models\VillaMedia::where('villa_id', $record->id)->where('file_path', $img)->delete();
        }

        // Update/replace cover jika diganti
        if ($cover && $oldCover && $cover !== $oldCover) {
            // Hapus cover lama
            Storage::disk('public')->delete($oldCover);
            \App\Models\VillaMedia::where('villa_id', $record->id)->where('file_path', $oldCover)->delete();

            // Simpan cover baru jika upload baru
            if ($cover instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path     = $cover->store('villa-images', 'public');
                $fileName = $cover->getClientOriginalName();
                \App\Models\VillaMedia::create([
                    'villa_id'  => $record->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'type'      => 'image',
                ]);
            } elseif (is_string($cover) && str_contains($cover, '/')) {
                // Jika cover sudah ada di storage, simpan ke DB jika belum ada
                if (! in_array($cover, $oldImages)) {
                    \App\Models\VillaMedia::create([
                        'villa_id'  => $record->id,
                        'file_path' => $cover,
                        'file_name' => basename($cover),
                        'type'      => 'image',
                    ]);
                }
            }
        }

        // Simpan gambar lain (hanya yang baru diupload)
        foreach ($newImages as $img) {
            if ($img instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path     = $img->store('villa-images', 'public');
                $fileName = $img->getClientOriginalName();
                \App\Models\VillaMedia::create([
                    'villa_id'  => $record->id,
                    'file_path' => $path,
                    'file_name' => $fileName,
                    'type'      => 'image',
                ]);
            } elseif (is_string($img) && str_contains($img, '/')) {
                // Jika gambar lama sudah ada, tidak perlu upload ulang
                if (! in_array($img, $oldImages)) {
                    \App\Models\VillaMedia::create([
                        'villa_id'  => $record->id,
                        'file_path' => $img,
                        'file_name' => basename($img),
                        'type'      => 'image',
                    ]);
                }
            }
        }

        unset($data['cover_image'], $data['images'], $data['video']);

        // Tambahkan baris ini agar data utama villa (termasuk ownership_status) ikut terupdate:
        $record->update($data);

        return $record;
    }

    protected function afterSave(): void
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
