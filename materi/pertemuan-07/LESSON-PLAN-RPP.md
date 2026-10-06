# RENCANA PELAKSANAAN PEMBELAJARAN (RPP) / LESSON PLAN
## PERTEMUAN 07: File Management, Media Handling & Cloud Storage Abstraction
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. IDENTITAS MATA KULIAH
- **Program Studi:** S1 Informatika / Teknik Informatika
- **Mata Kuliah:** Pemrograman Web Lanjut (Advanced Web Programming)
- **Bobot SKS:** 3 SKS (150 Menit Tatap Muka / Praktikum Lab)
- **Pertemuan ke-:** 07 (Pertemuan Terakhir Sebelum UTS)
- **Model Pembelajaran:** *Contextual Teaching and Learning (CTL)* & *Lab Hands-on Coding*

---

### II. CAPAIAN PEMBELAJARAN (Sub-CPMK)
Mahasiswa mampu:
1. Menjelaskan cara kerja arsitektur *Filesystem Abstraction* (Flysystem) pada Laravel 11/12.
2. Membedakan secara tegas mekanisme penyimpanan dokumen publik vs berkas privat rahasia.
3. Mengamankan fitur *file upload* dari celah *Remote Code Execution* (RCE) dan *Path Traversal*.
4. Mengimplementasikan tautan unduhan berbatas waktu (*Temporary Signed URLs*) dengan proteksi otorisasi.
5. Melakukan optimasi dan manipulasi berkas gambar (resizing dan konversi WebP) secara dinamis.
6. Mematangkan kesiapan arsitektur kode menjelang evaluasi Ujian Tengah Semester (UTS Milestone 1).

---

### III. RUNDOWN PEMBELAJARAN (150 MENIT)

| Sesi / Durasi | Aktivitas Dosen & Asisten | Aktivitas Mahasiswa | Output & Bukti Belajar |
| :--- | :--- | :--- | :--- |
| **Fase 1: Apersepsi & Security Alert**<br>*(20 Menit)* | • Dosen mendemonstrasikan serangan nyata: Mengunggah file `shell.php` via skrip upload native dan mengambil alih terminal web server (RCE).<br>• Menjelaskan kenapa `move_uploaded_file` dan penyimpanan mentah di folder publik adalah bencana keamanan. | Mahasiswa menganalisis kode rentan di [01-insecure-direct-upload.php](studi-kasus/01-insecure-direct-upload.php) dan berdiskusi interaktif mengenai kebocoran privasi dokumen KTP. | Mahasiswa memahami urgensi abstraksi storage dan isolasi file privat. |
| **Fase 2: Live Coding Multi-Disk & Validation**<br>*(40 Menit)* | • Dosen memandu konfigurasi `config/filesystems.php` (disk `local`, `public`, `s3`).<br>• Live coding pembuatan Form Request menggunakan *fluent validation builder* `File::types(['pdf'])->max('5mb')`.<br>• Mempraktikkan perintah `php artisan storage:link` dan menjelaskan cara kerja *symlink*. | Mahasiswa mengikuti konfigurasi multi-disk di repositori lab masing-masing dan menguji validasi file berukuran besar. | Berhasil membuat Form Request validasi MIME-type dan membuat tautan symlink publik. |
| **Fase 3: Lab Hands-on Signed URL & WebP**<br>*(40 Menit)* | • Asdos mendemonstrasikan pembuatan *Temporary Signed Route* (`URL::temporarySignedRoute()`) dengan masa berlaku 30 menit.<br>• Memandu pembuatan Controller `streamDownload()` yang memeriksa Policy kepemilikan dan mengalirkan file dengan `Storage::download()`.<br>• Demonstrasi manipulasi gambar dengan Intervention Image: auto-crop dan konversi WebP. | Mahasiswa mengimplementasikan controller unduhan privat, mencoba memanipulasi parameter query URL di browser untuk membuktikan respons **HTTP 403 Invalid Signature**. | Terverifikasi fitur download privat aman dan file avatar tersimpan dalam ekstensi `.webp`. |
| **Fase 4: Praktik Mandiri & Asistensi UTS**<br>*(30 Menit)* | • Dosen dan Asdos berkeliling memberikan asistensi individu/kelompok terkait progres tugas [TUGAS-07.md](TUGAS-07.md).<br>• Asistensi kesiapan repositori untuk review Milestone 1 UTS Minggu ke-8 (Clean Architecture, RBAC, Database Seeding). | Mahasiswa mengerjakan modul dokumen identitas dan berkonsultasi mengenai arsitektur proyek UTS mereka. | Kode tugas tersusun rapi di branch `feat/pertemuan-07` dan checklist UTS terkonfirmasi. |
| **Fase 5: Evaluasi & Penutup**<br>*(20 Menit)* | • Refleksi bersama: Manfaat abstraksi multi-disk saat migrasi ke AWS S3 / Supabase Storage tanpa ubah kode.<br>• Penjelasan teknis mekanisme penilaian Ujian Tengah Semester (UTS) minggu depan. | Mahasiswa mencatat poin evaluasi dan mempersiapkan diri menghadapi *Code Review* UTS. | Mahasiswa siap 100% menghadapi evaluasi UTS Milestone 1. |

---

### IV. PERANGKAT & SUMBER BELAJAR
- **Source Code Modul:** [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md)
- **Komparasi Kode:** Direktori [studi-kasus/](studi-kasus/README.md)
- **Lembar Tugas:** [TUGAS-07.md](TUGAS-07.md)
- **Pedoman Penilaian:** [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md)
- **Panduan UTS:** [Kontrak Kuliah Bab III: Milestone Proyek UTS](../../KONTRAK-KULIAH-DAN-PANDUAN-PROYEK.md#iii-panduan-proyek-tengah-semester-uts--milestone-1)
