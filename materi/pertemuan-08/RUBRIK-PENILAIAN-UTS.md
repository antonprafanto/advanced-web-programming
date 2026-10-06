# RUBRIK PENILAIAN UJIAN TENGAH SEMESTER (UTS)
## Midterm Project Defense & Code Review (Milestone 1)
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN BAGI DOSEN PENGUJI
- Dokumen ini adalah acuan resmi penilaian evaluasi Ujian Tengah Semester (UTS) berbasis *Outcome-Based Education* (OBE) untuk mengukur ketercapaian **CPMK 1** dan **CPMK 2**.
- Nilai Akhir UTS dihitung dengan skala **0 s/d 100**.
- Penilaian terdiri atas dua komponen utama: **Kualitas Kode Repositori (Kelompok)** dan **Penguasaan Individu (*Oral Defense & Live Challenge*)**.

---

### II. MATRIKS DETAIL PENILAIAN (TOTAL: 100 POIN)

#### 1. Clean Architecture & SOLID Principles (Bobot: 25 Poin)
| Kriteria Penilaian | Skor | Indikator Ketercapaian |
| :--- | :---: | :--- |
| **Skinny Controller** | 10 Poin | Controller sangat ramping ($\le 25$ baris per method), hanya bertindak sebagai orkestrator HTTP. Tidak ada query SQL mentah atau kalkulasi bisnis berserakan. |
| **Form Request Validation** | 5 Poin | Validasi input terpusat di kelas Form Request mandiri dengan rules yang ketat dan pesan error yang deskriptif. |
| **Service Layer & DTO** | 10 Poin | Logika transaksi utama diisolasi di kelas Service yang murni independen dari HTTP (`$request`/`response()`). Input kompleks dikemas dalam DTO bertipe tegas (`readonly class`). |

---

#### 2. Database Engineering & ACID Transactions (Bobot: 20 Poin)
| Kriteria Penilaian | Skor | Indikator Ketercapaian |
| :--- | :---: | :--- |
| **Skema Migrasi & Indexing** | 5 Poin | Skema tabel ternormalisasi, foreign key constraints terpasang lengkap, dan terdapat penambahan indeks eksplisit pada kolom filter pencarian. |
| **Factory & High-Speed Seeding** | 5 Poin | Menggunakan Model Factory dan Seeder dengan teknik batch/chunk yang mampu mengisi $\ge 500$ record dalam waktu $< 5$ detik. |
| **ACID & Pessimistic Locking** | 10 Poin | Alur transaksi kritis (saldo/kuota) dibungkus dalam `DB::transaction()` dan memproteksi *race condition* menggunakan `lockForUpdate()`. |

---

#### 3. Eloquent ORM & Query Performance (Bobot: 20 Poin)
| Kriteria Penilaian | Skor | Indikator Ketercapaian |
| :--- | :---: | :--- |
| **Relasi Eloquent Kompleks** | 10 Poin | Mengimplementasikan minimal satu relasi lanjutan (*Polymorphic Relations*, *Custom Pivot Model with Timestamps*, atau *Has-Many-Through*). |
| **Eliminasi N+1 Query Problem** | 10 Poin | Terbukti bebas dari masalah query N+1 pada Laravel Debugbar (jumlah query konsisten dan tidak berlipat ganda sesuai jumlah data baris). |

---

#### 4. Keamanan, RBAC & Storage Abstraction (Bobot: 20 Poin)
| Kriteria Penilaian | Skor | Indikator Ketercapaian |
| :--- | :---: | :--- |
| **Spatie RBAC & Model Policies** | 10 Poin | Sistem memiliki minimal 2 level peran aktor (`spatie/laravel-permission`). Hak kepemilikan data diproteksi ketat via *Model Policy* (User B ditolak dengan HTTP 403 saat mengedit data User A). *Super Admin Bypass hook* terpasang via `Gate::before()`. |
| **Multi-Disk & Temporary Signed URLs** | 10 Poin | Pemisahan tegas media publik (avatar WebP) dan dokumen privat (KTP/Ijazah di `storage/app/private`). Dokumen privat hanya bisa diunduh via *Temporary Signed URL* bertanda tangan digital. |

