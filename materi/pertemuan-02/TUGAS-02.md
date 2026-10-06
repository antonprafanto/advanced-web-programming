# TUGAS PRAKTIKUM 02
## Topik: Form Request Validation, Custom Middleware & Clean Controller

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih mahasiswa dalam menerapkan prinsip *Separation of Concerns* pada alur penanganan request web: memisahkan otorisasi & verifikasi alur ke **Middleware**, memindahkan validasi & sanitasi input ke **Form Request**, serta merampingkan **Controller** menjadi single-action (*Skinny Controller*).

---

### II. SOAL STUDI KASUS: PENDAFTARAN PROPOSAL TUGAS AKHIR

Sebuah program studi membutuhkan modul pendaftaran proposal Tugas Akhir (Skripsi) mahasiswa. Anda diminta membangun modul backend untuk fitur tersebut dengan spesifikasi berikut:

#### Bagian A: Form Request `SubmitThesisProposalRequest` (Bobot 50%)
Buat kelas Form Request dengan ketentuan:
1. **Otorisasi (`authorize`):**
   - Hanya mahasiswa yang berstatus terdaftar yang dapat mengajukan proposal.
2. **Pra-Sanitasi Data (`prepareForValidation`):**
   - NIM wajib diubah menjadi huruf kapital dan dihilangkan spasinya.
   - Judul proposal wajib disanitasi dari tag HTML (`strip_tags`) dan diubah ke format huruf kapital awal kata (*Title Case*).
3. **Aturan Validasi (`rules`):**
   - `nim`: Wajib, tepat 14 karakter.
   - `judul_proposal`: Wajib, minimal 20 karakter, maksimal 255 karakter.
   - `sks_tempuh`: Wajib, angka integer, minimal 100 SKS.
   - `ipk_terakhir`: Wajib, desimal, minimal 3.00 dan maksimal 4.00.
   - `berkas_draft_pdf`: Wajib, file tipe PDF, ukuran maksimal 2048 KB (2 MB).
4. **Kustomisasi Pesan Error (`messages`):**
   - Seluruh pesan kesalahan wajib berbahasa Indonesia yang jelas dan informatif.

---

#### Bagian B: Custom Middleware `EnsureEligibleForThesis` (Bobot 30%)
1. Buat Custom Middleware:
   - Middleware bertugas memeriksa apakah akun mahasiswa memenuhi syarat administratif:
     - Jika status akademik adalah `Cuti` atau `Non-Aktif`, tolak request dengan mengembalikan pesan error (403 Forbidden atau redirect dengan flash message).
2. Daftarkan alias middleware `thesis.eligible` pada file `bootstrap/app.php` (standar arsitektur Laravel 11/12).

---

#### Bagian C: Invokable Controller & Routing (Bobot 20%)
1. Buat Single Action Controller: `SubmitThesisProposalController` (`__invoke`).
   - Controller ini hanya menerima `SubmitThesisProposalRequest` yang telah valid, lalu mengembalikan response sukses berformat JSON (HTTP Status 201 Created) berisi ringkasan data yang diajukan.
2. Daftarkan rute `POST /thesis/apply` dengan proteksi middleware `thesis.eligible` dan penamaan rute `thesis.apply`.

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-02`.
2. Gunakan standar *Conventional Commits* (misal: `feat: buat form request pengajuan proposal`, `feat: buat middleware kelayakan skripsi`).
3. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*) pengujian:
   - Pengujian skenario gagal (validasi error HTTP 422).
   - Pengujian skenario berhasil (HTTP 201 Created).
4. Batas pengumpulan: H-1 sebelum Pertemuan 03 dimulai.
