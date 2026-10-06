# BANK SOAL & SKENARIO LIVE CHALLENGE DEFENSE UTS
## Panduan Uji Lisan & Praktik untuk Dosen Penguji & Asisten Lab
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENGGUNAAN
Dokumen ini disusun untuk membantu Dosen Penguji dan Asdos dalam menguji pemahaman orisinal setiap mahasiswa pada saat sesi **Midterm Project Defense (15 Menit)**. Penguji disarankan memilih **2 - 3 pertanyaan konseptual** dan **1 skenario live modification challenge** secara acak untuk setiap kelompok.

---

### II. BANK PERTANYAAN KONSEPTUAL PENGUJI (ORAL DEFENSE)

#### Kategori A: Clean Architecture & SOLID Principles
1. **Pertanyaan 1 (Service Layer HTTP-Agnostic):**
   > *"Tunjukkan pada kode Service Anda, apakah ada pemanggilan method `$request->all()`, fungsi `response()->json()`, atau `redirect()`? Jika ada, jelaskan mengapa hal tersebut melanggar prinsip pemisahan tanggung jawab (Separation of Concerns)!"*
   - **Ekspektasi Jawaban:** Service Layer harus murni menerima DTO / parameter primitif dan mengembalikan objek Model/DTO. Response HTTP atau redirect adalah tanggung jawab eksklusif Controller.

2. **Pertanyaan 2 (Peran DTO):**
   > *"Mengapa Anda repot-repot membuat kelas DTO (Data Transfer Object) alih-alih melempar associative array `$request->all()` langsung ke dalam Service?"*
   - **Ekspektasi Jawaban:** DTO memberikan jaminan *type-safety* (kejelasan tipe data via PHP 8 readonly class), autocompletion di IDE, mencegah *unintentional mass-assignment*, dan menjadi kontrak data yang tegas antar-lapisan.

3. **Pertanyaan 3 (Dependency Injection):**
   > *"Di mana Anda mendaftarkan kelas Service atau Repository Anda ke dalam Service Container Laravel? Apa perbedaan antara method `$this->app->bind()` dan `$this->app->singleton()`?"*
   - **Ekspektasi Jawaban:** Didaftarkan di `AppServiceProvider::register()`. `bind()` membuat instance objek baru setiap kali di-resolve, sedangkan `singleton()` hanya membuat 1 instance bersama sepanjang siklus hidup request.

---

#### Kategori B: Database Engineering & ACID Transactions
4. **Pertanyaan 4 (Pessimistic Locking):**
   > *"Tunjukkan di mana Anda menggunakan `lockForUpdate()`. Masalah apa yang akan terjadi jika dua pengguna menekan tombol transaksi kuota/saldo pada milidetik yang sama tanpa pessimistic locking?"*
   - **Ekspektasi Jawaban:** Akan terjadi *race condition* (overbooking/stok minus) karena kedua proses membaca saldo yang sama sebelum salah satu proses berhasil menulis saldo baru. `lockForUpdate()` mengunci baris di tabel database hingga transaksi ACID selesai di-commit.

5. **Pertanyaan 5 (Database Indexing):**
   > *"Kolom apa saja pada migrasi Anda yang Anda berikan `->index()`? Mengapa kita tidak memberikan index pada seluruh kolom tabel database?"*
   - **Ekspektasi Jawaban:** Index diberikan pada kolom yang sering muncul di klausa `WHERE`, `ORDER BY`, dan foreign keys. Kita tidak mengindeks semua kolom karena setiap index membutuhkan alokasi memori tambahan dan memperlambat operasi penulisan (`INSERT`/`UPDATE`).

---

#### Kategori C: Eloquent ORM & Query Performance
6. **Pertanyaan 6 (Pembuktian N+1 Problem):**
   > *"Buka Laravel Debugbar pada halaman data tabel Anda. Berapa banyak query SQL yang dieksekusi saat menampilkan 50 baris data? Jika data bertambah menjadi 10.000 baris, apakah jumlah query bertambah?"*
   - **Ekspektasi Jawaban:** Mahasiswa membuktikan jumlah query tetap konstan (misal: hanya 2-3 query berkat `with()` / Eager Loading), bukan bertambah menjadi puluhan atau ratusan query ($N+1$).

