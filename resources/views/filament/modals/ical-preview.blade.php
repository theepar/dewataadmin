<div>
    @if (isset($events['error']))
        <span class="text-red-600">{{ $events['error'] }}</span>
    @else
        @php
            $filtered = collect($events)->filter(function ($ev) {
                return !empty($ev['SUMMARY']) || !empty($ev['LOCATION']) || !empty($ev['DTSTART']);
            });
        @endphp
        @if ($filtered->isEmpty())
            <span class="text-gray-500">Tidak ada data booking yang bisa ditampilkan.</span>
        @else
            <ul class="list-disc pl-5">
                @foreach ($filtered as $ev)
                    <li class="mb-3">
                        <b>{{ $ev['SUMMARY'] ?? '(Tanpa Judul)' }}</b><br>
                        <span class="text-xs">
                            @php
                                $showAny = false;
                                // Parse DESCRIPTION Airbnb
                                $guestName = $reservationId = $status = $guestCount = null;
                                if (!empty($ev['DESCRIPTION'])) {
                                    if (preg_match('/Guest:\s*(.+)/i', $ev['DESCRIPTION'], $m)) {
                                        $guestName = trim($m[1]);
                                    }
                                    if (preg_match('/Reservation ID:\s*([\w-]+)/i', $ev['DESCRIPTION'], $m)) {
                                        $reservationId = trim($m[1]);
                                    }
                                    if (preg_match('/Status:\s*(.+)/i', $ev['DESCRIPTION'], $m)) {
                                        $status = trim($m[1]);
                                    }
                                    if (preg_match('/guests?:? (\d+)/i', $ev['DESCRIPTION'], $m)) {
                                        $guestCount = $m[1];
                                    }
                                }
                            @endphp
                            @if ($guestName)
                                <b>Guest:</b> {{ $guestName }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @if ($reservationId)
                                <b>Reservation ID:</b> {{ $reservationId }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @if ($status)
                                <b>Status:</b> {{ $status }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @if (!empty($ev['LOCATION']))
                                <b>Property:</b> {{ $ev['LOCATION'] }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @if (!empty($ev['DTSTART']) || !empty($ev['DTEND']))
                                <b>Tanggal:</b>
                                {{ isset($ev['DTSTART']) ? \Carbon\Carbon::parse($ev['DTSTART'])->format('d M Y H:i') : '-' }}
                                -
                                {{ isset($ev['DTEND']) ? \Carbon\Carbon::parse($ev['DTEND'])->format('d M Y H:i') : '-' }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @if ($guestCount)
                                <b>Jumlah Orang:</b> {{ $guestCount }}<br>
                                @php $showAny = true; @endphp
                            @endif
                            @php
                                $durasi = null;
                                if (!empty($ev['DTSTART']) && !empty($ev['DTEND'])) {
                                    $start = \Carbon\Carbon::parse($ev['DTSTART']);
                                    $end = \Carbon\Carbon::parse($ev['DTEND']);
                                    $durasi = $start->diffInDays($end);
                                }
                            @endphp
                            @if ($durasi)
                                <b>Durasi:</b> {{ $durasi . ' malam' }}
                                @php $showAny = true; @endphp
                            @endif
                            @if (!$showAny)
                                <span class="text-gray-500">Tidak ada detail booking.</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
