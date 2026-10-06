# Pertemuan 06: Advanced Authentication, Authorization Policies & RBAC (Spatie)

## 🎯 Capaian Pembelajaran (Sub-CPMK)
Mahasiswa mampu membedakan secara komprehensif antara Autentikasi (AuthN) dan Otorisasi (AuthZ), menerapkan sistem otorisasi tingkat lanjut menggunakan native *Gates* dan *Model Policies*, membangun arsitektur *Role-Based Access Control* (RBAC) skala industri dengan package `spatie/laravel-permission`, mengonfigurasi *Super Admin Bypass hook*, serta mengamankan alur transaksi sensitif dengan verifikasi email (*Email Verification*) dan konfirmasi kata sandi.

---

## 📚 Daftar Isi Perangkat Pembelajaran

| Dokumen / Berkas | Sasaran Pengguna | Deskripsi Ringkas |
| :--- | :---: | :--- |
| 📘 [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) | Mahasiswa & Dosen | Panduan lab komprehensif: AuthN vs AuthZ, Gates vs Policies, integrasi Spatie RBAC, cache permission, Super Admin Bypass hook, dan pengamanan akun lanjutan. |
| ⏱️ [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) | Dosen & Asisten | Skenario pembelajaran tatap muka 150 menit (Apersepsi celah otorisasi, live code Spatie RBAC & Policies, lab hands-on tinker, evaluasi). |
| 📝 [TUGAS-06.md](TUGAS-06.md) | Mahasiswa | Lembar instruksi tugas praktikum: pembangunan modul LMS multi-role kampus (Super Admin, Dosen, Mahasiswa) dengan kepemilikan data dan email verification. |
| ⚖️ [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) | Dosen & Asdos | Standar grading 100 poin, indikator penilaian, matriks sanksi penalti, serta kunci jawaban referensi lengkap. |
| 📂 [studi-kasus/](studi-kasus/README.md) | Mahasiswa | Komparasi 3 arsitektur:<br>• [01-naive-if-role-checks.php](studi-kasus/01-naive-if-role-checks.php) (Hardcoded If-Role)<br>• [02-policy-and-spatie-rbac.php](studi-kasus/02-policy-and-spatie-rbac.php) (Enterprise Spatie & Policy)<br>• [03-manual-rbac-implementation.php](studi-kasus/03-manual-rbac-implementation.php) (Manual Pivot RBAC) |

---

## 🔑 Konsep Kunci yang Dipelajari

```
Incoming Request
       │
       ▼
[Middleware: auth] ──(Belum Login)──> Redirect / 401 Unauthorized
       │
       ▼ (Telah Terotentikasi)
[Gate::before() Hook] ──(Role: super-admin)──> [BYPASS OTOMATIS: ALLOW] ──> Controller Action
       │
       ▼ (Bukan Super Admin)
[Model Policy (CoursePolicy)]
       ├── Cek Permission: $user->can('edit courses')
       └── Cek Kepemilikan: $user->id === $course->instructor_id
              │
              ├── [True]  ──> Controller Action Executed
              └── [False] ──> HTTP 403 Forbidden ("Anda bukan dosen pengampu kursus ini")
```

1. **Authentication vs Authorization:** AuthN membuktikan *siapa* Anda, sedangkan AuthZ menentukan *apa* yang boleh Anda lakukan.
2. **Gates vs Policies:** Gates untuk aksi global non-model (akses panel admin), Policies untuk aksi terikat model Eloquent tertentu (*resource ownership*).
3. **Spatie RBAC Ecosystem:** Relasi ternormalisasi $M:N$ antara `users`, `roles`, dan `permissions` dengan caching performa tinggi.
4. **Super Admin Bypass Pattern:** Memusatkan izin absolut super administrator di `Gate::before()` agar tidak perlu mengotori seeder dengan ratusan assignment permission manual.

---

## 🔗 Navigasi Pembelajaran
- ⬅️ **Pertemuan Sebelumnya:** [Pertemuan 05: Enterprise Architecture: Service Layer, DTO & Repository Pattern](../pertemuan-05/README.md)
- ➡️ **Pertemuan Selanjutnya:** [Pertemuan 07: File Management, Media Handling & Cloud Storage Abstraction](../pertemuan-07/README.md)
- 📋 **Kembali ke Indeks Modul:** [Daftar Seluruh Modul Materi](../README.md)
