# Pertemuan 08: Ujian Tengah Semester (UTS) — Midterm Project Defense

## 🎯 Capaian Pembelajaran yang Diuji (CPMK 1 & CPMK 2)
Mahasiswa mampu mendemonstrasikan penguasaan arsitektur web modern skala industri hasil integrasi pembelajaran Minggu 1 s/d 7: *Clean Architecture* (Service Layer & DTO), *Database Engineering* (Indexing, Batch Seeding, Transaksi ACID & Locking), optimasi *Eloquent ORM* (eliminasi N+1 problem), otorisasi ketat (*Spatie RBAC* & *Model Policies*), serta pengelolaan media aman (*Multi-Disk Storage* & *Temporary Signed URLs*).

---

## 📚 Dokumen Panduan Evaluasi UTS

| Dokumen / Berkas | Sasaran Pengguna | Deskripsi Ringkas |
| :--- | :---: | :--- |
| 📋 [PANDUAN-EVALUASI-UTS.md](PANDUAN-EVALUASI-UTS.md) | Mahasiswa & Dosen | Tata tertib ujian, alur presentasi *Live Code Defense* (15 menit per tim), verifikasi kontribusi Git, dan prosedur pengujian *live challenge*. |
| ⏱️ [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) | Dosen & Asisten | Skenario waktu 150 menit tatap muka lab, tata kelola antrean tim, dan alur sidang code review. |
| ⚖️ [RUBRIK-PENILAIAN-UTS.md](RUBRIK-PENILAIAN-UTS.md) | Dosen Penguji | Matriks penilaian berbasis OBE (Total 100 Poin), formulir rekap nilai individu, dan matriks penalti plagiarisme. |
| ❓ [BANK-SOAL-DAN-CHALLENGE-DEFENSE.md](BANK-SOAL-DAN-CHALLENGE-DEFENSE.md) | Dosen & Asisten | Bank 10 pertanyaan teknis penguji dan 5 skenario tantangan modifikasi kode langsung di tempat (*live modification*). |
| 📝 [BERITA-ACARA-DAN-FORMULIR-NILAI.md](BERITA-ACARA-DAN-FORMULIR-NILAI.md) | Dosen & Asisten | Berita acara resmi pelaksanaan evaluasi UTS dan formulir rekap nilai fisik berstandar program studi. |
| 📘 [Kontrak Kuliah Bab III](../../KONTRAK-KULIAH-DAN-PANDUAN-PROYEK.md#iii-panduan-proyek-tengah-semester-uts--milestone-1) | Mahasiswa | Ketentuan umum proyek tengah semester dan arsitektur dasar Milestone 1. |

---

## ⏱️ Rundown Sesi Evaluasi Tatap Muka (15 Menit / Tim)

```
[00' - 03'] Live Demo Fitur (Alur Autentikasi, Transaksi Data, Upload Berkas Privat)
     │
     ▼
[03' - 10'] Deep Code Walkthrough (Inspeksi Service Layer, DTO, Policy, Migration & Debugbar)
     │
     ▼
[10' - 15'] Live Challenge & Oral Defense Dosen (Uji Pemahaman Kode, Git Blame, & Anti-Plagiarisme)
```

1. **Live Demo Fitur (3 Menit):** Demonstrasi antarmuka dan alur bisnis utama aplikasi yang telah dibangun.
2. **Code Walkthrough (7 Menit):** Menunjukkan bukti arsitektur: controller ramping ($\le 25$ baris), tidak ada query N+1 pada Laravel Debugbar, dan bukti isolasi berkas sensitif di storage privat.
3. **Live Challenge & Tanya-Jawab (5 Menit):** Dosen menguji pemahaman individu setiap anggota tim melalui pertanyaan arsitektur dan modifikasi kode langsung di tempat (*live modification*).

---

## 🔗 Navigasi Pembelajaran
- ⬅️ **Pertemuan Sebelumnya:** [Pertemuan 07: File Management, Media Handling, & Review Milestone Proyek](../pertemuan-07/README.md)
- ➡️ **Pertemuan Selanjutnya:** [Pertemuan 09: RESTful API Engineering, Versioning & Eloquent API Resources](../pertemuan-09/README.md)
- 📋 **Kembali ke Indeks Modul:** [Daftar Seluruh Modul Materi](../README.md)
