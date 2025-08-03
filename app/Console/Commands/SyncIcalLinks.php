<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\IcalLink;
use App\Models\IcalEvent;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;

class SyncIcalLinks extends Command
{
    protected $signature = 'ical:sync';
    protected $description = 'Sync all iCal links and update events if changed';

    public function handle()
    {
        $links = IcalLink::all();
        foreach ($links as $record) {
            $icalUrl = $record->ical_url;
            $ics = @file_get_contents($icalUrl);
            if (!$ics) {
                $this->error("Gagal mengambil data dari URL: $icalUrl");
                continue;
            }
            if (!class_exists('om\\IcalParser')) {
                $this->error('Pustaka icalparser belum terpasang');
                continue;
            }
            $parser = new \om\IcalParser();
            $parser->parseString($ics);
            $events = $parser->getEvents();
            $updated = 0;
            $inserted = 0;
            foreach ($events as $ev) {
                $uid = $ev['UID'] ?? null;
                $summary = $ev['SUMMARY'] ?? null;
                $description = $ev['DESCRIPTION'] ?? null;
                $start = isset($ev['DTSTART']) ? Carbon::parse($ev['DTSTART']) : null;
                $end = isset($ev['DTEND']) ? Carbon::parse($ev['DTEND']) : null;
                $status = $ev['STATUS'] ?? null;
                $propertyName = $ev['LOCATION'] ?? null;
                $guestName = $reservationId = $guestCount = null;
                if (!empty($description)) {
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
                    'ical_link_id' => $record->id,
                    'uid' => $uid,
                ];
                $data = [
                    'summary' => $summary,
                    'description' => $description,
                    'start_date' => $start,
                    'end_date' => $end,
                    'status' => $status,
                    'guest_name' => $guestName,
                    'reservation_id' => $reservationId,
                    'property_name' => $propertyName,
                    'jumlah_orang' => $guestCount,
                    'durasi' => $durasi,
                    'is_cancelled' => $isCancelled,
                ];
                $existing = IcalEvent::where($where)->first();
                if ($existing) {
                    $isDifferent = false;
                    foreach ($data as $k => $v) {
                        if ($existing->$k != $v) {
                            $isDifferent = true;
                            break;
                        }
                    }
                    if ($isDifferent) {
                        $existing->update($data);
                        $updated++;
                    }
                } else {
                    IcalEvent::create(array_merge($where, $data));
                    $inserted++;
                }
            }
            $record->last_synced_at = now();
            $record->save();
            $this->info("[{$record->name}] Sync selesai: $inserted event baru, $updated event diupdate.");
        }
        return 0;
    }
}
