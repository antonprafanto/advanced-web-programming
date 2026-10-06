# Berkas Studi Kasus: Fat Controller vs Clean Controller & Form Request

Direktori ini memuat perbandingan arsitektural penanganan request web di Laravel:

1. **`01-fat-controller-bad.php`**:
   - Contoh kode yang sering dibuat oleh developer pemula / mahasiswa.
   - Melakukan pengecekan hak akses manual di dalam controller.
   - Melakukan validasi input panjang dan inline di controller.
   - Melakukan sanitasi data manual dengan PHP native.
   - Efek: Controller menjadi sangat panjang, sulit di-unit test, dan melanggar prinsip Single Responsibility.

2. **`02-clean-controller-formrequest.php`**:
   - Refactoring berstandar arsitektur bersih (*Clean Code*).
   - Otorisasi dipindahkan ke Middleware / Form Request `authorize()`.
   - Sanitasi data dipindahkan ke `prepareForValidation()`.
   - Aturan validasi terisolasi di `rules()` dan pesan kustom di `messages()`.
   - Controller menjadi ramping (*Skinny Controller*) dan hanya fokus menerima data valid untuk diteruskan.
