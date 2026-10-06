# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 01: Refactoring Legacy Code & Analisis Request Lifecycle
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-01.md](TUGAS-01.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan link repositori GitHub aktif dengan struktur commit rapi.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Code Refactoring Kasir Warung Kopi (Maks. 60 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Identifikasi Masalah Keamanan** | 15 Poin | Mahasiswa mampu menyebutkan & menjelaskan minimal 3 kelemahan fatal:<br>1. **SQL Injection** pada `$_POST['kode_produk']`.<br>2. **Pencampuran Concern** (Query, kalkulasi diskon, dan echo berada di 1 file).<br>3. Tidak ada validasi tipe data pada kuantitas/harga (rawan NaN / error fatal). |
| **Mitigasi SQL Injection (PDO Prepared Statement)** | 15 Poin | Mahasiswa menggunakan PDO atau MySQLi dengan **Prepared Statements (`prepare` & `execute`)**, bukan sekadar string concatenation. |
| **Penerapan Fitur Modern PHP 8.x** | 15 Poin | Menerapkan minimal 2 fitur modern:<br>- Constructor Property Promotion pada DTO/Model.<br>- Match Expression untuk penentuan diskon.<br>- Strict types (`declare(strict_types=1)`). |
| **Clean Architecture & Separation of Concerns** | 15 Poin | Memisahkan alur logika kalkulasi belanja dari logika penyimpanan database. Kode tidak lagi mencetak `echo` sembarangan di tengah proses transaksi. |

---

#### BAGIAN B: Analisis Alur Request Lifecycle Laravel (Maks. 40 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Ketepatan Diagram/Deskripsi Alur** | 20 Poin | Menjelaskan urutan dengan tepat:<br>`Client Request -> public/index.php -> bootstrap/app.php -> Middleware Stack -> Route web.php -> Login Controller -> Response`. |
| **Penjelasan Peran Komponen Kunci** | 20 Poin | Penjelasan akurat untuk:<br>1. `public/index.php`: Pintu masuk tunggal (*Single Entry Point*).<br>2. `bootstrap/app.php`: Pusat konfigurasi kernel modern (routing, middleware, exception).<br>3. `Middleware`: Filter validasi request (CSRF token, enkripsi cookie, session).<br>4. `routes/web.php`: Dispatcher penentu handler controller tujuan. |

---

### III. KUNCI JAWABAN STANDAR REFERENSI (BAGIAN A)

Berikut adalah contoh solusi referensi yang layak mendapat nilai sempurna (A / 100):

```php
<?php

declare(strict_types=1);

// 1. DTO Produk dengan Constructor Property Promotion
readonly class ProductDto {
    public function __construct(
        public string $code,
        public string $name,
        public int $price
    ) {}
}

// 2. Layanan Kalkulator Diskon dengan Match Expression
class DiscountCalculator {
    public static function calculate(int $total): int {
        return (int) match(true) {
            $total > 100_000 => $total * 0.10,
            $total > 50_000  => $total * 0.05,
            default          => 0,
        };
    }
}

// 3. Repository Transaksi dengan PDO Prepared Statements
class OrderService {
    public function __construct(private PDO $db) {}

    public function processCheckout(string $productCode, int $qty): int {
        // Ambil data produk secara aman
        $stmt = $this->db->prepare("SELECT kode, nama, harga FROM produk WHERE kode = :code LIMIT 1");
        $stmt->execute(['code' => $productCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new InvalidArgumentException("Produk tidak ditemukan.");
        }

        $product = new ProductDto(
            code: $row['kode'],
            name: $row['nama'],
            price: (int) $row['harga']
        );

        $totalBelanja = $product->price * $qty;
        $diskon = DiscountCalculator::calculate($totalBelanja);
        $totalBayar = $totalBelanja - $diskon;

        // Simpan transaksi via prepared statement
        $insert = $this->db->prepare("
            INSERT INTO transaksi (kode_produk, qty, total_bayar) 
            VALUES (:kode, :qty, :bayar)
        ");
        $insert->execute([
            'kode'  => $product->code,
            'qty'   => $qty,
            'bayar' => $totalBayar
        ]);

        return $totalBayar;
    }
}
```

---

### IV. PANDUAN PENGURANGAN NILAI (PENALTY)
- Keterlambatan pengumpulan: Pengurangan 10 poin per hari terlambat.
- Tidak menyertakan tangkapan layar eksekusi kode di `README.md`: Pengurangan 15 poin.
- Kode identik (*copy-paste*) antar-mahasiswa: **Nilai 0 (Plagiarisme)**.
