<p align="center">
  <img src="public/images/logo_baru.jpeg" alt="OurClass Logo" width="180">
</p>

# OurClass 
## Platform Manajemen Kelas Digital

OurClass adalah aplikasi web kelas digital interaktif yang dirancang untuk membantu siswa/mahasiswa dan guru/dosen dalam mengelola administrasi kelas, melacak tugas, mengelola jadwal, serta menyediakan laporan otomatis secara efisien dan terintegrasi.

Aplikasi ini sudah dideploy secara live dan dapat diakses di:
 **[ourclass-production.up.railway.app](https://ourclass-production.up.railway.app)**

---

## 1. Fitur Utama

1) **Autentikasi & Profil (Role-Based)**: Sistem pendaftaran dan masuk (Login/Register) terpisah untuk peran **Guru/Dosen (Pengajar)** dan **Siswa/Mahasiswa (Pelajar)**.
2) **Dashboard Interaktif**: Halaman utama yang menampilkan ringkasan kelas, jadwal terdekat, tugas aktif, dan notifikasi terbaru.
3) **Manajemen Kelas**:
   - Guru/Dosen dapat membuat kelas dengan visibilitas publik/privat.
   - Siswa/Mahasiswa dapat bergabung dengan memasukkan kode unik kelas.
4) **Manajemen Tugas & File Soal**: 
   - Guru/Dosen dapat membuat tugas dengan lampiran file soal dan tenggat waktu (*deadline*).
   - Siswa/Mahasiswa dapat melihat daftar tugas dan mengumpulkan (submit) jawaban secara langsung.
5) **Manajemen Agenda**: Menampilkan jadwal kuliah, pertemuan kelas, dan batas waktu tugas dalam format terpusat agar tidak ada yang terlewat.
6) **Manajemen Penyimpanan & Keamanan**: Terdapat pembatasan ukuran unggahan file (maks 10MB), validasi format (.pdf, .docx, dll), enkripsi Bcrypt, dan proteksi CSRF demi efisiensi *server* dan perlindungan data.
7) **Ruang Diskusi & Notifikasi**: Pusat komunikasi di dalam kelas serta pengingat pembaruan agar informasi penting tidak tenggelam.
8) **Kustomisasi Tema & UI Responsif**: Mendukung *Dark Mode* dan *Light Mode*, serta tampilan *Mobile-Ready* yang dioptimalkan untuk layar ponsel maupun laptop.
9) **Dukungan Multi-Bahasa (Opsional)**: Tersedia antarmuka dalam pilihan Bahasa Indonesia dan Bahasa Inggris.


---

## 2. Teknologi yang Digunakan (Tech Stack)

1) **Backend**: PHP 8.4 (Framework Laravel 11)
2) **Frontend**: Blade Templating Engine, HTML5, Vanilla CSS (Custom Design System, Glassmorphism), JavaScript
3) **Database**: MySQL 8
4) **Pustaka Ikon**: Lucide Icons
5) **Deployment Platform**: Railway

---

## 3. Struktur Database Utama

1) **`users`**: Menyimpan data akun pengguna (nama, email, password terenkripsi Bcrypt, peran/role, nomor WhatsApp, NIM/NIP).
2) **`classes`**: Menyimpan data kelas yang dibuat oleh Dosen (nama kelas, deskripsi, kode kelas unik, ID pembuat).
3) **`assignments`**: Menyimpan data tugas yang ditambahkan di setiap kelas (judul tugas, deskripsi, batas waktu/due date).
4) **`class_user`**: Tabel pivot relasi many-to-many untuk mencatat mahasiswa yang bergabung ke dalam kelas.

---

## 4. Pengembangan Lokal (Local Development)

Jika ingin menjalankan proyek ini secara lokal menggunakan Laragon atau PHP Development Server:

1) **Clone Repositori**:
   ```bash
   git clone https://github.com/NadNad-z/OurClass.git
   cd OurClass
   ```

2) **Instal Dependensi**:
   ```bash
   composer install
   ```

3) **Atur Environment**:
   Salin `.env.example` menjadi `.env` lalu sesuaikan kredensial databasemu:
   ```bash
   cp .env.example .env
   ```

4) **Generate Key & Migrasi Database**:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   ```

5) **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```
   Buka `http://127.0.0.1:8000` di browsermu.

---
*Proyek ini diajukan untuk memenuhi Ujian Akhir Semester (UAS) mata kuliah Pemrograman Web 2.*
