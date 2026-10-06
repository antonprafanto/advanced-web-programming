# Pertemuan 05: Enterprise Architecture: Service Layer, DTO & Dependency Injection

Selamat datang di modul perkuliahan dan praktikum **Pertemuan 05** mata kuliah Pemrograman Web Lanjut.

---

## 📌 Dokumen Pembelajaran & Praktikum

| Dokumen | Peruntukan | Deskripsi | Tautan |
| :--- | :--- | :--- | :--- |
| **Modul Praktikum** | Mahasiswa & Dosen | Panduan materi utama: Dekonstruksi Fat Controller, Service Layer, Strongly-Typed DTO, Action Classes, dan Dependency Injection (IoC Container). | [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) |
| **Skenario Pembelajaran (RPP)** | Dosen & Asisten Lab | Distribusi alokasi waktu 150 menit tatap muka (Teori Arsitektur Enterprise, Live Refactoring, Lab Hands-on). | [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) |
| **Rubrik & Pedoman Penilaian** | Dosen & Asisten Lab | Panduan penilaian tugas (100 poin), rubrik indikator SOLID, dan kunci jawaban standar referensi. | [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) |
| **Lembar Tugas Mandiri 05** | Mahasiswa | Soal latihan terstruktur mingguan: Refactoring sistem checkout transaksi monolitik menjadi arsitektur berlapis berbasis DTO & Service Layer. | [TUGAS-05.md](TUGAS-05.md) |

---

## 📂 Berkas Studi Kasus Mandiri
Subfolder: `studi-kasus/`
- [studi-kasus/README.md](studi-kasus/README.md): Ringkasan perbandingan kode monolitik fat controller vs arsitektur berlapis.
- 📄 [01-monolithic-fat-controller.php](studi-kasus/01-monolithic-fat-controller.php): Contoh kode nyata controller 120 baris yang mencampur logika HTTP, kalkulasi kupon, database, payment gateway, dan notifikasi.
- 📄 [02-layered-service-dto.php](studi-kasus/02-layered-service-dto.php): Solusi refactoring berstandar enterprise dengan Controller ramping (15 baris), DTO immutable, Service Layer, dan Interface Binding.

---

## 🧭 Navigasi
- ⬅️ Kembali ke [Daftar Seluruh Modul (materi/README.md)](../README.md)
- 🏠 Kembali ke [Halaman Utama Repositori (README.md)](../../README.md)
