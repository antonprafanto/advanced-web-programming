# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 03: Database Engineering: Schema Migration, Seeding & Model Factory
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan & Review Evaluasi Tugas 02 (Form Request & Middleware)
[015 - 055'] Sesi Teori: Advanced Migration, Indexing, Factory States, & Jaminan ACID
[055 - 065'] Istirahat Singkat / Tanya-Jawab Interaktif
[065 - 095'] Live Demo Dosen: Uji Komparasi Seeding Naive vs High-Speed Batch Chunk
[095 - 135'] Hands-on Lab Mahasiswa: Skema Berelasi, Factory Bertingkat, & DB::transaction
[135 - 150'] Evaluasi Hasil Praktikum, Penjelasan Tugas 03, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Review Tugas 02 (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa dan mengecek presensi.
  - Membahas kesalahan umum di Tugas 02 (misal: lupa header `Accept: application/json` di Postman atau penulisan sanitasi di controller alih-alih `prepareForValidation`).
  - **Pertanyaan Pemantik:** *"Berapa lama seeder kalian berjalan jika diminta menggenerate 10.000 data transaksi? Mengapa seeder sering membuat laptop lab hang?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Menjelaskan pentingnya **Foreign Key Cascades** dan bahaya *Orphan Records*.
  - Menjelaskan perbedaan Single Index vs **Composite Index** pada query pencarian multi-kolom (`where category_id and status`).
  - Membedah fitur **Model Factory**: State transformations (`->free()`, `->paid()`), Sequence, dan konfigurasi Faker ke lokalisasi Indonesia (`id_ID`).
  - Menjelaskan prinsip **ACID** dan peran `DB::transaction()` untuk mencegah bencana integritas data keuangan/stok.

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Menjalankan uji perbandingan langsung:
    1. Menunjukkan lambatnya seeder naif [01-naive-seeder-slow.php](studi-kasus/01-naive-seeder-slow.php) yang memakan waktu lama.
    2. Menjalankan skrip batch chunk [02-optimized-factory-chunk.php](studi-kasus/02-optimized-factory-chunk.php) yang menuntaskan 5.000 data dalam hitungan milidetik.
    3. Mendemonstrasikan rollback otomatis saat terjadi simulasi error di dalam `DB::transaction()`.
- **Aktivitas Mahasiswa:**
  - Mengamati perbedaan kecepatan dan mencatat teknik optimasi query batching.

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Membuat migration skema `categories`, `courses`, dan `enrollments` lengkap dengan composite unique dan composite index.
  - Membuat `CourseFactory` dengan method state `free()` dan `draft()`.
  - Mengisi database dengan minimal 50 kursus terhubung ke kategori.
  - Memverifikasi data via `php artisan tinker`.
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Berkeliling membantu mahasiswa yang mengalami kendala foreign key constraint saat rollback atau drop table.

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Mereview hasil eksekusi migration dan factory mahasiswa.
  - Menjelaskan rincian soal [TUGAS-03.md](TUGAS-03.md).
  - Memberi gambaran awal materi pertemuan 4: *Deep Dive Eloquent ORM: Complex Relationships & N+1 Optimization*.
