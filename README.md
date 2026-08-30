# Ads Aggregator

## Instalasi

Ada dua cara untuk menjalankan aplikasi ini: secara Native (tanpa Docker) atau menggunakan Docker penuh (direkomendasikan).

### 1. Konfigurasi Environment (Wajib)
Buat file konfigurasi `.env` dengan menyalin file `.env.example`:
```bash
cp .env.example .env
```
Sesuaikan kredensial di `.env`. **Penting:** Jika menggunakan Docker, ubah `DB_HOST` menjadi `db`.
```env
CI_BASE_URL=http://localhost:8181/
DB_HOST=127.0.0.1   # Ubah menjadi 'db' jika pakai Docker
DB_NAME=aggregator
DB_USER=user
DB_PASSWORD=pass
DB_ROOT_PASSWORD=root
```

---

### Opsi A: Menjalankan dengan Docker (Direkomendasikan)
Cara paling instan. Sudah membundel Nginx, PHP, MySQL, dan Cron Job Scheduler.

**Prerequisites:** Docker & Docker Compose terinstall.

**Langkah:**
1. Pastikan `DB_HOST=db` di file `.env`.
2. Buka terminal di folder project dan jalankan:
   ```bash
   docker compose up -d --build
   ```
3. Selesai! Akses aplikasi di 👉 **http://localhost:8181**
*(Database akan di-seed otomatis pada run pertama, dan Cron jobs sudah berjalan di background).*

---

### Opsi B: Menjalankan Native (Tanpa Docker)
Gunakan cara ini jika Anda ingin memakai web server lokal bawaan OS Anda (seperti XAMPP, MAMP, atau native Nginx).

**Prerequisites:** PHP 7.4+, Composer, MySQL Server, Web Server (Apache/Nginx).

**Langkah:**
1. **Install Library PHP:**
   ```bash
   composer install
   ```
2. **Setup Database Manual:**
   - Buat database baru di MySQL lokal Anda.
   - Import struktur tabel: `migration/tables.sql`.
   - Import data dummy: `migration/dummy.sql`.
3. **Konfigurasi Web Server:**
   - Arahkan *Document Root* web server lokal Anda ke folder project ini.
   - Atur `CI_BASE_URL` di file `.env` sesuai dengan domain lokal Anda (misal: `http://localhost/ads-aggregator/`).
4. Selesai! Akses aplikasi melalui URL lokal Anda.


---

## Menjalankan Cron Jobs
Sistem ini membutuhkan cron jobs untuk menarik data dari API (Facebook, Instagram, GA4, YouTube) secara berkala. Terdapat dua fungsi utama untuk setiap platform:
- `fetch`: Menarik postingan/konten terbaru.
- `sync`: Menarik data metrik (insights).

**1. Eksekusi Manual:**
Jalankan perintah ini di direktori root project:
```bash
# Menjalankan platform spesifik (contoh: facebook, instagram, youtube, ga4):
php index.php Cron/Platform fetch facebook
php index.php Cron/Platform sync facebook

# Menjalankan SEMUA platform sekaligus:
php index.php Cron/Platform fetch all
php index.php Cron/Platform sync all

# Menentukan rentang waktu spesifik (format: YYYY-MM-DD):
# Penggunaan: php index.php Cron/Platform [action] [platform] [since] [until]
php index.php Cron/Platform fetch all 2023-01-01 2023-12-31
php index.php Cron/Platform sync facebook 2023-10-01 2023-10-31
```

**2. Setup Crontab Server (Otomatis / Native):**
Jika Anda **tidak menggunakan Docker** dan ingin proses ini berjalan otomatis di background server (Linux), tambahkan konfigurasi berikut ke crontab Anda (`crontab -e`):
```bash
# Tarik postingan semua platform setiap jam
# Log disimpan ke logs/cron_fetch.log
0 * * * * cd /path/to/project && php index.php Cron/Platform fetch all >> logs/cron_fetch.log 2>&1

# Tarik insights semua platform setiap tengah malam (00:00)
# Log disimpan ke logs/cron_sync.log
0 0 * * * cd /path/to/project && php index.php Cron/Platform sync all >> logs/cron_sync.log 2>&1
```
*(Sesuaikan `/path/to/project` dengan lokasi direktori project Anda).*

**3. Mengubah Jadwal Cron (Khusus Pengguna Docker):**
Jika Anda menjalankan via Docker, waktu penjadwalan dikonfigurasi melalui file `docker/cron/crontab`. 
Untuk mengubah waktu penjadwalannya:
1. Buka dan edit file `docker/cron/crontab`.
2. Build ulang dan restart container cron untuk menerapkan perubahan:
   ```bash
   docker compose build cron
   docker compose up -d cron
   ```

**Log Sistem:**
- **Docker:** Log cron job otomatis ter-redirect ke Docker. Gunakan perintah:
  `docker logs -f ads_aggregator_cron_prod`
- **Native:** Output cron akan tersimpan ke file log lokal:
  - `logs/cron_fetch.log` (Output fetch)
  - `logs/cron_sync.log` (Output sync)
