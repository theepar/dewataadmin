@php
    // Ambil semua gambar villa
    $gambarVilla = ($villa->media ?? collect())->where('type', 'image')->values();
    // Ambil cover (foto pertama)
    $cover = $gambarVilla->first();
    $fotoLain = $gambarVilla->skip(1);
@endphp

{{-- KOLOM KANAN: INFORMASI DETAIL --}}
<div class="flex flex-col p-4 md:col-span-2">
    <div>
        <div>
            <h3 class="mt-2 text-lg font-semibold text-white">Nama properti ini</h3>
            <div class="prose prose-invert text-md line-clamp-6 max-w-none text-gray-400">{{ $villa->name }}</div>
        </div>
        @if ($villa->location)
            <h3 class="mt-2 text-lg font-semibold text-white">Lokasi properti ini</h3>
            <div class="prose prose-invert text-md line-clamp-6 max-w-none text-gray-400"> <a
                    href="{{ $villa->location }}" target="_blank"
                    class="mt-2 inline-flex items-center text-base text-gray-400 hover:text-blue-400">
                    {{ $villa->location ?? 'Lihat di Peta' }}</a>
            </div>
        @endif

        {{-- Tambahkan info ownership --}}
        @if (count($statuses))
            <div>
                <h3 class="mt-2 text-lg font-semibold text-white">Status Kepemilikan</h3>
                <div class="prose prose-invert text-md line-clamp-6 max-w-none text-gray-400">
                    {{ implode(', ', array_filter(array_map('trim', $statuses))) }}</div>
            </div>
        @endif
    </div>

    <div>
        <h3 class="mt-2 text-lg font-semibold text-white">Tentang properti ini</h3>
        <div class="prose prose-invert text-md line-clamp-6 max-w-none text-gray-400">{!! $villa->description ?? 'Tidak ada deskripsi.' !!}</div>
    </div>
    <div>
        <h3 class="mt-2 text-lg font-semibold text-white">Harga properti ini</h3>
        <div class="prose prose-invert line-clamp-6 max-w-none text-lg text-gray-400">
            Rp{{ number_format($villa->price_idr, 0, ',', '.') }}</div>
    </div>

    {{-- Info kelengkapan kamar --}}
    <div class="mt-6 flex flex-wrap gap-4 text-base text-gray-400">
        <div><span class="font-semibold text-white">{{ $villa->bedroom }}</span> bedroom</div>
        <div><span class="font-semibold text-white">{{ $villa->bed }}</span> bed</div>
        <div><span class="font-semibold text-white">{{ $villa->bathroom }}</span> bathroom</div>
    </div>

    {{-- Amenities --}}
    @if (!empty($villa->amenities) && is_array($villa->amenities))
        @php
            $icons = [
                'Kitchen' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 10h16M4 14h16M9 18V6m6 12V6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Dedicated workspace' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="8" width="16" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 8V6a4 4 0 1 1 8 0v2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Hot tub' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 16v2a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2v-2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Dryer' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Wifi' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13a10 10 0 0 1 14 0M8.5 16.5a5 5 0 0 1 7 0M12 20h.01" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Free parking on premises' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="6" rx="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'TV' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="15" rx="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M17 2l-5 5-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Exterior security cameras on property' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="10" rx="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Carbon monoxide alarm' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/><line x1="8" y1="12" x2="16" y2="12" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                'Smoke alarm' =>
                    '<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M8 16h8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                // Tambahkan mapping icon lain sesuai kebutuhan
            ];
            $defaultIcon =
                '<svg class="h-5 w-5 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
            $notIncludedIcon =
                '<svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" stroWke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';

            $included = collect($villa->amenities)->where('available', true)->values();
            $notIncluded = collect($villa->amenities)->where('available', false)->values();
        @endphp

        <div class="mt-6">
            <h3 class="mb-2 text-lg font-semibold text-white">What this place offers</h3>
            <div class="grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2">
                @foreach ($included as $amenity)
                    <div class="flex items-center gap-3">
                        {!! $icons[$amenity['name']] ?? $defaultIcon !!}
                        <span>{{ $amenity['name'] }}</span>
                    </div>
                @endforeach
            </div>

            @if ($notIncluded->count())
                <h4 class="mb-2 mt-6 text-base font-semibold text-gray-400">Not included</h4>
                <div class="grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-2">
                    @foreach ($notIncluded as $amenity)
                        <div class="flex items-center gap-3 text-gray-500">
                            {!! $notIncludedIcon !!}
                            <span class="line-through">{{ $amenity['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>

<div class="grid grid-cols-2 gap-2 md:grid-cols-3 md:grid-cols-4">
    {{-- Foto cover (foto pertama) --}}
    @if ($cover)
        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-gray-800">
            <img src="{{ asset('storage/' . $cover->file_path) }}" alt="Cover Villa: {{ $cover->file_name }}"
                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105">
            <span class="absolute left-2 top-2 z-10 rounded bg-blue-600 px-2 py-1 text-xs font-bold text-white shadow">
                Cover Foto
            </span>
        </div>
    @endif

    {{-- Foto lainnya --}}
    @foreach ($fotoLain as $img)
        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-gray-800">
            <img src="{{ asset('storage/' . $img->file_path) }}" alt="Gambar Villa: {{ $img->file_name }}"
                class="h-full w-full object-cover transition-transform duration-300 hover:scale-105">
        </div>
    @endforeach
</div>
