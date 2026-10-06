# MODUL PRAKTIKUM 01
## Topik: Transisi dari Native PHP ke Modern PHP 8.x & Request Lifecycle Laravel

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Mengidentifikasi kelemahan mendasar kode web PHP native (prosedural, pencampuran logika & tampilan, serta risiko keamanan umum).
2. Menerapkan fitur-fitur modern PHP 8.x (*Constructor Property Promotion*, *Match Expression*, *Nullsafe Operator*, *Named Arguments*, dan *Type Hinting*) untuk menulis kode yang bersih (*clean code*).
3. Menjelaskan secara komprehensif siklus hidup permintaan HTTP (*Request Lifecycle*) pada framework Laravel.
4. Menyiapkan dan mengonfigurasi lingkungan kerja pengembangan web modern menggunakan Composer dan Git.

---

### II. ALAT & PRASYARAT LINGKUNGAN
Sebelum memulai, pastikan perangkat telah terpasang:
- **PHP CLI**: Versi $\ge$ 8.2 (`php -v`)
- **Composer**: Dependency Manager untuk PHP (`composer -V`)
- **Node.js & NPM**: Runtime JS untuk asset bundling (`node -v` & `npm -v`)
- **Git**: Version Control System (`git --version`)
- **Code Editor**: VS Code (rekomendasi ekstensi: *PHP Intelephense*, *Laravel Extra Intellisense*) atau PhpStorm.

---

### III. TEORI & BEDAH SINTAKS MODERN PHP 8.X

PHP telah bertransformasi pesat dari bahasa skrip prosedural menjadi bahasa pemrograman berbasis objek bertipe ketat (*strictly typed OOP*). Berikut 5 fitur krusial yang wajib dikuasai:

#### 1. Constructor Property Promotion
Mengurangi boilerplate pendeklarasian properti class dan assignment berulang.

*Cara Lama (PHP 7 ke bawah):*
```php
class User {
    public string $name;
    public string $email;

    public function __construct(string $name, string $email) {
        $this->name = $name;
        $this->email = $email;
    }
}
```

*Cara Modern (PHP 8.x):*
```php
class User {
    public function __construct(
        public string $name,
        public string $email,
        public readonly int $id = 0
    ) {}
}
```

#### 2. Match Expression (Pengganti `switch-case`)
`match` mengevaluasi ekspresi secara *strict comparison* (`===`) dan langsung mengembalikan nilai (*return value*).

```php
$status = 'paid';

$badgeColor = match($status) {
    'pending' => 'yellow',
    'paid'    => 'green',
    'failed', 'cancelled' => 'red',
    default   => 'gray',
};
```

#### 3. Nullsafe Operator (`?->`)
Mencegah fatal error *"Call to a member function on null"* tanpa nested `if (!is_null(...))`.

```php
// Alih-alih:
// $country = $user !== null && $user->profile !== null ? $user->profile->country : null;

// Cukup tulis:
$country = $user?->profile?->country;
```

#### 4. Named Arguments
Mengirim parameter fungsi berdasarkan namanya tanpa perlu memedulikan urutan parameter opsional.

```php
function createUser(string $name, string $role = 'user', bool $isActive = true): void {
    // ...
}

// Memanggil fungsi dengan melewati argumen $role:
createUser(name: 'Anton', isActive: false);
```

---

### IV. ANATOMI SIKLUS HIDUP REQUEST (LARAVEL REQUEST LIFECYCLE)

Ketika user mengakses URL (misal: `https://aplikasi.test/mahasiswa`), apa yang sebenarnya terjadi di balik layar?

```
[Browser / HTTP Client]
       │
       ▼ (1. HTTP Request)
[public/index.php]  <─── Entry Point Tunggal (Single Entry Point)
       │
       ▼ (2. Autoload & Bootstrap)
[bootstrap/app.php] <─── Inisialisasi Service Container & Kernel
       │
       ▼ (3. Pipeline Global & Route Middleware)
[Middleware Stack]  <─── Verifikasi CSRF, Session, Auth, Rate Limiter
       │
       ▼ (4. Routing & Controller)
[Route] ───────────> [Controller Method]
                             │
                             ▼ (5. Business Logic & ORM)
                      [Eloquent Model / Database]
                             │
                             ▼ (6. Response Preparation)
[HTTP Response / JSON / View Blade]
       │
       ▼ (7. Kirim kembali ke User)
[Browser / HTTP Client]
```

**Poin Kunci:**
1. **Single Entry Point (`public/index.php`)**: Tidak ada lagi akses langsung ke file terpisah seperti `edit_mahasiswa.php` atau `koneksi.php`. Semua request melewati pintu gerbang yang sama.
2. **Inversion of Control (IoC) & Service Container**: Komponen framework saling terhubung secara modular, bukan melalui *hardcoded instantiation*.

---

### V. LANGKAH PRAKTIKUM MANDIRI

#### Langkah 1: Memeriksa Studi Kasus Refactoring
Buka dan pelajari dua berkas studi kasus di subfolder `studi-kasus/`:
1. [01-native-legacy.php](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/studi-kasus/01-native-legacy.php): Kode native PHP spaghetti dengan celah SQL Injection.
2. [02-modern-php8-refactored.php](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/studi-kasus/02-modern-php8-refactored.php): Kode yang telah direfaktor menggunakan OOP modern, PDO prepared statements, dan custom exception.

Jalankan skrip refactor menggunakan terminal:
```bash
php materi/pertemuan-01/studi-kasus/02-modern-php8-refactored.php
```

#### Langkah 2: Inisialisasi Project Baru Laravel
Buat project latihan Laravel di direktori lokal mahasiswa:
```bash
composer create-project laravel/laravel praktikum-web-lanjut
cd praktikum-web-lanjut
```

Jalankan web server lokal bawaan Laravel:
```bash
php artisan serve
```
Akses di browser: `http://127.0.0.1:8000`.

#### Langkah 3: Mengamati Request Lifecycle via `dd()` / `dump()`
Buka file `routes/web.php`, tambahkan route eksperimen:
```php
use Illuminate\Http\Request;

Route::get('/debug-lifecycle', function (Request $request) {
    return [
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'method'     => $request->method(),
        'php_version'=> PHP_VERSION,
        'laravel_ver'=> app()->version(),
    ];
});
```
Akses URL `http://127.0.0.1:8000/debug-lifecycle` dan amati struktur data JSON yang dikembalikan.

---

### VI. TUGAS PRAKTIKUM
Kerjakan penugasan terstruktur yang ada pada dokumen [TUGAS-01.md](file:///c:/Users/anton/vibecoding/weblanjut/materi/pertemuan-01/TUGAS-01.md).
