# Pertemuan 03: Database Engineering: Schema Migration, Seeding & Model Factory

Selamat datang di modul perkuliahan dan praktikum **Pertemuan 03** mata kuliah Pemrograman Web Lanjut.

---

## 📌 Dokumen Pembelajaran & Praktikum

| Dokumen | Peruntukan | Deskripsi | Tautan |
| :--- | :--- | :--- | :--- |
| **Modul Praktikum** | Mahasiswa & Dosen | Panduan materi utama: Advanced Migration, Indexing, Factory States, Localized Faker Indonesia, Seeding Batch/Chunk, dan ACID Database Transactions. | [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) |
| **Skenario Pembelajaran (RPP)** | Dosen & Asisten Lab | Distribusi alokasi waktu 150 menit tatap muka (Teori, Live Demo Batch Seeding, Lab Hands-on). | [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) |
| **Rubrik & Pedoman Penilaian** | Dosen & Asisten Lab | Panduan penilaian tugas (100 poin), rubrik indikator, dan kunci jawaban standar referensi. | [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) |
| **Lembar Tugas Mandiri 03** | Mahasiswa | Soal latihan terstruktur mingguan: Perancangan skema 8 tabel, Factory bertingkat, Seeding 5.000 data, dan Transaksi ACID. | [TUGAS-03.md](TUGAS-03.md) |

---

## 📂 Berkas Studi Kasus Mandiri
Subfolder: `studi-kasus/`
- [studi-kasus/README.md](studi-kasus/README.md): Ringkasan komparasi performa seeder naive vs batch factory.
- 📄 [01-naive-seeder-slow.php](studi-kasus/01-naive-seeder-slow.php): Contoh kode seeder konvensional yang lambat (eksekusi insert satu per satu dalam looping tanpa transaksi).
- 📄 [02-optimized-factory-chunk.php](studi-kasus/02-optimized-factory-chunk.php): Solusi seeding performa tinggi menggunakan Model Factory, Faker `id_ID`, chunk batch insertion, dan `DB::transaction`.

---

## 🧭 Navigasi
- ⬅️ Kembali ke [Daftar Seluruh Modul (materi/README.md)](../README.md)
- 🏠 Kembali ke [Halaman Utama Repositori (README.md)](../../README.md)
