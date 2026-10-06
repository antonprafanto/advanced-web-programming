# Studi Kasus: Penanganan Upload Berkas & Media Storage

Direktori ini membandingkan dua pendekatan implementasi fitur unggah berkas (*file upload*) pada aplikasi web:

## 1. [01-insecure-direct-upload.php](01-insecure-direct-upload.php) (Anti-Pattern / Sangat Berbahaya)
- **Karakteristik:**
  - Menggunakan fungsi pemindahan berkas langsung ke direktori publik (`public_path('uploads')`).
  - Menggunakan nama asli klien (`$file->getClientOriginalName()`) tanpa hashing.
  - Validasi hanya memeriksa ekstensi string sederhana tanpa validasi MIME-type ketat di server.
  - Berkas rahasia (KTP/Ijazah) ditaruh di folder publik dan dapat dibuka oleh siapa saja yang mengetahui URL-nya.
- **Dampak Fatal:** Rawan serangan *Remote Code Execution* (RCE) via web shell, penimpaan file sistem (*Path Traversal*), dan kebocoran data privasi massal (*Data Breach*).

## 2. [02-secure-multi-disk-storage.php](02-secure-multi-disk-storage.php) (Modern Industry Standard)
- **Karakteristik:**
  - Menggunakan abstraksi *Flysystem* (`Storage::disk()`).
  - Pemisahan ketat: Disk publik (`public`) untuk avatar dengan kompresi WebP otomatis, dan Disk privat (`local`/`s3`) untuk dokumen sensitif.
  - Nama berkas di-hash menggunakan UUID.
  - Akses dokumen sensitif diproteksi dengan *Temporary Signed URLs* dan otorisasi *Policy*.
- **Keunggulan:** Tahan terhadap RCE, aman dari sniffing berkas publik, hemat bandwidth hingga 70%, dan siap di-*deploy* ke AWS S3 / Supabase tanpa ubah kode bisnis.
