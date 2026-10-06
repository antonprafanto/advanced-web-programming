# TUGAS PRAKTIKUM 07
## Topik: File Management, Media Handling & Cloud Storage Abstraction
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan menguji pemahaman dan keahlian mahasiswa dalam mengelola berkas media pada aplikasi web secara profesional: memisahkan aset publik dan privat, mengamankan dokumen rahasia identitas dari celah RCE dan kebocoran data (*data breach*), menerapkan *Temporary Signed URLs*, serta melakukan optimasi gambar otomatis menggunakan format WebP.

---

### II. SOAL STUDI KASUS: MODUL VERIFIKASI IDENTITAS MAHASISWA

Anda diminta membangun modul pengunggahan berkas verifikasi identitas mahasiswa baru pada Sistem Informasi Akademik:

#### Bagian A: Form Request & Penyimpanan Berkas Privat (Bobot 35%)
1. Buat Form Request `UploadDocumentRequest` dengan aturan ketat:
   - File berkas wajib bertipe: `pdf`, `jpg`, atau `png`.
   - Ukuran maksimal: 3 Megabyte (`3 * 1024 KB`).
   - Tipe dokumen wajib salah satu dari: `ktp`, `ijazah`, atau `kartu_keluarga`.
2. Simpan berkas ke dalam **disk privat** (`local` / `storage/app/private/documents/{user_id}/`).
3. Berkas **TIDAK BOLEH** disimpan menggunakan nama asli dari user. Wajib di-hash menggunakan UUID.
4. Simpan metadata berkas ke tabel basis data `identity_documents` (kolom: `user_id`, `type`, `original_filename`, `storage_path`, `mime_type`, `file_size_bytes`).

---

#### Bagian B: Secure Download via Temporary Signed URLs & Policy (Bobot 40%)
1. Buat endpoint untuk menghasilkan tautan sementara:
   - Tautan menggunakan `URL::temporarySignedRoute()`.
   - Masa berlaku tautan: **15 Menit**.
2. Buat endpoint pengunduhan: `GET /documents/{document}/download` yang diproteksi oleh middleware `signed`.
3. Buat `DocumentPolicy` dan daftarkan pada method pengunduhan:
   - Dokumen hanya boleh diunduh jika user yang sedang login adalah **pemilik dokumen** ATAU memiliki peran (*role*) **super-admin** / **verifikator**.
4. Controller mengalirkan berkas langsung ke peramban menggunakan `Storage::disk('local')->download()`.
5. Buktikan bahwa memodifikasi query parameter URL secara manual akan menghasilkan respons **HTTP 403 Invalid Signature**.

---

#### Bagian C: Media Processing: Watermarking & Konversi WebP (Bobot 25%)
1. Buat fitur unggah foto profil (avatar) atau media publik yang disimpan di **disk publik** (`public`).
2. Gunakan library manipulasi gambar modern (`intervention/image-laravel`):
   - Gambar dipotong otomatis (*crop cover* 400x400 px) atau di-resize proporsional.
   - Bubuhkan **Watermark** (teks semi-transparan misal `VERIFIED` atau logo) di sudut gambar.
   - Format gambar otomatis dikonversi menjadi **WebP** dengan kualitas 80% (sekaligus membuang metadata EXIF tersembunyi).
3. Simpan berkas dengan ekstensi `.webp`.
4. Jika user mengunggah berkas baru, berkas lama yang ada di disk publik harus **otomatis terhapus** (*clean storage garbage collection*).

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-07`.
2. Terapkan *Conventional Commits* (misal: `feat: validasi dan upload dokumen privat`, `feat: implementasi signed url dan policy unduhan`).
3. Sertakan file `README.md` pada folder tugas yang berisi bukti tangkapan layar (*screenshot*):
   - Bukti berkas tersimpan di direktori privat `storage/app/private/documents/`.
   - Bukti akses sukses pengunduhan berkas via Temporary Signed URL.
   - Bukti penolakan **HTTP 403 Invalid Signature** saat URL dimanipulasi atau kedaluwarsa.
   - Bukti berkas avatar berformat `.webp` di direktori publik `storage/app/public/avatars/`.
4. Batas pengumpulan: H-1 sebelum Ujian Tengah Semester (Pertemuan 08) dimulai.
