<?php

namespace App\Console\Commands;

use App\Models\IcalEvent;
use App\Models\VillaUnit;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SyncIcalLinks extends Command
{
    protected $signature   = 'ical:sync {unit_id?}';
    protected $description = 'Sync all iCal links and update events if changed';

    public function handle()
    {
        $unitId = $this->argument('unit_id');
        $links = VillaUnit::when($unitId, function ($query, $unitId) {
            return $query->where('id', $unitId);
        })->get();

        foreach ($links as $record) {
            $icalUrl = $record->ical_link;
            if (empty($icalUrl)) {
                $this->warn("Unit ID {$record->id} tidak punya iCal URL, di-skip.");
                continue;
            }
            $ics     = @file_get_contents($icalUrl);
            if (! $ics) {
                $this->error("Gagal mengambil data dari URL: $icalUrl");
                continue;
            }
            if (! class_exists('om\\IcalParser')) {
                $this->error('Pustaka icalparser belum terpasang');
                continue;
            }
            $parser = new \om\IcalParser();
            $parser->parseString($ics);
            $events   = $parser->getEvents();
            $updated  = 0;
            $inserted = 0;
            foreach ($events as $ev) {
                $uid          = $ev['UID'] ?? uniqid('ical_');
                $summary      = $ev['SUMMARY'] ?? null;
                $description  = $ev['DESCRIPTION'] ?? null;
                $start        = isset($ev['DTSTART']) ? Carbon::parse($ev['DTSTART']) : null;
                $end          = isset($ev['DTEND'])   ? Carbon::parse($ev['DTEND'])   : null;
                $status       = $ev['STATUS'] ?? null;
                $propertyName = $ev['LOCATION'] ?? null;
                $guestName    = $reservationId    = $guestCount    = null;
                if (! empty($description)) {
                    if (preg_match('/Guest:\\s*(.+)/i', $description, $m)) {
                        $guestName = trim($m[1]);
                    }
                    if (preg_match('/Reservation ID:\\s*([\\w-]+)/i', $description, $m)) {
                        $reservationId = trim($m[1]);
                    }
                    if (preg_match('/guests?:? (\\d+)/i', $description, $m)) {
                        $guestCount = $m[1];
                    }
                }
                $durasi = null;
                if ($start && $end) {
                    $durasi = $start->diffInDays($end);
                }
                $isCancelled = ($status && strtolower($status) === 'cancelled');
                $where = [
                    'villa_unit_id' => $record->id,
                    'uid'           => $uid,
                ];
                $data = [
                    'summary'        => $summary,
                    'description'    => $description,
                    'start_date'     => $start,
                    'end_date'       => $end,
                    'status'         => $status,
                    'guest_name'     => $guestName,
                    'reservation_id' => $reservationId,
                    'property_name'  => $propertyName,
                    'jumlah_orang'   => $guestCount,
                    'durasi'         => $durasi,
                    'is_cancelled'   => $isCancelled,
                    'villa_unit_id'  => $record->id, // atau $record->villa_unit_id sesuai model
                ];
                $existing = IcalEvent::where($where)->first();
                if ($existing) {
                    $existing->update($data); // update jika sudah ada
                } else {
                    IcalEvent::create(array_merge($where, $data)); // insert jika belum ada
                }
            }
            $record->last_synced_at = now()->setTimezone('Asia/Makassar');
            $record->save();
            $this->info("[{$record->name}] Sync selesai: $inserted event baru, $updated event diupdate.");
        }
        return 0;
    }
}
