# PulseGuard — Self-Hosted HTTP & Uptime Monitor

**PulseGuard** adalah aplikasi pemantau ketersediaan (*uptime*) dan performa latensi layanan web maupun REST API berbasis **Laravel**, **Vue 3**, dan **SQLite**. Dirancang sebagai alternatif *self-hosted* dari layanan SaaS berbayar (seperti UptimeRobot atau Pingdom) untuk memantau aset digital setiap 1 menit tanpa biaya langganan bulanan.

---

## Pratinjau Aplikasi

### 1. Halaman Status Publik (`/`)
Halaman *read-only* tanpa login yang menampilkan status layanan secara *real-time* beserta visualisasi 60 riwayat ping terakhir.
![Halaman Status Publik](docs/screenshots/public-status.png)

### 2. Dasbor Kendali Admin (`/admin`)
Panel manajemen terproteksi passcode dengan status *Task Scheduler* 1 menit, telemetry kapasitas SQLite, filter status, dan aksi ping manual.
![Dasbor Admin](docs/screenshots/admin-dashboard.png)

### 3. Riwayat Log & Diagnostik Error
Inspeksi detail setiap pengecekan hingga 100 riwayat terakhir, lengkap dengan kode status HTTP, latensi milidetik (`ms`), dan pesan error jaringan.
![Riwayat Log Ping](docs/screenshots/ping-logs.png)

---

## Fitur Utama

* **Pemantauan Multi-Metode (`GET`, `HEAD`, `POST`):** Mendukung pengecekan halaman web publik maupun *endpoint* API/webhook dengan *header* menyerupai browser untuk meminimalkan pemblokiran WAF standar.
* **Deteksi Error 4 Lapis:**
  1. *Connection Timeout / Hang* (`cURL error 28`) dengan batas koneksi 5 detik dan total tunggu 10 detik.
  2. *DNS & Network Unreachable* (domain tidak aktif atau gagal resolusi).
  3. *HTTP Client & Server Errors* (`4xx` dan `5xx`).
  4. *Latency Degradation* otomatis menandai layanan sebagai **Slow** jika waktu respons melampaui `2.000 ms`.
* **Visualisasi Heartbeat 60 Ping:** Menampilkan riwayat 60 pengecekan terakhir dalam bentuk grafik batang interaktif (*hover* untuk melihat waktu, status, dan latensi).
* **Pemisahan Akses Publik & Admin:** Halaman status publik (`/`) dilindungi *rate limiter*, sedangkan seluruh aksi tulis/hapus di `/admin` dikunci menggunakan `ADMIN_PASSCODE` melalui *header* `X-Admin-Key`.
* **Auto-Pruning & Telemetry SQLite:** Otomatis menghapus log ping yang berusia lebih dari 14 hari setiap hari agar ukuran database SQLite tetap ringan dan terpantau langsung dari dasbor.

---

## Batasan Sistem (*Engineering Scope*)

| Aspek | Cakupan PulseGuard | Di Luar Cakupan (Non-Goals) |
| :--- | :--- | :--- |
| **Metode Pengecekan** | *Black-box* HTTP/HTTPS request (`GET`, `HEAD`, `POST`) | Eksekusi JavaScript (*headless browser* / Playwright) |
| **Metrik Server** | Kode status HTTP, latensi jaringan (`ms`), & pesan error cURL | Penggunaan CPU, RAM, atau disk internal server target |
| **Penyimpanan** | SQLite lokal dengan retensi otomatis 14 hari | *Time-series database* terdistribusi skala besar |

---

## Tech Stack

* **Backend:** PHP 8.4+, Laravel Framework, Laravel Task Scheduler
* **Frontend:** Vue 3 (Composition API), Tailwind CSS, Vite, Lucide Icons
* **Database:** SQLite

---

## Instalasi & Menjalankan di Lokal

1. **Clone repositori dan masuk ke folder proyek:**
   ```bash
   git clone [https://github.com/USERNAME_KAMU/pulseguard.git](https://github.com/dbbyaulia-arch/pulseguard)
   cd pulseguard