7. **Pertanyaan 7 (Polymorphic Relations):**
   > *"Jika proyek Anda menggunakan relasi polimorfik (misal: komentar atau tag), bagaimana Laravel mengetahui model tujuan tanpa membuat tabel terpisah untuk setiap entitas?"*
   - **Ekspektasi Jawaban:** Melalui dua kolom: `[name]_id` (ID integer tujuan) dan `[name]_type` (nama class model target seperti `App\Models\Course`).

---

#### Kategori D: Keamanan, RBAC & Media Storage
8. **Pertanyaan 8 (Super Admin Bypass):**
   > *"Bagaimana cara kerja hook `Gate::before()` di `AppServiceProvider`? Mengapa kita tidak perlu memberi permissions satu per satu di database seeder untuk akun Super Admin?"*
   - **Ekspektasi Jawaban:** `Gate::before()` mencegat seluruh pengecekan sebelum Policy dievaluasi. Jika user memiliki role `super-admin`, hook langsung me-return `true`, sehingga super-admin lolos secara global tanpa perlu ratusan record izin di database.

9. **Pertanyaan 9 (Temporary Signed URLs):**
   > *"Buktikan apa yang terjadi jika salah satu query parameter pada URL unduhan dokumen privat Anda diubah 1 karakter saja di browser!"*
   - **Ekspektasi Jawaban:** Mahasiswa mempraktikkan modifikasi URL, dan browser langsung menampilkan **HTTP 403 Invalid Signature** karena hash HMAC SHA-256 yang ditandatangani dengan `APP_KEY` menjadi tidak cocok.

10. **Pertanyaan 10 (Pembersihan Metadata Gambar):**
    > *"Mengapa gambar foto profil di-reencode ke WebP alih-alih disimpan langsung berkas JPEG aslinya?"*
    - **Ekspektasi Jawaban:** Menghemat ukuran file hingga 70% dan secara otomatis membuang (*strip*) seluruh metadata EXIF yang berpotensi menyembunyikan payload kode PHP berbahaya (*polyglot malware*).

---

### III. SKENARIO LIVE MODIFICATION CHALLENGES (PENGUJIAN PRAKTIK LANGSUNG)

Dosen dapat meminta salah satu anggota kelompok melakukan modifikasi kode langsung di depan penguji dalam waktu **3 - 5 menit**:

#### Challenge 1: Modifikasi Batas Waktu Signed URL
- **Instruksi Penguji:** *"Ubah masa berlaku Temporary Signed URL dokumen Anda dari 15 menit menjadi hanya 10 detik. Kemudian refresh halaman setelah 11 detik untuk membuktikan link sudah hangus!"*
- **Kunci Keberhasilan:** Mengubah parameter `now()->addMinutes(15)` menjadi `now()->addSeconds(10)` di controller/service dan membuktikan respons 403.

#### Challenge 2: Tambah Validasi Dimensi Avatar
- **Instruksi Penguji:** *"Tambahkan aturan validasi pada Form Request foto profil Anda agar menolak gambar yang resolusinya kurang dari 300x300 pixel!"*
- **Kunci Keberhasilan:** Menambahkan `File::image()->dimensions(Rule::dimensions()->minWidth(300)->minHeight(300))` pada `UploadAvatarRequest`.

#### Challenge 3: Cek Hak Akses Policy di Blade
- **Instruksi Penguji:** *"Sembunyikan tombol 'Hapus' pada halaman web jika user yang login bukan pemilik data tersebut menggunakan direktif Blade `@can`!"*
- **Kunci Keberhasilan:** Membungkus elemen tombol dengan `@can('delete', $item) ... @endcan`.

#### Challenge 4: Uji Coba Pessimistic Locking
- **Instruksi Penguji:** *"Buka Tinker atau dua tab browser berbeda, simulasikan alur transaksi kuota dan tunjukkan log transaksi database!"*
- **Kunci Keberhasilan:** Mahasiswa mampu menjelaskan baris `lockForUpdate()` dan `DB::transaction()`.

#### Challenge 5: Reset Cache Permission Spatie
- **Instruksi Penguji:** *"Tambahkan 1 role baru di database, lalu tunjukkan perintah apa yang wajib dijalankan jika role tersebut belum terbaca di sistem!"*
- **Kunci Keberhasilan:** Mahasiswa mengeksekusi `php artisan permission:cache-reset`.
