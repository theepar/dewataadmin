# Dewata Management Backend

Aplikasi backend berbasis Laravel untuk manajemen villa, booking, dan sinkronisasi kalender eksternal (Airbnb iCal). Sistem ini mengintegrasikan panel admin Filament, manajemen media, peran pengguna, dan API untuk aplikasi mobile dan website.

---

## Fitur Utama

- **Multi Device Login & Notifikasi**
  - User dapat login di banyak device (mobile dan browser) secara bersamaan.
  - Setiap device menyimpan token akses dan FCM token unik di database.
  - Push notifikasi FCM otomatis ke semua device user jika ada booking baru atau update event.
  - Device name dan FCM token dapat diupdate/dihapus per device (logout, update token).
  - Login dari browser otomatis terdeteksi sebagai "Browser", login dari mobile terdeteksi dari device name.
  - History login mencatat asal login (device name, IP, user agent, waktu login).

- **Manajemen Villa**
  - CRUD data villa: nama, harga, status kepemilikan, deskripsi, gambar, galeri, video.
  - Info kamar: jumlah bedroom, bed, bathroom.
  - Fasilitas (amenities): input/edit dinamis via repeater, tersimpan dalam format JSON.
  - Relasi villa dengan media (gambar/video) dan user (setiap villa hanya bisa dikelola satu user, admin bisa akses semua).
  - **Manajemen Unit Villa:** Input jumlah unit villa langsung dari form, otomatis membuat/menghapus data unit di tabel `villa_units` sesuai input. Setiap unit villa dapat memiliki link iCal sendiri.
  - **Sinkronisasi jumlah unit:** Edit jumlah unit di villa resource akan otomatis menambah/menghapus unit di tabel `villa_units`.
- **Manajemen User & Role**
  - Otentikasi dan otorisasi berbasis peran (admin, user) menggunakan Spatie Permission.
  - User hanya bisa melihat dan mengelola villa miliknya sendiri.
  - Admin bisa mengelola semua data.
- **Sinkronisasi Kalender Airbnb**
  - Otomatis unduh dan proses file iCal (.ics) dari Airbnb.
  - Event booking disimpan di tabel `ical_events`.
  - Sinkronisasi otomatis setiap 20 menit via Laravel Scheduler.
  - Hanya update jika ada perubahan event booking.
  - **Manajemen iCal per unit:** Setiap unit villa dapat memiliki link iCal berbeda, sehingga sinkronisasi event lebih detail per unit.
- **Manajemen Media**
  - Upload dan pengelolaan gambar/video villa dengan Spatie Media Library.
  - Gambar villa tersimpan di tabel `villa_media` dan relasi ke villa.
- **Admin Panel Filament**
  - Dashboard modern untuk mengelola semua data, akses dibatasi sesuai peran.
  - Input/edit amenities dan info kamar langsung di form.
  - Input jumlah unit villa langsung di form, otomatis sinkron ke tabel unit.
  - Generate dan kelola API key website untuk akses data villa dari frontend.
- **API untuk Mobile & Website**
  - Endpoint JSON untuk aplikasi mobile (autentikasi dengan Laravel Sanctum).
  - Endpoint khusus website dengan API key (header `Admin`), bisa akses semua villa.
  - Data villa, event, dan link iCal bisa diakses sesuai role dan API key.
  - **Endpoint unit villa:** Data unit villa dan link iCal per unit tersedia di API.
- **Notifikasi**
  - Integrasi dengan Firebase Cloud Messaging untuk push notifikasi ke aplikasi mobile dan multi device.
  - Isi notifikasi (title, body, tanggal update) diambil langsung dari event booking terbaru.
- **History Login**
  - Setiap login user tercatat di tabel `login_histories` (IP, device name, user agent, waktu login).
  - History login hanya bisa dilihat oleh user sendiri di dashboard.
  - Data login history lengkap, bisa membedakan login dari browser atau mobile.
