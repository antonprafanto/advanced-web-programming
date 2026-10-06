# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 01: Transisi dari Native PHP ke Modern PHP 8.x & Request Lifecycle Laravel
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan, Kontrak Kuliah, & Apersepsi
[015 - 055'] Sesi Teori: Paradigma Modern PHP 8.x, PSR-4, & Request Lifecycle
[055 - 065'] Istirahat Singkat / Tanya-Jawab Interaktif
[065 - 095'] Live Coding Dosen: Refactoring Studi Kasus Spaghetti Code
[095 - 135'] Hands-on Lab Mahasiswa: Setup Laravel, Debug Lifecycle, & Tinker
[135 - 150'] Review Pembelajaran, Penjelasan Tugas 01, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Apersepsi (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa, mengecek kehadiran.
  - Memaparkan silabus perkuliahan dan *Course Roadmap* (target UTS & UAS).
  - Menyepakati [Kontrak Perkuliahan & Kebijakan AI](file:///c:/Users/anton/vibecoding/weblanjut/KONTRAK-KULIAH-DAN-PANDUAN-PROYEK.md).
  - **Pertanyaan Pemantik (Apersepsi):** *"Siapa yang di semester lalu saat membuat aplikasi web pernah mengalami error `Headers already sent` atau lupa menutup tag PHP di dalam file HTML?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/MODUL-PRAKTIKUM.md).
  - Membedah masalah skalabilitas kode PHP prosedural (*spaghetti code* & *fragile include paths*).
  - Menjelaskan bagaimana Composer PSR-4 Autoloading bekerja di balik layar.
  - Mengulas fitur unggulan PHP 8.x (*Constructor Promotion, Match, Nullsafe, Readonly, Attributes*).
  - Menjelaskan arsitektur *Request Lifecycle* dan struktur *Slim Skeleton* Laravel 11/12.

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Membuka file [01-native-legacy.php](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/studi-kasus/01-native-legacy.php).
  - Menunjukkan celah SQL Injection jika input `id=1 OR 1=1` disuntikkan ke parameter query.
  - Melakukan *live refactoring* baris demi baris menjadi kode modern OOP bertipe ketat seperti pada [02-modern-php8-refactored.php](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/studi-kasus/02-modern-php8-refactored.php).
- **Aktivitas Mahasiswa:**
  - Mengamati perubahan paradigma dari prosedural ke OOP modular dan mencatat poin-poin *clean code*.

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/MODUL-PRAKTIKUM.md).
  - Memverifikasi environment lokal (`php -v`, `composer -V`, ekstensi PDO).
  - Menginisialisasi project Laravel baru via Composer.
  - Bereksperimen dengan route `/cek-request` dan mencoba manipulasi data di `php artisan tinker`.
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Berkeliling memfasilitasi mahasiswa yang mengalami kendala teknis (khususnya isu environment PATH pada Windows).

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Menarik kesimpulan inti pertemuan hari ini.
  - Menjelaskan teknis pengumpulan [TUGAS-01.md](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/TUGAS-01.md) dan batas waktu pengumpulan di GitHub.
  - Memberi gambaran singkat materi pertemuan 2: *Advanced Routing, Controller Pattern & Form Request Validation*.
