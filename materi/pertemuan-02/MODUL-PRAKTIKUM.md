# MODUL PRAKTIKUM 02 (EDISI LENGKAP & REVISI)
## Topik: Advanced Routing, Controllers Pattern, Form Request Validation, & Middleware Pipeline

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang routing aplikasi web kompleks menggunakan **Implicit & Explicit Route Model Binding**, **Scoped Binding**, **Subdomain Routing**, dan **Route Groups**.
2. Mengimplementasikan pola Controller modern: **Resource Controller (Shallow Nesting)** dan **Single Action (Invokable) Controller**.
3. Membangun validasi data terisolasi menggunakan **Form Request**, sanitasi pra-validasi (`prepareForValidation`), serta membuat **Custom Validation Rule Object** (`php artisan make:rule`).
4. Mengimplementasikan **Custom Middleware dengan Parameter** dan **Terminable Middleware** di Laravel 11/12 via `bootstrap/app.php`.
5. Memahami mekanisme *Content Negotiation* (perbedaan respons validasi HTTP 302 Redirect pada Browser vs HTTP 422 JSON pada API/Postman).

---

### II. TEORI & KONSEP KUNCI

#### 1. Advanced Routing di Laravel

##### A. Implicit vs Explicit Route Model Binding
- **Implicit Binding:** Laravel otomatis menginjeksi instance Model berdasarkan nama parameter dan tipe data. Secara default mencari berdasarkan kolom `id` atau kolom kustom:
  ```php
  // Mencocokkan wildcard {course} dengan kolom 'slug' di tabel courses
  Route::get('/courses/{course:slug}', [CourseController::class, 'show']);
  ```
- **Explicit Binding:** Didefinisikan secara eksplisit di `app/Providers/AppServiceProvider.php` method `boot()`. Berguna jika Anda ingin kueri kustom atau format ID terenkripsi (misal: Hashids):
  ```php
  // app/Providers/AppServiceProvider.php
  use App\Models\User;
  use Illuminate\Support\Facades\Route;

  public function boot(): void
  {
      Route::bind('user_custom', function (string $value) {
          return User::where('uuid', $value)->where('is_active', true)->firstOrFail();
      });
  }
  ```

##### B. Scoped Bindings (Mencegah Celah Keamanan IDOR)
Ketika ada relasi bertingkat (misal: Course memiliki banyak Lesson), scoped binding menjamin bahwa lesson ID yang diakses benar-benar milik course tersebut:
```php
// Otomatis 404 jika lesson bukan milik course yang bersangkutan
Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->scopeBindings();
```

##### C. Subdomain Routing
Berguna untuk aplikasi multi-tenant atau portal khusus:
```php
Route::domain('{kampus}.portal-akademik.test')->group(function () {
    Route::get('/info', [TenantController::class, 'index']);
});
```

##### D. Fallback Routes
Menangani URL yang tidak ditemukan dengan tampilan ramah pengguna:
```php
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
```

---

#### 2. Controllers Pattern: Kapan Resource vs Invokable?

- **Resource Controller:** Cocok untuk entitas CRUD standar (7 aksi: `index, create, store, show, edit, update, destroy`).
  ```php
  // Gunakan shallow() agar URI nested tidak bertele-tele
  Route::resource('courses.reviews', CourseReviewController::class)->shallow();
  ```
- **Single Action (Invokable) Controller:** Pilihan terbaik untuk proses transaksi, kalkulasi, atau aksi tunggal kompleks (misal: `CheckoutController`, `SubmitProposalController`, `ActivateAccountController`).
  ```bash
  php artisan make:controller ApplyScholarshipController --invokable
  ```
  Controller hanya memiliki method `__invoke()`, sehingga sangat fokus (*Single Responsibility Principle*).

---

#### 3. Form Request Validation & Custom Validation Rules

