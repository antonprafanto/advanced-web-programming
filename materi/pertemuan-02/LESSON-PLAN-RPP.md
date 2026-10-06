# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 02: Advanced Routing, Controllers Pattern & Form Request Validation
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan & Review Tugas 01
[015 - 055'] Sesi Teori: Route Model Binding, Controller Patterns & Form Request
[055 - 065'] Istirahat Singkat / Diskusi Interaktif
[065 - 095'] Live Coding Dosen: Refactoring Fat Controller ke Form Request & Custom Middleware
[095 - 135'] Hands-on Lab Mahasiswa: Implementasi Invokable Controller, Form Request, & Middleware
[135 - 150'] Evaluasi Hasil Praktikum, Penjelasan Tugas 02, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Review Tugas 01 (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa dan mengecek presensi.
  - Membahas secara ringkas evaluasi [TUGAS-01.md](../pertemuan-01/TUGAS-01.md) (isu SQL Injection dan implementasi PHP 8.x).
  - Memberikan *hook* pemantik untuk pertemuan 2: *"Berapa baris kode controller terpanjang yang pernah kalian tulis di semester lalu? Bagaimana jika controller kita batasi maksimal 20 baris saja?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Menjelaskan keunggulan **Route Model Binding** (`{course:slug}`) dibanding manual query `findOrFail()`.
  - Membandingkan kapan menggunakan **Resource Controller** vs **Invokable Controller** (`__invoke`).
  - Menjelaskan bahaya *Fat Controller* dan bagaimana **Form Request** mengambil alih tanggung jawab validasi & sanitasi input.
  - Menjelaskan arsitektur pendaftaran Middleware pada Laravel 11/12 via `bootstrap/app.php` (`withMiddleware`).

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Menampilkan [01-fat-controller-bad.php](studi-kasus/01-fat-controller-bad.php) di editor.
  - Mendemonstrasikan langkah refactoring:
    1. Membuat Form Request: `php artisan make:request StoreStudentRegistrationRequest`.
    2. Memindahkan sanitasi ke `prepareForValidation()`.
    3. Memindahkan validasi ke `rules()` dan pesan bahasa Indonesia ke `messages()`.
    4. Mengganti controller menjadi invokable ramping seperti pada [02-clean-controller-formrequest.php](studi-kasus/02-clean-controller-formrequest.php).
    5. Membuat custom middleware `EnsureProfileIsComplete` dan mendaftarkannya di `bootstrap/app.php`.
- **Aktivitas Mahasiswa:**
  - Menyimak demonstrasi, mencatat poin penting pemisahan tanggung jawab (*Separation of Concerns*).

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Membuat route group terproteksi middleware di `routes/web.php`.
  - Membuat Form Request `ScholarshipApplicationRequest` dengan aturan IPK dan semester.
  - Membuat controller invokable `ApplyScholarshipController`.
  - Menguji pengiriman form valid & invalid (mengamati error JSON / flash message).
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Membantu mahasiswa yang mengalami kendala saat mendaftarkan alias middleware di Laravel 11/12.

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Memberikan umpan balik atas latihan lab mahasiswa.
  - Memaparkan instruksi [TUGAS-02.md](TUGAS-02.md).
  - Memberi gambaran singkat pertemuan 3: *Database Engineering: Schema Migration, Seeder & Factory*.
