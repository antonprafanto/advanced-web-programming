# Pertemuan 06: Advanced Authentication, Authorization Policies & RBAC (Spatie)

Selamat datang di modul perkuliahan dan praktikum **Pertemuan 06** mata kuliah Pemrograman Web Lanjut.

---

## 📌 Dokumen Pembelajaran & Praktikum

| Dokumen | Peruntukan | Deskripsi | Tautan |
| :--- | :--- | :--- | :--- |
| **Modul Praktikum** | Mahasiswa & Dosen | Panduan materi utama: Laravel Breeze, Gates vs Policies, Spatie Laravel-Permission (RBAC), Email Verification, dan Konsep 2FA. | [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) |
| **Skenario Pembelajaran (RPP)** | Dosen & Asisten Lab | Distribusi alokasi waktu 150 menit tatap muka (Teori Keamanan & Otorisasi, Live Setup Spatie & Policy, Lab Hands-on). | [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) |
| **Rubrik & Pedoman Penilaian** | Dosen & Asisten Lab | Panduan penilaian tugas (100 poin), rubrik indikator RBAC multi-role, dan kunci jawaban standar referensi. | [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) |
| **Lembar Tugas Mandiri 06** | Mahasiswa | Soal latihan terstruktur mingguan: Pembangunan sistem RBAC 3-Role (Admin, Dosen, Mahasiswa) dengan Model Policies & Email Verification. | [TUGAS-06.md](TUGAS-06.md) |

---

## 📂 Berkas Studi Kasus Mandiri
Subfolder: `studi-kasus/`
- [studi-kasus/README.md](studi-kasus/README.md): Ringkasan komparasi pengecekan peran hardcoded vs Policy & RBAC.
- 📄 [01-naive-if-role-checks.php](studi-kasus/01-naive-if-role-checks.php): Contoh anti-pattern pengecekan hak akses berserakan menggunakan `if ($user->role == 'admin')` di seluruh controller dan view.
- 📄 [02-policy-and-spatie-rbac.php](studi-kasus/02-policy-and-spatie-rbac.php): Solusi enterprise menggunakan Model Policies (`CoursePolicy`), Gate hooks, dan package `spatie/laravel-permission`.

---

## 🧭 Navigasi
- ⬅️ Kembali ke [Daftar Seluruh Modul (materi/README.md)](../README.md)
- 🏠 Kembali ke [Halaman Utama Repositori (README.md)](../../README.md)