##### A. Anatomi Kelas Form Request
```bash
php artisan make:request StoreStudentRequest
```

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    // 1. Otorisasi Request
    public function authorize(): bool
    {
        return $this->user()?->can('create-student') ?? false;
    }

    // 2. Sanitasi Pra-Validasi (Mengubah input sebelum divalidasi)
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nim'   => strtoupper(trim((string) $this->nim)),
            'email' => strtolower(trim((string) $this->email)),
        ]);
    }

    // 3. Aturan Validasi
    public function rules(): array
    {
        return [
            'nim'   => ['required', 'string', 'size:14', 'unique:students,nim'],
            'email' => ['required', 'email:rfc,dns', 'unique:students,email'],
            'ipk'   => ['required', 'numeric', 'between:0.00,4.00'],
        ];
    }

    // 4. Pesan Error Ramah Pengguna
    public function messages(): array
    {
        return [
            'nim.size'     => 'Format NIM harus tepat 14 karakter.',
            'ipk.between'  => 'Nilai IPK harus berada pada rentang 0.00 hingga 4.00.',
        ];
    }
}
```

##### B. Membuat Custom Validation Rule Object
Jika aturan validasi tidak bisa diakomodasi oleh validator bawaan (misal: validasi nomor WhatsApp berformat Indonesia atau cek format NIM kampus tertentu):
```bash
php artisan make:rule ValidIndonesianPhone
```

Isi berkas `app/Rules/ValidIndonesianPhone.php`:
```php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidIndonesianPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nomor HP Indonesia wajib diawali 08 atau 628 dan panjang 10-15 digit
        if (!preg_match('/^(08|628)[0-9]{8,13}$/', (string) $value)) {
            $fail(':attribute harus berupa nomor telepon Indonesia yang valid (contoh: 08123456789).');
        }
    }
}
```

Gunakan di Form Request:
```php
'no_hp' => ['required', new \App\Rules\ValidIndonesianPhone],
```

---

#### 4. Custom Middleware Pipeline di Laravel 11/12

##### A. Middleware dengan Parameter (Multi-Role Guard)
```bash
php artisan make:middleware CheckRole
```

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Middleware dapat menerima argumen dinamis (...$roles)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk halaman ini.');
        }

        return $next($request);
    }
}
```

##### B. Terminable Middleware (Post-Response Tasks)
Terminable middleware mengeksekusi kode **setelah** response dikirim ke browser pengguna (sangat berguna untuk logging audit trail atau pencatatan metrik performa tanpa memperlambat loading user):
```php
public function terminate(Request $request, Response $response): void
{
    // Dijalankan setelah browser menerima response
    \Log::info("Akses rute: {$request->path()} dengan status {$response->getStatusCode()}");
}
```

##### C. Pendaftaran Alias Middleware di `bootstrap/app.php` (Laravel 11/12):
```php
// bootstrap/app.php
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    // ...
```

Penggunaan pada Route:
```php
// Hanya role 'admin' atau 'kaprodi' yang boleh mengakses
Route::get('/laporan-akademik', [ReportController::class, 'index'])->middleware('role:admin,kaprodi');
```

---

#### 5. Jebakan Klasik Pengujian: Browser vs Postman (Content Negotiation)

> [!IMPORTANT]
> **Mengapa saat uji coba POST di Postman terjadi Redirect 302 ke halaman login/beranda alih-alih menampilkan error validasi?**
> - **Pada Web Browser:** Laravel mengasumsikan request berasal dari form HTML biasa, sehingga saat validasi gagal, Laravel otomatis melakukan `redirect()->back()` dengan membawa sesi flash `$errors` dan `old()`.
> - **Pada Postman / API Client:** Anda **WAJIB** menambahkan header HTTP:
>   `Accept: application/json`
>   Dengan header ini, Laravel langsung mengembalikan respons standar API: **HTTP Status 422 (Unprocessable Content)** beserta detail error berformat JSON!

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Pelajari Contoh Studi Kasus
Periksa berkas pada subfolder `studi-kasus/`:
1. [01-fat-controller-bad.php](studi-kasus/01-fat-controller-bad.php): Controller gemuk yang melanggar prinsip desain.
2. [02-clean-controller-formrequest.php](studi-kasus/02-clean-controller-formrequest.php): Refactoring bersih dengan Form Request & Invokable Controller.

