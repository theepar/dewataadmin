<div>
    @if (empty($events))
        <span class="text-gray-500">Tidak ada data booking yang bisa ditampilkan.</span>
    @else
        <ul class="list-disc pl-5">
            @foreach ($events as $ev)
                <li class="mb-3">
                    <b>{{ $ev['summary'] ?? '(Tanpa Judul)' }}</b><br>
                    <span class="text-xs">
                        @if (!empty($ev['guest_name']))
                            <b>Guest:</b> {{ $ev['guest_name'] }}<br>
                        @endif
                        @if (!empty($ev['reservation_id']))
                            <b>Reservation ID:</b> {{ $ev['reservation_id'] }}<br>
                        @endif
                        @if (!empty($ev['status']))
                            <b>Status:</b> {{ $ev['status'] }}<br>
                        @endif
                        @if (!empty($ev['property_name']))
                            <b>Property:</b> {{ $ev['property_name'] }}<br>
                        @endif
                        @if (!empty($ev['start_date']) || !empty($ev['end_date']))
                            <b>Tanggal:</b>
                            @php
                                $start = $ev['start_date'] ? \Carbon\Carbon::parse($ev['start_date']) : null;
                                $end = $ev['end_date'] ? \Carbon\Carbon::parse($ev['end_date']) : null;
                            @endphp
                            {{ $start ? $start->format('d M Y') : '-' }} {{ $start ? $start->format('H:i') : '' }}
                            -
                            {{ $end ? $end->format('d M Y') : '-' }} {{ $end ? $end->format('H:i') : '' }}<br>
                        @endif
                        @if (!empty($ev['jumlah_orang']))
                            <b>Jumlah Orang:</b> {{ $ev['jumlah_orang'] }}<br>
                        @endif
                        @if (!empty($ev['durasi']))
                            <b>Durasi:</b> {{ $ev['durasi'] . ' malam' }}<br>
                        @endif
                        @if (empty($ev['guest_name']) &&
                                empty($ev['reservation_id']) &&
                                empty($ev['status']) &&
                                empty($ev['property_name']) &&
                                empty($ev['start_date']) &&
                                empty($ev['end_date']) &&
                                empty($ev['jumlah_orang']) &&
                                empty($ev['durasi']))
                            <span class="text-gray-500">Tidak ada detail booking.</span>
                        @endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
