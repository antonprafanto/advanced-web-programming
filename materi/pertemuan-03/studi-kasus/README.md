# Berkas Studi Kasus: Seeding Performance & Database Transactions

Direktori ini memuat perbandingan teknis antara teknik pengisian data tiruan (*seeding*) konvensional dengan teknik optimasi tingkat lanjut:

1. **`01-naive-seeder-slow.php`**:
   - Contoh kode seeder naif yang menjalankan `Model::create()` atau `INSERT` satu demi satu di dalam looping perulangan.
   - Tanpa dibungkus transaksi database, menyebabkan ratusan network round-trip dan I/O disk yang sangat lambat.

2. **`02-optimized-factory-chunk.php`**:
   - Seeder modern berkecepatan tinggi menggunakan *Array Collection Chunking* dan bulk `DB::table()->insert()`.
   - Menggunakan Faker terlokalisasi Indonesia (`id_ID`) dan dibungkus dalam `DB::transaction()` untuk menjamin keamanan ACID.
