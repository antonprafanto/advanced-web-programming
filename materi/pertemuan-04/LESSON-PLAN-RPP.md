# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 04: Deep Dive Eloquent ORM: Complex Relationships & N+1 Optimization
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan & Review Evaluasi Tugas 03 (Migration & Seeder Batch)
[015 - 055'] Sesi Teori: Relasi Polimorfik, Bencana N+1 Problem, & Query Scopes
[055 - 065'] Istirahat Singkat / Diskusi Interaktif
[065 - 095'] Live Demo Dosen: Profiling N+1 (101 vs 3 Query) & PreventLazyLoading
[095 - 135'] Hands-on Lab Mahasiswa: Polimorfik Komentar, Eager Loading, & Scopes
[135 - 150'] Evaluasi Hasil Praktikum, Penjelasan Tugas 04, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Review Tugas 03 (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa dan memeriksa kehadiran.
  - Membahas evaluasi Tugas 03 (optimasi batch seeder dan rollback transaksi ACID).
  - **Pertanyaan Pemantik:** *"Siapa yang pernah membuat web dan ketika datanya bertambah banyak, loading halamannya mendadak lambat dari 1 detik menjadi 5 detik? Tahukah kalian bahwa penyebab 90% kelambatan tersebut adalah N+1 Query Problem?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Menjelaskan konsep **Polymorphic Relations**: mengapa tabel komentar tidak perlu dibuat dobel (`post_comments`, `video_comments`), melainkan cukup satu tabel dengan kolom `morphs()`.
  - Menjelaskan bahaya refactoring class dan pentingnya **Strict Morph Map** (`Relation::enforceMorphMap`).
  - Membedah mekanisme terjadinya **N+1 Query Problem** dan cara mengatasinya via **Eager Loading (`with()`)**.
  - Mengulas teknik agregasi efisien: **`withCount()`** alih-alih me-load relasi ke RAM.
  - Menjelaskan perbedaan **Local Scopes** (reusable query filter) dan **Global Scopes**.

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Menjalankan uji perbandingan langsung:
    1. Menunjukkan skrip [01-n-plus-one-disaster.php](studi-kasus/01-n-plus-one-disaster.php).
    2. Menjalankan demonstrasi benchmark [02-eager-loading-optimized.php](studi-kasus/02-eager-loading-optimized.php) di terminal untuk membuktikan reduksi dari 101 query menjadi 3 query.
    3. Mengaktifkan `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider.php` dan mendemonstrasikan bagaimana Laravel otomatis melempar layar exception saat N+1 terjadi.
- **Aktivitas Mahasiswa:**
  - Mengamati log kueri database dan memahami cara mengaudit query SQL.

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Mengaktifkan `Model::preventLazyLoading()` di project lokal masing-masing.
  - Membuat tabel `comments` polimorfik yang terhubung ke `Course`.
  - Menerapkan local scopes `scopePublished()` pada model.
  - Membuat route benchmarking di web dan mengamati jumlah query yang dieksekusi via `DB::getQueryLog()`.
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Berkeliling membantu mahasiswa mengidentifikasi bagian kode yang masih memicu lazy loading.

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Mereview hasil query log mahasiswa.
  - Memaparkan ketentuan [TUGAS-04.md](TUGAS-04.md).
  - Memberi gambaran singkat materi pertemuan 5: *Enterprise Architecture: Service Layer, DTO & Dependency Injection*.