- **Website API Key Management**
  - Admin dapat generate API key unik untuk tiap website frontend.
  - API key diverifikasi di backend sebelum mengirim data villa.

---

## Struktur Database

- **users**: Data user, role, otentikasi.
- **villa_user**: Relasi villa dengan user.

---

## Endpoint API

API sekarang disederhanakan: hanya ada endpoint untuk autentikasi (Auth) dan data villa. Semua data terkait (unit, media, iCal links, event) sudah termasuk di dalam response villa.

| Controller     | Endpoint                       | Keterangan |
|----------------|--------------------------------|-----------|
| AuthController | POST `/api/login`              | Login user (mobile/admin/user) |
|                | POST `/api/logout`             | Logout user |
| VillaController| GET `/api/villas`              | Dapatkan daftar villa (mengembalikan relasi: units, media, ical_links, ical_events). Menggunakan autentikasi (Sanctum) atau header API key untuk website. |
|                | GET `/api/villas/{id}`         | Detail villa lengkap (termasuk units, media, ical_links, ical_events). Menggunakan autentikasi (Sanctum) atau header API key untuk website. |
|                | GET `/api/website/villas`      | Endpoint khusus untuk website (akses dengan header `Admin: <api_key>`) — mengembalikan villa lengkap untuk frontend. |

Catatan singkat:
- Untuk aplikasi mobile/pegawai/admin gunakan token (Laravel Sanctum).
- Untuk website frontend gunakan header: `Admin: <api_key>`.
- Response villa sudah mencakup semua resource terkait sehingga tidak perlu endpoint terpisah untuk units/media/ical/events kecuali diperlukan filter

---

## Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/theepar/dewatabackend
   cd dewatabackend
   ```
2. Install dependency PHP dan JS:
   ```bash
   composer install
   npm install && npm run build
   ```
3. Copy file `.env.example` ke `.env` dan sesuaikan konfigurasi database
4. Generate key aplikasi:
   ```bash
   php artisan key:generate
   ```
5. Jalankan migrasi dan seeder:
   ```bash
   php artisan migrate --seed
   ```
6. Jalankan server lokal:
   ```bash
   php artisan serve
   ```

---

## Penggunaan

- Akses admin panel di `http://localhost:8000/admin`
- Endpoint API tersedia di `http://localhost:8000/api`
- Login dengan user yang sudah di-seed atau buat user baru melalui admin panel
- Kelola villa, unit villa, user, media, amenities, dan API key website dari dashboard Filament

---

## Contoh Command Sinkronisasi iCal

Jalankan perintah berikut untuk sinkronisasi manual event booking dari Airbnb:
```bash
php artisan ical:sync
```
Command ini juga dijalankan otomatis setiap 20 menit melalui scheduler.

### Scheduler Otomatis

Untuk menjalankan sinkronisasi otomatis, pastikan scheduler Laravel aktif di server/hosting Anda. Jalankan perintah berikut agar scheduler berjalan di background:

```bash
php artisan schedule:work
```
Atau jika menggunakan cron, tambahkan ke crontab:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```
Scheduler akan menjalankan sync setiap 20 menit secara otomatis.

---

## Cara Integrasi Website Frontend

1. Admin generate API key di dashboard Filament.
2. Copy API key ke konfigurasi website frontend (misal `.env` Next.js).
3. Website frontend fetch data villa dengan header:
   ```
   Admin: <api_key>
   ```
   Contoh di Next.js:
   ```js
   fetch('https://your-backend-domain.com/api/website/villas', {
     headers: { 'Admin': process.env.NEXT_PUBLIC_VILLA_API_KEY }
   })
   ```

---

## Kontribusi

Silakan buat pull request atau issue untuk saran dan perbaikan.

---

## Kontak & Dukungan

Untuk pertanyaan atau dukungan, silakan hubungi developer melalui email atau issue di repository ini.

---

## Lisensi

Proyek ini menggunakan lisensi MIT.
