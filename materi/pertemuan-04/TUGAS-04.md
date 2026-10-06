# TUGAS PRAKTIKUM 04
## Topik: Relasi Polimorfik Ganda, Eliminasi N+1 Query & Local Scopes

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih mahasiswa dalam mengimplementasikan pemodelan relasi polimorfik tingkat lanjut, mengamankan arsitektur dari *N+1 Query Problem* menggunakan teknik *Eager Loading* dan `withCount()`, serta membangun logika kueri yang ekspresif dan bersih via *Local Query Scopes*.

---

### II. SOAL STUDI KASUS: PORTAL MEDIA KAMPUS (ARTIKEL & VIDEO)

Anda diminta membangun modul backend untuk portal multimedia kampus yang memuat dua jenis konten utama: **Artikel Berita (`Article`)** dan **Video Edukasi (`Video`)**.

#### Bagian A: Relasi Polimorfik Ganda (Bobot 40%)
1. **Polymorphic Comments (One-to-Many):**
   - Buat skema tabel `comments` yang memiliki relasi polimorfik (`morphs('commentable')`) sehingga dapat menampung komentar untuk `Article` maupun `Video`.
2. **Polymorphic Tags (Many-to-Many):**
   - Buat skema tabel `tags` dan tabel pivot polimorfik `taggables` (`tag_id, taggable_id, taggable_type`) sehingga satu artikel atau video dapat memiliki banyak tag (misal: "Teknologi", "Akademik", "Prestasi").
3. **Enforce Strict Morph Map:**
   - Daftarkan alias morph di `AppServiceProvider.php` (`article` => `Article::class`, `video` => `Video::class`).

---

#### Bagian B: Eliminasi N+1 Query & Benchmarking Log (Bobot 35%)
1. **Aktivasi Strict Mode:**
   - Aktifkan `Model::preventLazyLoading(! app()->isProduction())` di `AppServiceProvider.php`.
2. **Endpoint Teroptimasi:**
   - Buat route `GET /media/trending` yang menampilkan 25 konten teratas beserta data penulis (`author`), kategori, daftar tag (`tags`), dan **total hitungan komentar** menggunakan `withCount('comments')`.
   - **Ketentuan Mutlak:** Kueri harus dieksekusi secara optimal menggunakan *Eager Loading* bertingkat. Tidak boleh terjadi exception `LazyLoadingViolationException`!
3. **Bukti Benchmarking Query Log:**
   - Catat log kueri menggunakan `DB::getQueryLog()` dan laporkan jumlah query SQL yang dieksekusi (maksimal 4-5 query SQL untuk 25 data lengkap dengan relasinya).

---

#### Bagian C: Local Query Scopes Reusable (Bobot 25%)
Tambahkan minimal 2 *Local Scope* pada model `Article` dan `Video`:
1. `scopePublished($query)`: Menyaring data yang memiliki `status = 'published'` dan `published_at <= now()`.
2. `scopeTrending($query, int $minViews = 500)`: Menyaring konten yang memiliki jumlah tayangan (`views_count`) di atas batas minimal parameter.

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-04`.
2. Gunakan format *Conventional Commits* (misal: `feat: buat relasi polimorfik tags dan comments`, `feat: implementasi eager loading dan local scope`).
3. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*):
   - Bukti log kueri `DB::getQueryLog()` yang menunjukkan jumlah query yang sedikit dan efisien.
   - Bukti pengujian `preventLazyLoading` di terminal/browser.
4. Batas pengumpulan: H-1 sebelum Pertemuan 05 dimulai.
