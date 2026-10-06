# Berkas Studi Kasus: N+1 Query Problem vs Eager Loading

Direktori ini memuat berkas komparasi benchmarking performa Eloquent ORM:

1. **`01-n-plus-one-disaster.php`**:
   - Contoh bencana performa ketika data berelasi diakses di dalam perulangan loop tanpa eager loading.
   - Mengambil 50 postingan artikel memicu **101 query SQL** ke database (1 query post + 50 query author + 50 query category).

2. **`02-eager-loading-optimized.php`**:
   - Solusi arsitektural berkecepatan tinggi menggunakan Eager Loading (`with(['author', 'category'])`) dan subquery aggregation `withCount('comments')`.
   - Mengambil 50 postingan artikel hanya membutuhkan **3 query SQL** berkecepatan tinggi via klausa `WHERE IN`.
