# Dewata Management Backend

Aplikasi backend berbasis Laravel untuk manajemen villa, booking, dan sinkronisasi kalender eksternal (Airbnb iCal). Sistem ini mengintegrasikan panel admin Filament, manajemen media, peran pengguna, dan API untuk aplikasi mobile.

---

## Fitur Utama

- **Manajemen Villa**: CRUD data villa, termasuk detail, harga, status kepemilikan, gambar, galeri, dan video.
- **Manajemen User & Role**: Sistem otentikasi dan otorisasi berbasis peran (admin, pegawai) menggunakan Spatie Permission.
- **Sinkronisasi Kalender Airbnb**: Otomatis mengunduh dan memproses file iCal (.ics) dari Airbnb, menyimpan event booking ke database. Sinkronisasi berjalan otomatis setiap 20 menit menggunakan Laravel Scheduler, dan hanya memperbarui data jika ada perubahan pada event booking.
- **Manajemen Media**: Upload dan pengelolaan gambar/video villa dengan Spatie Media Library.
- **Admin Panel Filament**: Dashboard modern untuk mengelola semua data, akses dibatasi sesuai peran.
- **API untuk Mobile**: Endpoint JSON untuk aplikasi mobile, otentikasi dengan Laravel Sanctum.
- **Notifikasi**: Integrasi dengan Firebase Cloud Messaging untuk push notifikasi ke aplikasi mobile.

---

## Struktur Folder

- `app/Models` : Model utama (User, Villa, IcalLink, IcalEvent)
- `app/Filament/Resources` : Resource untuk Filament admin
- `app/Console/Commands` : Command artisan kustom (sinkronisasi iCal)
- `database/migrations` : Migrasi database
- `database/seeders` : Seeder data awal (roles, users, villa)
- `routes/` : Definisi route web dan API

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

## Kontribusi

Silakan buat pull request atau issue untuk saran dan perbaikan.

---

## Kontak & Dukungan

Untuk pertanyaan atau dukungan, silakan hubungi developer melalui email atau issue di repository ini.

---

## Lisensi

Proyek ini menggunakan lisensi MIT.
