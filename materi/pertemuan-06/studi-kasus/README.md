# Berkas Studi Kasus: Naive If-Role Checks vs Policy & Spatie RBAC

Direktori ini memuat perbandingan implementasi sistem otorisasi pada aplikasi web:

1. **`01-naive-if-role-checks.php`**:
   - Contoh kode rapuh yang banyak dibuat mahasiswa: melakukan pengecekan `if ($user->role == 'admin')` secara hardcoded di dalam Controller dan View Blade.
   - Masalah: Kode menjadi terduplikasi di puluhan controller, tidak fleksibel terhadap multi-peran, dan tidak memiliki abstraksi kepemilikan data (*resource ownership*).

2. **`02-policy-and-spatie-rbac.php`**:
   - Solusi berstandar enterprise:
     - Menggunakan **Model Policies** (`CoursePolicy`) untuk logika kepemilikan kursus.
     - Menggunakan **`spatie/laravel-permission`** untuk mengelola multi-role dan permissions yang dinamis.
     - Menerapkan **Gate Hook (`before`)** untuk otomatisasi Super Admin Bypass.
