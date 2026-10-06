# Studi Kasus: Evolusi Arsitektur Otorisasi & RBAC

Direktori ini membandingkan tiga tahapan evolusi tata kelola hak akses (*authorization & RBAC*) pada aplikasi Laravel:

---

## 1. [01-naive-if-role-checks.php](01-naive-if-role-checks.php) (Anti-Pattern / Legacy)
- **Karakteristik:**
  - Logika otorisasi di-hardcode menggunakan string comparison `if ($user->role == 'admin')` di dalam controller method dan view.
  - Logika kepemilikan data disalin (*copy-paste*) berulang kali di banyak method controller.
- **Kelemahan Fatal:** Sangat rapuh, melanggar prinsip *Don't Repeat Yourself* (DRY), dan memicu *maintenance nightmare* saat ada penambahan peran baru.

---

## 2. [02-policy-and-spatie-rbac.php](02-policy-and-spatie-rbac.php) (Enterprise Industry Standard)
- **Karakteristik:**
  - Logika kepemilikan resource diisolasi rapi ke dalam kelas **Model Policy** (`CoursePolicy`).
  - Mengembalikan objek respons informatif `Response::allow()` dan `Response::deny('pesan kustom')`.
  - Mengintegrasikan package industri `spatie/laravel-permission` yang mendukung pemisahan peran (*Roles*) dan izin atomik (*Permissions*).
  - Menggunakan hook **Super Admin Bypass** (`Gate::before()`) untuk hak akses absolut tanpa boilerplate.
  - Controller sangat ramping (*Skinny Controller*) hanya memanggil `Gate::authorize('update', $course)`.

---

## 3. [03-manual-rbac-implementation.php](03-manual-rbac-implementation.php) (Arsitektur RBAC Manual)
- **Karakteristik:**
  - Membangun skema RBAC mandiri tanpa package eksternal menggunakan tabel `roles`, tabel pivot `role_user`, dan relasi `belongsToMany`.
  - Mengimplementasikan helper `$user->hasRole()` dan custom middleware `EnsureUserHasRole`.
- **Tujuan Pembelajaran:** Memahami cara kerja RBAC di level basis data dan mengetahui kapan RBAC manual cukup digunakan serta kapan wajib beralih ke package industri Spatie.
