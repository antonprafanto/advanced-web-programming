# MODUL PRAKTIKUM 01 (EDISI LENGKAP & REVISI)
## Topik: Transisi dari Native PHP ke Modern PHP 8.x, PSR-4 Autoloading, & Request Lifecycle Laravel

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Mengidentifikasi kelemahan mendasar kode web PHP native (prosedural, pencampuran logika & tampilan, serta risiko keamanan fatal).
2. Memahami bagaimana **Composer PSR-4 Autoloading & Namespace** menggantikan ketergantungan terhadap `include` / `require_once`.
3. Menerapkan fitur-fitur esensial modern PHP 8.x (*Constructor Property Promotion, Match Expression, Nullsafe Operator, Named Arguments, Readonly Class, dan Type Hinting*).
4. Memahami struktur minimalis framework **Laravel versi terbaru (11.x/12.x)** dan siklus hidup permintaan HTTP (*Request Lifecycle*).
5. Menguasai manajemen environment (`.env`), enkripsi `APP_KEY`, serta perkakas CLI (`php artisan serve`, `route:list`, dan `tinker`).

---

### II. ALAT & PRASYARAT LINGKUNGAN
Pastikan perangkat telah terpasang:
- **PHP CLI**: Versi $\ge$ 8.2 (`php -v`)
- **Composer**: Dependency Manager untuk PHP (`composer -V`)
- **Node.js & NPM**: Runtime JS untuk asset bundling (`node -v` & `npm -v`)
- **Git**: Version Control System (`git --version`)
- **Code Editor**: VS Code (ekstensi rekomendasi: *PHP Intelephense*, *Laravel Extra Intellisense*) atau PhpStorm.

> [!WARNING]
> **Troubleshooting Pengguna Windows (XAMPP / Laragon):**
> 1. Jika muncul pesan `'php' is not recognized as an internal or external command`, pastikan folder binary PHP (misal: `C:\laragon\bin\php\php-8.x` atau `C:\xampp\php`) telah ditambahkan ke **System Environment Variables (PATH)**.
> 2. Buka `php.ini`, pastikan ekstensi berikut tidak diawali tanda titik koma (sudah aktif):
>    `extension=curl`, `extension=fileinfo`, `extension=mbstring`, `extension=openssl`, `extension=pdo_mysql`, `extension=pdo_sqlite`, `extension=zip`.

---

### III. JEMBATAN KONSEPTUAL: DARI NATIVE KE MODERN

#### 1. Masalah Ketergantungan `require` / `include` vs PSR-4 Autoloading
Di PHP Native, mahasiswa terbiasa menulis:
```php
// Kode Native Usang
require_once 'koneksi.php';
require_once 'model/Mahasiswa.php';
require_once 'helper/fungsi.php';
```
Kelemahannya: Rawan *duplicate declaration error*, jalur path relatif yang rapuh (*fragile paths*), dan beban memori jika file di-load padahal tidak digunakan.

**Solusi Modern (PSR-4 Autoloading via Composer):**
Composer memetakan *Namespace* ke struktur folder secara otomatis. Cukup panggil class via keyword `use`:
```php
namespace App\Services;

use App\Models\Student; // Otomatis diload oleh Composer dari app/Models/Student.php!
```

---

#### 2. Fitur Unggulan Modern PHP 8.x yang Wajib Dikuasai

##### A. Constructor Property Promotion
Menghilangkan boilerplate penulisan properti berulang.
```php
// PHP 8.x
class Student {
    public function __construct(
        public string $nim,
        public string $name,
        public readonly int $id = 0
    ) {}
}
```

##### B. Match Expression (Strict, Safe, dan Menghasilkan Nilai)
```php
$status = 'A';

$keterangan = match($status) {
    'A' => 'Mahasiswa Aktif',
    'C' => 'Cuti Akademik',
    'N', 'D' => 'Non-Aktif / Drop Out',
    default => 'Status Tidak Dikenal',
};
```

##### C. Nullsafe Operator (`?->`)
Mencegah fatal error *"Call to a member function on null"*:
```php
// Aman meskipun $student atau $advisor bernilai null
$dosenPembimbing = $student?->advisor?->name;
```

##### D. Named Arguments
Mengirim parameter fungsi berdasarkan nama secara fleksibel:
```php
function kirimEmailNotifikasi(string $to, string $subject, bool $urgent = false): void {
    // ...
}

// Melewatkan parameter opsional dengan aman
kirimEmailNotifikasi(to: 'budi@kampus.ac.id', subject: 'Jadwal Kuliah', urgent: true);
```

