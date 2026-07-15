# Product Requirement Document (PRD) - OurClass

**Proyek**: OurClass (Final Project UAS)
**Versi Dokumen**: 1.0
**Tanggal**: 15 Juli 2026

---

## 1. Pendahuluan

### 1.1 Latar Belakang
Dalam proses pembelajaran modern, baik dosen maupun mahasiswa membutuhkan platform yang terpusat untuk mengelola jadwal, mendistribusikan tugas, serta melakukan komunikasi akademik. Seringkali, informasi tersebut tersebar di berbagai platform chat dan email yang mengakibatkan miskomunikasi, keterlambatan pengumpulan tugas, dan sulitnya pemantauan produktivitas.

### 1.2 Visi Produk
OurClass hadir sebagai **"Digital Classroom Companion"** yang interaktif, cepat, dan mudah digunakan. Visi aplikasi ini adalah memusatkan seluruh administrasi akademik dan interaksi kelas dalam satu tempat yang modern, sekaligus menyediakan wawasan (analytics) terkait beban belajar bagi mahasiswa dan dosen.

### 1.3 Target Pengguna
1. **Dosen / Pengajar**: Membutuhkan alat untuk membuat kelas, memberikan dan menilai tugas, serta mengekspor rekap nilai dengan cepat.
2. **Mahasiswa**: Membutuhkan pengingat (reminder) jadwal, akses mudah ke materi/tugas, dan informasi langsung mengenai nilai serta beban tugas mingguan mereka.

---

## 2. Fitur Utama & Functional Requirements

Berikut adalah daftar kebutuhan fungsional (Functional Requirements) untuk OurClass:

| ID | Fitur | Deskripsi | Aktor | Status |
|---|---|---|---|---|
| **FR-01** | Autentikasi | Pengguna dapat melakukan registrasi, login, dan logout dengan aman (Role: Dosen & Mahasiswa). | Semua | Selesai |
| **FR-02** | Manajemen Kelas | Pembuatan kelas baru (generate kode unik), edit informasi kelas, dan menghapus kelas. | Dosen | Selesai |
| **FR-03** | Gabung Kelas | Memasukkan kode unik untuk bergabung ke dalam kelas tertentu. | Mahasiswa | Selesai |
| **FR-04** | Manajemen Tugas | Membuat, mengedit, dan menghapus tugas (termasuk upload soal dan pengaturan deadline). | Dosen | Selesai |
| **FR-05** | Pengumpulan Tugas | Mengunggah file jawaban sebelum deadline dan melihat status pengumpulan (terlambat/tepat waktu). | Mahasiswa | Selesai |
| **FR-06** | Penilaian (Grading) | Memberikan skor nilai dan *feedback* atas tugas yang dikumpulkan mahasiswa. | Dosen | Selesai |
| **FR-07** | Forum Diskusi | Membuat thread diskusi kelas dan memberikan komentar/balasan. | Semua | Selesai |
| **FR-08** | Analytics & Laporan | Menampilkan grafik statistik tugas mingguan dan mengekspor rekap nilai (PDF/Excel). | Dosen | Selesai |
| **FR-09** | Jadwal Kelas | Melihat jadwal tatap muka/sinkron setiap kelas. | Semua | Selesai |

---

## 3. Non-Functional Requirements (NFR)

1. **Performance**: Bebas dari *N+1 Query Problem*. Halaman dengan data kompleks (seperti dashboard dan analytics) harus dimuat dalam waktu kurang dari 2 detik.
2. **Security**: 
   - Dilengkapi proteksi CSRF di seluruh form.
   - Hak akses rute divalidasi dengan ketat menggunakan *Middleware* atau *Policies* (misal: mahasiswa tidak bisa mengedit kelas).
   - Validasi file unggahan (maksimal ukuran dan ekstensi tertentu) serta terintegrasi pengecekan ClamAV (opsional).
3. **Usability**: Tampilan antarmuka yang responsif (Mobile-first) menggunakan CSS modern, dilengkapi fitur *Dark Mode* dan kustomisasi warna aksen kelas.
4. **Reliability**: Semua tindakan kritis (buat/edit/hapus) harus terekam dalam *Activity Log* untuk tujuan audit.

---

## 4. Entity Relationship Diagram (Desain Database)

Berikut adalah struktur entitas relasi dasar dari aplikasi OurClass:

- **Users**: Menyimpan kredensial (`id`, `name`, `email`, `role`, `nim_nip`).
- **ClassModels (classes)**: Menyimpan detail kelas (`id`, `nama_kelas`, `kode_unik`, `admin_id`, `color`).
  - *Relasi*: 1 kelas dimiliki 1 Dosen (Admin), tapi bisa berelasi *Many-to-Many* dengan Mahasiswa lewat tabel pivot `class_user`.
- **Tasks**: Menyimpan informasi penugasan (`id`, `class_id`, `judul`, `deadline`, `nilai_max`).
  - *Relasi*: Dimiliki oleh 1 kelas (BelongsTo ClassModel).
- **Submissions**: Menyimpan jawaban mahasiswa (`id`, `task_id`, `user_id`, `file`, `nilai`, `status`).
  - *Relasi*: Menghubungkan User (Mahasiswa) dengan Task.
- **Discussions / Replies**: Forum diskusi di masing-masing kelas/tugas.
- **ActivityLogs**: Tabel log riwayat aktivitas pengguna.

---

## 5. UI/UX Guidelines

- **Typography**: Menggunakan font modern Sans-Serif (misal: Inter atau Roboto) untuk keterbacaan yang maksimal di layar kecil.
- **Komponen**: Penggunaan gaya *glassmorphism* atau gradien halus (*soft gradients*) untuk *Banner Kelas* agar terkesan premium.
- **Aksesibilitas**: Kontras teks dan background harus mematuhi standar. Tombol aksi (seperti Edit/Hapus) diisolasi dengan aman lewat peringatan (Alert Confirm).
- **Feedback**: Menggunakan notifikasi Toast/Alert (Success/Error) seketika setelah pengguna melakukan tindakan (contoh: "Tugas berhasil diedit").

---
*Dokumen ini digunakan sebagai lampiran penilaian Ujian Akhir Semester (UAS).*
