<div style="padding: 8px;">
    <h2 style="margin-bottom: 10px;">{{ $villa->name }}</h2>
    <div style="margin-bottom: 8px;">
        <span style="font-weight: bold;">Status Kepemilikan:</span> {{ $villa->ownership_status }}
    </div>
    <div style="margin-bottom: 8px;">
        <span style="font-weight: bold;">Harga (IDR):</span> {{ number_format($villa->price_idr) }}
    </div>
    <div style="margin-bottom: 8px;">
        <span style="font-weight: bold;">Harga (USD):</span> {{ number_format($villa->price_usd) }}
    </div>
    <div style="margin-bottom: 8px;">
        <span style="font-weight: bold;">Deskripsi:</span>
        <div style="margin-top: 4px;">{!! $villa->description !!}</div>
    </div>
    <hr style="margin: 16px 0;">
    <h4 style="margin-bottom: 8px;">Gambar:</h4>
    <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
        @forelse ($villa->media->where('type', 'image') as $media)
            <div style="background: #222; padding: 6px; border-radius: 8px;">
                <img src="{{ asset('storage/' . $media->file_path) }}" alt="Gambar Villa"
                    style="width: 140px; height: 100px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 8px #0002;">
                <div style="font-size: 11px; color: #aaa; margin-top: 2px; text-align: center;">{{ $media->file_name }}
                </div>
            </div>
        @empty
            <span style="color: #aaa;">Belum ada gambar.</span>
        @endforelse
    </div>
    <h4 style="margin-bottom: 8px;">Video:</h4>
    <div style="display: flex; flex-wrap: wrap; gap: 12px;">
        @forelse ($villa->media->where('type', 'video') as $media)
            <div style="background: #222; padding: 6px; border-radius: 8px;">
                <video src="{{ asset('storage/' . $media->file_path) }}" width="320" controls
                    style="border-radius:6px; box-shadow: 0 2px 8px #0002; background: #000;"></video>
                <div style="font-size: 11px; color: #aaa; margin-top: 2px; text-align: center;">{{ $media->file_name }}
                </div>
            </div>
        @empty
            <span style="color: #aaa;">Belum ada video.</span>
        @endforelse
    </div>
</div>