---

#### Langkah 2: Membuat Invokable Controller
```bash
php artisan make:controller ApplyScholarshipController --invokable
```
Buka `app/Http/Controllers/ApplyScholarshipController.php`:
```php
namespace App\Http\Controllers;

use App\Http\Requests\ScholarshipApplicationRequest;
use Illuminate\Http\JsonResponse;

class ApplyScholarshipController extends Controller
{
    public function __invoke(ScholarshipApplicationRequest $request): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran beasiswa berhasil diterima!',
            'data'    => $request->validated(),
        ], 201);
    }
}
```

---

#### Langkah 3: Membuat Form Request & Custom Rule
Buat Form Request:
```bash
php artisan make:request ScholarshipApplicationRequest
```

Buka `app/Http/Requests/ScholarshipApplicationRequest.php`:
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScholarshipApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nim'          => strtoupper(trim((string) $this->nim)),
            'nama_lengkap' => ucwords(trim((string) $this->nama_lengkap)),
        ]);
    }

    public function rules(): array
    {
        return [
            'nim'             => ['required', 'string', 'size:14'],
            'nama_lengkap'    => ['required', 'string', 'min:3'],
            'ipk'             => ['required', 'numeric', 'between:3.00,4.00'],
            'penghasilan_ortu'=> ['required', 'numeric', 'min:0'],
            'semester'        => ['required', 'integer', 'between:3,8'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.size'         => 'NIM mahasiswa harus tepat 14 karakter.',
            'ipk.between'      => 'Syarat minimal IPK penerima beasiswa adalah 3.00.',
            'semester.between' => 'Pendaftaran beasiswa hanya terbuka untuk semester 3 s/d 8.',
        ];
    }
}
```

---

#### Langkah 4: Membuat Blade Form Pengujian
Buat file `resources/views/scholarship/form.blade.php`:
```html
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pendaftaran Beasiswa</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; max-width: 500px; margin: auto; }
        .error { color: red; font-size: 0.875rem; }
        .field { margin-bottom: 1rem; }
        input { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <h2>Formulir Pendaftaran Beasiswa</h2>

    @if(session('success'))
        <div style="color: green; margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <form action="{{ route('beasiswa.daftar') }}" method="POST">
        @csrf

        <div class="field">
            <label>NIM (14 Karakter):</label>
            <input type="text" name="nim" value="{{ old('nim') }}">
            @error('nim') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Nama Lengkap:</label>
            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}">
            @error('nama_lengkap') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>IPK Terakhir (3.00 - 4.00):</label>
            <input type="text" name="ipk" value="{{ old('ipk') }}">
            @error('ipk') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Penghasilan Orang Tua (Rp):</label>
            <input type="number" name="penghasilan_ortu" value="{{ old('penghasilan_ortu') }}">
            @error('penghasilan_ortu') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label>Semester (3 - 8):</label>
            <input type="number" name="semester" value="{{ old('semester') }}">
            @error('semester') <div class="error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" style="padding: 0.75rem 1.5rem; background: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Kirim Pendaftaran
        </button>
    </form>
</body>
</html>
```

Daftarkan rute di `routes/web.php`:
```php
use App\Http\Controllers\ApplyScholarshipController;

Route::get('/beasiswa', function () {
    return view('scholarship.form');
})->name('beasiswa.form');

Route::post('/beasiswa/daftar', ApplyScholarshipController::class)
    ->name('beasiswa.daftar');
```

---

#### Langkah 5: Pengujian Dua Skenario
1. **Pengujian Browser:** Akses `http://127.0.0.1:8000/beasiswa`, kirim data kosong, dan amati pesan error Bahasa Indonesia serta repopulasi data `old()`.
2. **Pengujian Postman:** Kirim request `POST http://127.0.0.1:8000/beasiswa/daftar` dengan menyertakan header `Accept: application/json`. Amati bahwa respons mengembalikan status **HTTP 422 Unprocessable Content** berformat JSON.

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-02.md](TUGAS-02.md).
