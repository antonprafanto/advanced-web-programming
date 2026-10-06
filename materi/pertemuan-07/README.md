# Pertemuan 07: File Management, Media Handling & Cloud Storage Abstraction

## 🎯 Capaian Pembelajaran (Sub-CPMK)
Mahasiswa mampu mengelola asset media secara aman pada arsitektur multi-disk (*local private*, *public*, dan *cloud object storage* seperti S3/Supabase), menerapkan validasi keamanan berkas untuk mencegah kerentanan *Remote Code Execution* (RCE), mengimplementasikan *Temporary Signed URLs* untuk berkas privat, serta melakukan optimasi gambar secara dinamis.

---

## 📚 Daftar Isi Perangkat Pembelajaran

| Dokumen / Berkas | Sasaran Pengguna | Deskripsi Ringkas |
| :--- | :---: | :--- |
| 📘 [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md) | Mahasiswa & Dosen | Panduan lab komprehensif: arsitektur Flysystem, disk lokal vs publik vs S3, pengamanan unggah berkas, signed URL, dan konversi WebP. |
| ⏱️ [LESSON-PLAN-RPP.md](LESSON-PLAN-RPP.md) | Dosen & Asisten | Skenario pembelajaran tatap muka 150 menit (Apersepsi bahaya RCE, live code storage abstraction, lab hands-on, asistensi UTS). |
| 📝 [TUGAS-07.md](TUGAS-07.md) | Mahasiswa | Lembar instruksi tugas praktikum: modul berkas rahasia (KTP/Ijazah) dengan proteksi signed URL dan avatar WebP. |
| ⚖️ [RUBRIK-PENILAIAN-ASDOS.md](RUBRIK-PENILAIAN-ASDOS.md) | Dosen & Asdos | Standar grading 100 poin, indikator penilaian, matriks penalti, serta kunci jawaban implementasi referensi. |
| 📂 [studi-kasus/](studi-kasus/README.md) | Mahasiswa | Perbandingan kode *insecure direct upload* vs *secure multi-disk storage*. |

---

## 🔑 Konsep Kunci yang Dipelajari

```
User Upload ──> [FormRequest: Strict MIME & Size] ──> [File Sanitization / UUID]
                                                              │
                    ┌─────────────────────────────────────────┴─────────────────────────────────────────┐
                    ▼                                                                                   ▼
       [Dokumen Rahasia (KTP/Ijazah)]                                                       [Avatar / Media Publik]
                    │                                                                                   │
        Disk: 'private' (Local/S3)                                                          Disk: 'public' (storage/app/public)
                    │                                                                                   │
          Akses Publik Diblokir                                                                 WebP Resize & Optimize
                    │                                                                                   │
    Download via Temporary Signed URL                                                   Akses via /storage URL (Symlink)
  (URL::temporarySignedRoute, exp: 30m)
```

1. **Flysystem Storage Abstraction:** Menulis kode satu kali, dapat dijalankan di Local Storage, AWS S3, Cloudflare R2, maupun Supabase Storage tanpa merombak logika aplikasi.
2. **Security Vulnerability Prevention:** Mencegah celah berbahaya seperti *Unrestricted File Upload*, *Path Traversal*, dan pengeksekusian script PHP jahat (*Remote Code Execution*).
3. **Privacy via Signed URLs:** Mengamankan berkas sensitif dengan tautan terenkripsi yang memiliki masa kedaluwarsa waktu (*time-limited cryptographic token*).
4. **On-the-fly Image Processing:** Mengompres ukuran media dan mengubah format menjadi WebP demi efisiensi *bandwidth* dan kecepatan *loading* web.

---

## 🔗 Navigasi Pembelajaran
- ⬅️ **Pertemuan Sebelumnya:** [Pertemuan 06: Advanced Authentication, Authorization Policies & RBAC](../pertemuan-06/README.md)
- ➡️ **Pertemuan Selanjutnya:** [Pertemuan 08: Ujian Tengah Semester (UTS) - Review Milestone Proyek](../../silabus-pemrograman-web-lanjut.md#minggu-8-ujian-tengah-semester-uts)
- 📋 **Kembali ke Indeks Modul:** [Daftar Seluruh Modul Materi](../README.md)
