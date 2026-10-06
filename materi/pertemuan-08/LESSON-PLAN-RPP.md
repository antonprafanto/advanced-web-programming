# SKENARIO PELAKSANAAN EVALUASI (LESSON PLAN / RPP)
## PERTEMUAN 08: Ujian Tengah Semester (UTS) — Midterm Project Defense
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit Tatap Muka Lab

---

### I. IDENTITAS PELAKSANAAN
- **Mata Kuliah:** Pemrograman Web Lanjut (Advanced Web Programming)
- **Fase Pembelajaran:** Evaluasi Tahap 1 (Milestone 1 Defense)
- **Bentuk Ujian:** *Live Code Review & Oral Examination* (Bukan Ujian Tulis)
- **Tim Penguji:** Dosen Pengampu & Asisten Dosen (Asdos)
- **Peserta:** Seluruh Mahasiswa Peserta Kelas (Bekerja per Kelompok Proyek)

---

### II. DISTRIBUSI ALOKASI WAKTU (150 MENIT)

```
[000 - 010'] Pembukaan, Briefing Tata Tertib, & Pengundian Urutan Giliran Tim
[010 - 130'] Sesi Live Defense & Code Review (15 Menit x Maksimal 8 Kelompok)
[130 - 145'] Rapat Pleno Evaluator (Sinkronisasi Nilai Dosen & Verifikasi Git Blame Asdos)
[145 - 150'] Evaluasi Umum, Refleksi Arsitektur, & Penutupan Sesi UTS
```

---

### III. DETAIL AKTIVITAS & PERAN PENGUJI

#### Sesi 1: Pembukaan & Pengundian Antrean (Menit 00 – 10)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa, memimpin doa, dan memeriksa presensi kehadiran.
  - Memastikan seluruh kelompok telah melakukan *push* commit final dan membuat Git Tag `v0.5.0-uts` sebelum batas waktu.
- **Aktivitas Asisten Lab (Asdos):**
  - Mengundi nomor urut giliran maju kelompok.
  - Menyiapkan proyektor/layar lab dan lembar berita acara ujian.

#### Sesi 2: Sesi Live Code Defense Per Kelompok (Menit 10 – 130)
Setiap kelompok mendapatkan alokasi waktu **15 Menit** dengan pembagian:

1. **Menit 01 – 03 (Live Feature Demo):**
   - Mahasiswa mendemonstrasikan aplikasi: login multi-role (Admin vs User), menjalankan 1 transaksi bisnis utama (checkout/pendaftaran), dan upload berkas rahasia.
2. **Menit 03 – 10 (Deep Architectural Code Review):**
   - Dosen menginspeksi struktur kode:
     - Apakah controller ramping ($\le 25$ baris)?
     - Apakah terdapat Service Layer dan DTO bertipe tegas?
     - Apakah ada query N+1 pada Laravel Debugbar?
     - Apakah Model Policy memproteksi resource ownership?
3. **Menit 10 – 15 (Oral Defense & Live Challenge):**
   - Dosen mengajukan pertanyaan individual kepada setiap anggota tim mengacu pada [BANK-SOAL-DAN-CHALLENGE-DEFENSE.md](BANK-SOAL-DAN-CHALLENGE-DEFENSE.md).
   - Dosen memberikan 1 *live modification challenge* (misal: "Ubah masa berlaku signed URL dari 15 menit menjadi 5 menit, lalu uji apakah link lama ditolak").
- **Aktivitas Asdos (Time-Keeper & Auditor):**
  - Mengingatkan sisa waktu (peringatan di menit ke-12).
  - Melakukan audit silang grafik kontribusi commit GitHub (`Contributors Graph`) untuk mendeteksi *free-rider*.

#### Sesi 3: Rapat Pleno Evaluator (Menit 130 – 145)
- Dosen dan Asdos melakukan rekapitulasi nilai individual.
- Menyamakan catatan penalti (misal: pemotongan poin untuk query N+1 atau dokumen yang tersimpan di folder publik).
- Mengisi dan menandatangani Berita Acara Ujian Resmi.

#### Sesi 4: Evaluasi Umum & Penutupan (Menit 145 – 150)
- Dosen memberikan *feedback* umum mengenai kekuatan dan kelemahan arsitektur yang ditemukan di kelas.
- Dosen memberikan pengantar singkat fase pasca-UTS (Minggu 09: RESTful API Engineering).

---

### IV. PERLENGKAPAN & RUJUKAN PENDUKUNG
- 📋 [PANDUAN-EVALUASI-UTS.md](PANDUAN-EVALUASI-UTS.md): Aturan main dan checklist arsitektur.
- ⚖️ [RUBRIK-PENILAIAN-UTS.md](RUBRIK-PENILAIAN-UTS.md): Rubrik penilaian 100 poin berbasis OBE.
- ❓ [BANK-SOAL-DAN-CHALLENGE-DEFENSE.md](BANK-SOAL-DAN-CHALLENGE-DEFENSE.md): Panduan pertanyaan penguji & studi kasus modifikasi live.
- 📝 [BERITA-ACARA-DAN-FORMULIR-NILAI.md](BERITA-ACARA-DAN-FORMULIR-NILAI.md): Format berita acara dan scoring sheet fisik.