##### E. Readonly Class & Enums
```php
enum Role: string {
    case Admin = 'admin';
    case Dosen = 'dosen';
    case Mahasiswa = 'mahasiswa';
}

readonly class UserProfile {
    public function __construct(
        public string $username,
        public Role $role
    ) {}
}
```

---

### IV. ANATOMI SIKLUS HIDUP REQUEST & STRUKTUR BARU LARAVEL (11.x / 12.x)

> [!IMPORTANT]
> **Catatan Arsitektur Laravel Terbaru:**
> Mulai Laravel 11.x, direktori aplikasi dirampingkan (*Slim Skeleton*). 
> - **TIDAK ADA LAGI** `app/Http/Kernel.php`.
> - Konfigurasi Middleware, Routing, dan Exceptions dipusatkan di file `bootstrap/app.php`.

#### Diagram Alur Request Lifecycle:
```
[User Browser / Postman]
       │
       ▼ (1. HTTP Request dikirim)
[public/index.php]  <─── Titik Masuk Tunggal (Single Entry Point)
       │
       ▼ (2. Autoload & Bootstrap Aplikasi)
[bootstrap/app.php] <─── Mengonfigurasi Routing, Middleware Pipeline, & Exception Handling
       │
       ▼ (3. Middleware Pipeline)
[Middleware Stack]  <─── Enkripsi Cookie, Verifikasi CSRF, Session, Rate Limiter
       │
       ▼ (4. Routing Engine)
[routes/web.php / api.php] ──> Menentukan Controller / Closure tujuan
       │
       ▼ (5. Business Logic & ORM)
[Controller / Service / Eloquent Model]
       │
       ▼ (6. Response Preparation)
[View Blade / JSON Payload / Inertia Response]
       │
       ▼ (7. HTTP Response balik ke Client)
[User Browser / Postman]
```

---

### V. MANAJEMEN ENVIRONMENT (`.env`) & KEAMANAN DASAR

1. **Kenapa harus `.env`?**
   - Memisahkan konfigurasi sensitif (kredensial database, API key rahasia, port mailer) dari kode sumber aplikasi.
   - **Aturan Baku:** File `.env` **HARAM** di-commit ke Git! Git hanya menyimpan template contoh yaitu `.env.example`.
2. **Peran `APP_KEY`:**
   - Dibuat via perintah `php artisan key:generate`.
   - String 32 karakter acak ini digunakan Laravel untuk mengenkripsi cookie sesi, password reset token, dan data terenkripsi lainnya. Jika key ini hilang, semua session yang tersimpan tidak akan dapat didekripsi.

---

### VI. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Uji Coba Mandiri Kode Refactoring PHP 8.x
Buka terminal pada repositori mata kuliah ini dan jalankan skrip studi kasus refactoring mandiri:
```bash
php materi/pertemuan-01/studi-kasus/02-modern-php8-refactored.php
```
Pelajari bagaimana data DTO, enum, dan query PDO prepared statements bekerja secara independen.

---

#### Langkah 2: Inisialisasi Project Baru Laravel
Buat direktori latihan terpisah:
```bash
composer create-project laravel/laravel praktikum-01-weblanjut
cd praktikum-01-weblanjut
```

Periksa informasi lingkungan instalasi:
```bash
php artisan about
```

---

#### Langkah 3: Menjalankan Server & Memeriksa Routing
Jalankan server pengembangan:
```bash
php artisan serve
```
Buka browser pada alamat `http://127.0.0.1:8000`.

Buka file `routes/web.php` dan tambahkan route baru untuk mengecek lifecycle:
```php
use Illuminate\Http\Request;

Route::get('/cek-request', function (Request $request) {
    return [
        'status'       => 'Sukses',
        'ip_pengguna'  => $request->ip(),
        'metode_http'  => $request->method(),
        'url_lengkap'  => $request->fullUrl(),
        'php_version'  => PHP_VERSION,
        'laravel_ver'  => app()->version(),
    ];
});
```
Buka terminal baru di folder yang sama, periksa daftar routing yang aktif:
```bash
php artisan route:list
```

---

#### Langkah 4: Eksplorasi Interaktif dengan `php artisan tinker`
Masuk ke mode REPL (Read-Eval-Print Loop) Laravel:
```bash
php artisan tinker
```
Coba jalankan sintaks modern langsung di dalam console tinker:
```php
// Test 1: Match expression di tinker
$kode = 'A';
match($kode) { 'A' => 'Lulus', default => 'Ulang' };

// Test 2: Enkripsi Laravel menggunakan APP_KEY
$rahasia = encrypt('PasswordSuperRahasia123');
decrypt($rahasia);

// Keluar dari tinker
exit;
```

---

### VII. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-01.md](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/TUGAS-01.md).
