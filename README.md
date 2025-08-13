# Dewata Management Backend

Aplikasi backend berbasis Laravel untuk manajemen villa, booking, dan sinkronisasi kalender eksternal (Airbnb iCal). Sistem ini mengintegrasikan panel admin Filament, manajemen media, peran pengguna, dan API untuk aplikasi mobile dan website.

---

## Fitur Utama

- **Manajemen Villa**
  - CRUD data villa: nama, harga, status kepemilikan, deskripsi, gambar, galeri, video.
  - Info kamar: jumlah bedroom, bed, bathroom.
  - Fasilitas (amenities): input/edit dinamis via repeater, tersimpan dalam format JSON.
  - Relasi villa dengan media (gambar/video) dan pegawai (setiap villa hanya bisa dikelola satu pegawai, admin bisa akses semua).
  - **Manajemen Unit Villa:** Input jumlah unit villa langsung dari form, otomatis membuat/menghapus data unit di tabel `villa_units` sesuai input. Setiap unit villa dapat memiliki link iCal sendiri.
  - **Sinkronisasi jumlah unit:** Edit jumlah unit di villa resource akan otomatis menambah/menghapus unit di tabel `villa_units`.
- **Manajemen User & Role**
  - Otentikasi dan otorisasi berbasis peran (admin, pegawai) menggunakan Spatie Permission.
  - Pegawai hanya bisa melihat dan mengelola villa miliknya sendiri.
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
  - Integrasi dengan Firebase Cloud Messaging untuk push notifikasi ke aplikasi mobile.
- **History Login**
  - Setiap login user tercatat di tabel `login_histories` (IP, device, waktu login).
  - History login hanya bisa dilihat oleh user sendiri di dashboard.
- **Website API Key Management**
  - Admin dapat generate API key unik untuk tiap website frontend.
  - API key diverifikasi di backend sebelum mengirim data villa.

---

## Struktur Database

- **users**: Data user, role, otentikasi.
- **villas**: Data villa (nama, harga, deskripsi, status, info kamar, amenities).
- **villa_units**: Data unit per villa (relasi ke villa, nomor unit, link iCal per unit).
- **villa_media**: Gambar/video villa, relasi ke villa.
- **villa_user**: Relasi villa dengan user (pegawai).
- **ical_links**: Link iCal Airbnb, relasi ke unit villa.
- **ical_events**: Event booking dari iCal (summary, tanggal, status, tamu, dll).
- **login_histories**: History login user (user_id, IP, device, waktu login).
- **website_api_keys**: API key untuk website frontend (website_name, api_key).

---

## Endpoint API

| Controller         | Endpoint                       | Keterangan                                    |
|--------------------|-------------------------------|------------------------------------------------|
| AuthController     | POST `/api/login`              | Login user (mobile/admin/pegawai)              |
|                    | POST `/api/logout`             | Logout user                                    |
| VillaController    | GET `/api/villas`              | Get villa (admin/pegawai, sesuai role)         |
|                    | GET `/api/villas/{id}`         | Get detail villa (admin/pegawai, sesuai role)  |
|                    | GET `/api/website/villas`      | Get semua villa untuk website (pakai API key)  |
| IcalLinkController | GET `/api/ical-links`          | Get semua iCal link (admin/pegawai)            |
|                    | GET `/api/ical-links/{id}`     | Get detail iCal link                           |
| IcalEventController| GET `/api/ical-events`         | Get semua event booking                        |
|                    | GET `/api/ical-events/{id}`    | Get detail event booking                       |
| VillaUnitController| GET `/api/villa-units`         | Get semua unit villa dan link iCal per unit    |
|                    | GET `/api/villa-units/{id}`    | Get detail unit villa                          |

**Catatan:**  
- Endpoint `/api/website/villas` hanya bisa diakses dengan header `Admin: <api_key>`.
- Endpoint lain menggunakan autentikasi token (Sanctum).

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
- Website frontend dapat mengambil data villa dan unit dengan API key yang digenerate admin

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
