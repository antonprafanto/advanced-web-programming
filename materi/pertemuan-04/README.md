# Pertemuan 04: Deep Dive Eloquent ORM: Complex Relationships & N+1 Optimization

Selamat datang di modul perkuliahan dan praktikum **Pertemuan 04** mata kuliah Pemrograman Web Lanjut.

---

## 📌 Dokumen Pembelajaran & Praktikum

| Dokumen | Peruntukan | Deskripsi | Tautan |
| :--- | :--- | :--- | :--- |
| **Modul Praktikum** | Mahasiswa & Dosen | Panduan materi utama: Custom Pivot, Polymorphic Relations, Has-Many-Through, N+1 Query Problem, Eager Loading, Subquery Selects, dan Local/Global Scopes. | [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) |
| **Skenario Pembelajaran (RPP)** | Dosen & Asisten Lab | Distribusi alokasi waktu 150 menit tatap muka (Teori, Live Demo Profiling Query N+1, Lab Hands-on). | [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) |
| **Rubrik & Pedoman Penilaian** | Dosen & Asisten Lab | Panduan penilaian tugas (100 poin), rubrik indikator, dan kunci jawaban standar referensi. | [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) |
| **Lembar Tugas Mandiri 04** | Mahasiswa | Soal latihan terstruktur mingguan: Implementasi relasi polymorphic, benchmarking query N+1 vs Eager Loading, dan Local Scopes. | [TUGAS-04.md](TUGAS-04.md) |

---

## 📂 Berkas Studi Kasus Mandiri
Subfolder: `studi-kasus/`
- [studi-kasus/README.md](studi-kasus/README.md): Ringkasan perbandingan query N+1 vs Eager Loading.
- 📄 [01-n-plus-one-disaster.php](studi-kasus/01-n-plus-one-disaster.php): Contoh bencana performa N+1 query problem (menghasilkan ratusan query ke database dalam satu request).
- 📄 [02-eager-loading-optimized.php](studi-kasus/02-eager-loading-optimized.php): Solusi optimasi menggunakan Eager Loading, `withCount()`, dan subquery select (hanya mengeksekusi 2-3 query cepat).

---

## 🧭 Navigasi
- ⬅️ Kembali ke [Daftar Seluruh Modul (materi/README.md)](../README.md)
- 🏠 Kembali ke [Halaman Utama Repositori (README.md)](../../README.md)
