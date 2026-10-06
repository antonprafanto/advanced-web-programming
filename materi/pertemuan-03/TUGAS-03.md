# TUGAS PRAKTIKUM 03
## Topik: Advanced Migration, Model Factory Bertingkat, High-Speed Seeding & Transaksi ACID

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih mahasiswa dalam merancang arsitektur basis data relasional enterprise: menerapkan integritas relasi foreign key, optimasi indeks pencarian, memproduksi ribuan dummy data realistis berbasis Factory, serta mengamankan alur transaksi multi-tabel menggunakan prinsip ACID.

---

### II. SOAL STUDI KASUS: SISTEM E-COMMERCE MINI (8 TABEL)

Anda diminta membangun fondasi basis data untuk aplikasi toko daring (*e-commerce*) dengan ketentuan:

#### Bagian A: Perancangan Skema & Migration (Bobot 40%)
Rancang skema basis data yang memuat minimal 8 tabel berelasi:
1. `users` (pelanggan & admin)
2. `categories` (kategori produk)
3. `products` (produk barang)
4. `product_images` (galeri foto produk)
5. `orders` (header pesanan belanja)
6. `order_items` (detail barang yang dibeli)
7. `payments` (riwayat transaksi pembayaran)
8. `product_reviews` (ulasan produk oleh pelanggan)

**Ketentuan Khusus Migration:**
- Gunakan `foreignId()->constrained()->cascadeOnDelete()` atau aturan cascade yang relevan antar-tabel.
- Tabel `products` wajib mengaktifkan `softDeletes()`.
- Tambahkan minimal **2 Composite Index** (misal: `[category_id, status]` di `products` dan `[user_id, status]` di `orders`).
- Tambahkan minimal **1 Composite Unique** (misal: pelanggan hanya boleh memberikan 1 ulasan untuk produk yang sama: `unique(['user_id', 'product_id'])`).

---

#### Bagian B: Model Factory & State Transformations (Bobot 30%)
1. Aktifkan lokalisasi Faker Indonesia (`FAKER_LOCALE=id_ID`).
2. Buat Model Factory untuk entitas utama:
   - `ProductFactory`:
     - State `outOfStock()`: Mengubah stok menjadi 0.
     - State `discounted()`: Mengubah harga promo lebih rendah dari harga normal.
   - `OrderFactory`:
     - State `paid()`: Mengubah status menjadi 'paid' dan mengisi tanggal `paid_at`.
     - State `cancelled()`: Mengubah status menjadi 'cancelled'.

---

#### Bagian C: High-Speed Seeding (5.000 Data) & Transaksi ACID (Bobot 30%)
1. **High-Speed Seeder:**
   - Buat seeder yang mampu mengisikan minimal **5.000 data produk/transaksi** secara cepat menggunakan teknik *chunk batch insertion* (`collect()->chunk()->each()`). Waktu eksekusi tidak boleh melebihi 10 detik!
2. **Simulasi Transaksi ACID:**
   - Buat sebuah class service atau script console/controller yang mengimplementasikan proses *Checkout* menggunakan `DB::transaction()`:
     - Mengurangi stok barang.
     - Membuat record `orders` dan `order_items`.
     - Melakukan `throw new Exception` jika stok produk kurang dari jumlah beli, lalu buktikan bahwa seluruh perubahan berhasil di-rollback secara otomatis.

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-03`.
2. Gunakan format *Conventional Commits* (misal: `feat: buat migration 8 tabel e-commerce`, `feat: implementasi factory state dan batch seeder`).
3. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*):
   - Hasil eksekusi `php artisan migrate:status`.
   - Waktu eksekusi seeder 5.000 data di terminal.
   - Bukti rollback data transaksi saat terjadi kegagalan stok.
4. Batas pengumpulan: H-1 sebelum Pertemuan 04 dimulai.
