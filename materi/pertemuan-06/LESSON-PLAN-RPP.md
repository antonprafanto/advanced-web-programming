# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 06: Advanced Authentication, Authorization Policies & RBAC (Spatie)
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan & Review Evaluasi Tugas 05 (Service Layer & DTO)
[015 - 055'] Sesi Teori: AuthN vs AuthZ, Gates vs Policies, & Arsitektur Spatie RBAC
[055 - 065'] Istirahat Singkat / Tanya-Jawab Interaktif
[065 - 095'] Live Demo Dosen: Migrasi If-Role Menjadi Model Policy & Super Admin Bypass
[095 - 135'] Hands-on Lab Mahasiswa: Setup Spatie Permission, Seeder RBAC, & CoursePolicy
[135 - 150'] Evaluasi Hasil Praktikum, Penjelasan Tugas 06, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Review Tugas 05 (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa dan mengecek kehadiran.
  - Mengulas hasil submission Tugas 05 (kebersihan DTO dan independensi Service Layer dari HTTP).
  - **Pertanyaan Pemantik:** *"Di semester lalu, bagaimana kalian membedakan hak akses Admin dan User biasa? Apakah menggunakan `if ($user->role == 'admin')`? Bagaimana jika kampus menambahkan 5 role baru minggu depan?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Mengulas arsitektur starter kit modern **Laravel Breeze** (struktur controller di `app/Http/Controllers/Auth/` dan pencegahan brute-force via login rate limiting).
  - Membedakan konsep mendasar **Authentication** (siapa Anda) vs **Authorization** (apa yang boleh dilakukan).
  - Menjelaskan perbedaan **Gates** (aksi global seperti akses dashboard) vs **Policies** (otorisasi berbasis kepemilikan model).
  - Mengupas tuntas komparasi **RBAC Manual** (tabel pivot `role_user` pada [03-manual-rbac-implementation.php](studi-kasus/03-manual-rbac-implementation.php)) vs package industri **`spatie/laravel-permission`** (model relasi ternormalisasi & memori caching).
  - Mengulas fitur keamanan akun modern: **Email Verification** (`MustVerifyEmail`), **Password Reset Flow** (siklus hashing token di `password_reset_tokens`), **Password Confirmation** (`password.confirm`), serta konsep algoritma **Two-Factor Authentication (2FA / TOTP RFC 6238)**.

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Memperlihatkan kode anti-pattern di [01-naive-if-role-checks.php](studi-kasus/01-naive-if-role-checks.php).
  - Mendemonstrasikan langkah refactoring:
    1. Membuat kelas Policy: `php artisan make:policy CoursePolicy --model=Course`.
    2. Menulis logika kepemilikan (`$user->id === $course->instructor_id`).
    3. Mendaftarkan Super Admin Bypass di `AppServiceProvider::boot()` via `Gate::before()`.
    4. Menyederhanakan controller menjadi 1 baris: `Gate::authorize('update', $course)` seperti pada [02-policy-and-spatie-rbac.php](studi-kasus/02-policy-and-spatie-rbac.php).
- **Aktivitas Mahasiswa:**
  - Mengamati bagaimana otorisasi menjadi sangat terpusat dan tidak lagi mengotori controller.

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Menginstall package `spatie/laravel-permission` dan menambahkan trait `HasRoles` pada Model `User`.
  - Membuat Seeder untuk 3 Role (`super-admin`, `dosen`, `mahasiswa`) dan hak izin (*permissions*).
  - Menerapkan `CoursePolicy` dan menguji pembatasan akses via browser / tinker.
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Membantu mahasiswa yang mengalami kendala cache permission Spatie (solusi: `php artisan permission:cache-reset`).

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Mereview hasil pengujian otorisasi mahasiswa.
  - Memaparkan instruksi [TUGAS-06.md](TUGAS-06.md).
  - Memberi gambaran awal materi pertemuan 7: *File Management, Media Handling & Cloud Storage Abstraction (S3/Supabase)*.