---

#### 5. Kualitas Git, Dokumentasi & Live Defense (Bobot: 15 Poin)
| Kriteria Penilaian | Skor | Indikator Ketercapaian |
| :--- | :---: | :--- |
| **Git Release & README Repositori** | 5 Poin | Memiliki Git Tag resmi `v0.5.0-uts`, riwayat commit menggunakan format *Conventional Commits*, dan file `README.md` memuat panduan instalasi lokal yang dapat direplikasi. |
| **Oral Defense & Live Challenge** | 10 Poin | Mahasiswa mampu menjawab pertanyaan konseptual dosen dengan lancar dan mampu melakukan perubahan kode kecil (*live modification challenge*) secara percaya diri. |

---

### III. MATRIKS SANKSI DAN PENALTI

| Jenis Pelanggaran | Pengurangan Nilai / Konsekuensi |
| :--- | :---: |
| Ditemukan masalah N+1 parah ($> 25$ query SQL identik untuk 1 halaman) | -15 Poin |
| Dokumen rahasia (KTP/Ijazah) disimpan di direktori publik `public/` | -25 Poin (Pelanggaran Keamanan Fatal) |
| Controller gemuk (*Fat Controller* $> 80$ baris per method) | -15 Poin |
| Riwayat Git commit pasif / hanya 1 commit instan (*free-rider*) | Nilai Individu diturunkan menjadi **Maksimal 40 (E)** |
| Mahasiswa tidak memahami kode saat ditanya (*AI-generated tanpa paham*) | Nilai Individu dipotong **30 - 50 Poin** |
| Plagiarisme antar-kelompok (kode identik) | **NILAI 0 (OTOMATIS GUGUR KEDUA TIM)** |

---

### IV. STANDAR KONVERSI NILAI AKADEMIK

| Rentang Nilai Angka | Nilai Huruf | Bobot | Kualifikasi Kompetensi |
| :---: | :---: | :---: | :--- |
| **85.00 – 100.00** | **A** | 4.00 | Sangat Istimewa (Arsitektur solid, pemahaman industri, live defense sempurna) |
| **80.00 – 84.99** | **AB** | 3.50 | Sangat Baik (Semua fitur berjalan, arsitektur rapi, defense lancar) |
| **70.00 – 79.99** | **B** | 3.00 | Baik (Fitur utama jalan, ada sedikit catatan minor pada N+1 atau locking) |
| **65.00 – 69.99** | **BC** | 2.50 | Cukup Baik (Arsitektur bercampur antara native dan modern) |
| **60.00 – 64.99** | **C** | 2.00 | Cukup (Banyak catatan clean code dan otorisasi masih rapuh) |
| **50.00 – 59.99** | **D** | 1.00 | Kurang (Tidak memenuhi standar arsitektur minimal, free-rider parsial) |
| **< 50.00** | **E** | 0.00 | Gagal / Plagiarisme / Tidak hadir ujian |

---

### V. LEMBAR REKAP SKOR DOSEN (SCORING SHEET TEMPLATE)

```markdown
Nama Kelompok / Proyek : _________________________________________
Judul Aplikasi         : _________________________________________
URL Repositori GitHub  : _________________________________________
Git Tag Release        : [ ] v0.5.0-uts Terverifikasi

┌───┬───────────────────────────────┬────────────┬─────────────┬──────────────────────────┐
│No │ Nama Mahasiswa                │ NIM        │ Kontribusi  │ Nilai Akhir UTS (0 - 100)│
├───┼───────────────────────────────┼────────────┼─────────────┼──────────────────────────┤
│ 1 │                               │            │             │                          │
│ 2 │                               │            │             │                          │
│ 3 │                               │            │             │                          │
└───┴───────────────────────────────┴────────────┴─────────────┴──────────────────────────┘

Catatan Evaluator / Dosen:
__________________________________________________________________________________________
__________________________________________________________________________________________
Tanda Tangan Dosen Penguji: ________________________  Tanggal: ___________________________
```
