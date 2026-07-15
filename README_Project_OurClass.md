# OurClass — Final Project

## 1. Judul & Deskripsi

- **Judul**: OurClass
- **Deskripsi**: Aplikasi kelas digital berbasis web untuk mahasiswa dan dosen/guru yang menyediakan manajemen kelas, jadwal, tugas, notifikasi, analisis, dan laporan otomatis untuk mendukung proses pembelajaran dan administrasi akademik.

## 2. Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP
- **Framework**: Laravel
- **Database**: MySQL / MariaDB
- **Front-End**: HTML, CSS, JavaScript (Blade templates)
- **Library Tambahan**: PhpSpreadsheet, TCPDF, Chart.js
- **Integrasi Eksternal**: Google Calendar API, Microsoft Graph API (Outlook)
- **Development Lokal**: Laragon
- **Hosting**: Shared Hosting atau Cloud (AWS, Google Cloud)

## 3. Fitur Utama

1. **Login & Registrasi** (Mahasiswa, Dosen, Admin)
2. **Dashboard Ringkas** (jadwal, tugas, notifikasi)
3. **Manajemen Kelas** (kode unik, multi-admin, edit info kelas)
4. **Jadwal** (sinkron ke Google Calendar/Outlook)
5. **Tugas** (upload soal, submit jawaban, status pengerjaan)
6. **Diskusi Kelas** (forum tanya jawab)
7. **Notifikasi** (deadline & update tugas/jadwal)
8. **Analisis Beban Belajar** (grafik tugas mingguan, statistik produktivitas)
9. **Kustomisasi Tema** (dark/light mode, warna per kelas)
10. **Export Laporan** (Excel / PDF)
11. **Keamanan & Audit** (role-based access, backup cloud, riwayat perubahan data)

## 4. Struktur Database (Contoh Tabel)

- **users**: `id`, `nama`, `email`, `password`, `role`
- **classes**: `id`, `nama_kelas`, `kode_unik`, `admin_id`
- **tasks**: `id`, `judul`, `deskripsi`, `deadline`, `class_id`, `status`
- **submissions**: `id`, `task_id`, `user_id`, `file_path`, `nilai`
- **schedules**: `id`, `class_id`, `mata_kuliah`, `dosen`, `waktu`
- **reports**: `id`, `class_id`, `file_path`, `created_at`

## 5. Alur Penggunaan (Workflow)

1. **Splash Screen**: Menampilkan logo OurClass singkat saat aplikasi dibuka.
2. **Login/Registrasi**: Pengguna masuk sesuai peran (mahasiswa/dosen/admin).
3. **Dashboard**: Menampilkan ringkasan jadwal, tugas, notifikasi.
4. **Manajemen Kelas**: Dosen membuat kelas; mahasiswa bergabung menggunakan kode unik.
5. **Tugas & Jadwal**: Dosen menambah tugas; mahasiswa mengumpulkan; sistem memberi notifikasi.
6. **Analisis & Laporan**: Dosen mengekspor nilai dan laporan tugas ke Excel/PDF.
7. **Tema & Pengaturan**: Pengguna mengubah warna dan mode tampilan.

## 6. Tampilan Awal (Splash Screen)

- **Durasi**: Logo muncul selama ±2 detik sebelum menuju halaman login.
- **Desain**: Background putih atau hijau lembut, logo OurClass di tengah.
- **Navigasi**: Setelah splash selesai, langsung diarahkan ke halaman login.

## 7. Docker Deployment

A Docker setup is included to run the app locally with PHP-FPM, Nginx, and MySQL.

### Build and start

```bash
docker compose up --build -d
```

### Environment

Use your existing `.env`, and set database values to point to the Docker MySQL service:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ourclass
DB_USERNAME=user
DB_PASSWORD=password
```

### Database setup

```bash
docker compose exec app php artisan migrate --seed
```

### Access the app

Open `http://localhost:8000` in your browser.

## 8. Backup & Restore

Aplikasi menyediakan perintah backup dan restore untuk menyimpan data penting.

### Backup lokal

```bash
php artisan project:backup --include-public --disk=local
```

### Backup ke S3

Pastikan `.env` berisi konfigurasi AWS berikut:

```env
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
FILESYSTEM_DISK=s3
```

Lalu jalankan:

```bash
php artisan project:backup --include-public --disk=s3
```

### Restore backup

Untuk restore file ZIP dari penyimpanan lokal:

```bash
php artisan project:restore backup_20260703_120000.zip --disk=local
```

Untuk restore file ZIP dari S3:

```bash
php artisan project:restore backup_20260703_120000.zip --disk=s3
```

Restore akan mengekstrak konten ke folder `storage/app/backups/restore_<timestamp>/` sehingga Anda dapat memeriksa file JSON dan memulihkan data secara manual.

---

Jika kamu mau, saya bisa:

- Membuat skeleton fitur (route, controller, migration) untuk fitur prioritas.
- Meninjau dependensi dan konfigurasi di repo.
- Menambahkan halaman splash dan layout Blade dasar.

Beritahu pilihanmu, saya lanjutkan langkah berikutnya.
