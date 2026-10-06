# TUGAS PRAKTIKUM 01
## Topik: Refactoring Legacy Code & Analisis Request Lifecycle

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih kepekaan mahasiswa dalam membedakan kode legacy prosedural yang berantakan (*spaghetti code*) dengan kode berstandar modern berbasis prinsip *Clean Code* dan keamanan aplikasi web.

---

### II. SOAL PENUGASAN

#### Bagian A: Code Refactoring (Bobot 60%)
Diberikan cuplikan kode sistem kasir warung kopi sederhana berikut yang ditulis dengan PHP native:

```php
<?php
// koneksi.php
$db = mysqli_connect("localhost", "root", "", "db_kopi");

$kode = $_POST['kode_produk'];
$jumlah = $_POST['qty'];

// Mengambil harga produk
$q = mysqli_query($db, "SELECT * FROM produk WHERE kode = '$kode'");
$p = mysqli_fetch_array($q);

$total = $p['harga'] * $jumlah;

if ($total > 100000) {
    $diskon = 0.1 * $total;
} else if ($total > 50000) {
    $diskon = 0.05 * $total;
} else {
    $diskon = 0;
}

$bayar = $total - $diskon;

mysqli_query($db, "INSERT INTO transaksi VALUES ('', '$kode', '$jumlah', '$bayar')");
echo "Berhasil bayar: Rp " . $bayar;
?>
```

**Instruksi Refactoring:**
1. Temukan minimal **3 kelemahan/risiko keamanan fatal** dari kode di atas.
2. Tulis ulang kode tersebut menggunakan **PHP 8.x Modern**:
   - Gunakan PDO dengan **Prepared Statements** (mencegah SQL Injection).
   - Buat class DTO / Model representasi produk (`ProductDto`) dengan **Constructor Property Promotion**.
   - Gunakan fitur **Match Expression** untuk kalkulasi diskon atau status order.
   - Pisahkan logika perhitungan & transaksi dari proses *output/echo*.

---

#### Bagian B: Analisis Alur Request Lifecycle Laravel (Bobot 40%)
1. Buatlah diagram alur (flowchart) ringkas atau deskripsi bertahap ketika pengguna menekan tombol submit form login pada URL `POST /login` di aplikasi Laravel hingga response berhasil dikembalikan.
2. Jelaskan peran file berikut dalam siklus request tersebut:
   - `public/index.php`
   - `bootstrap/app.php`
   - `app/Http/Middleware/...`
   - `routes/web.php`

---

### III. KETENTUAN PENGUMPULAN
1. Tugas diunggah ke repositori GitHub pribadi masing-masing mahasiswa dengan format penamaan repo: `weblanjut-tugas-01-<nim>`.
2. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*) eksekusi kode di terminal/browser.
3. Batas waktu pengumpulan: H-1 sebelum perkuliahan pertemuan 2 dimulai.
