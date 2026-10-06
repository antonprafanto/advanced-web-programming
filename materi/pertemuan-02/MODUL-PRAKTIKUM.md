# MODUL PRAKTIKUM 02
## Topik: Advanced Routing, Controllers Pattern & Form Request Validation

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang routing aplikasi web yang terstruktur menggunakan **Route Model Binding**, **Scoped Binding**, **Prefix**, dan **Route Groups**.
2. Mengimplementasikan pola Controller modern: **Resource Controller**, **Nested Resources**, dan **Single Action (Invokable) Controller**.
3. Mengisolasi logika validasi input dari Controller menggunakan **Form Request** mandiri (*authorize, rules, messages, dan sanitasi data*).
4. Membuat, mengonfigurasi, dan menerapkan **Custom Middleware** pada arsitektur Laravel 11/12 via `bootstrap/app.php`.

---

### II. TEORI & KONSEP KUNCI

#### 1. Advanced Routing di Laravel

##### A. Implicit & Custom Route Model Binding
Alih-alih mencari model secara manual (`Product::findOrFail($id)`), Laravel dapat langsung menginjeksi instance Model berdasarkan parameter wildcard di URL.

Secara default, Laravel mencocokkan wildcard dengan kolom `id`. Kita dapat mengubahnya ke kolom lain (misal: `slug` atau `uuid`):

```php
// Mengikat parameter otomatis berdasarkan kolom 'slug'
Route::get('/courses/{course:slug}', [CourseController::class, 'show']);
```

##### B. Scoped Bindings (Nested Model Binding)
Untuk relasi bertingkat, scoped binding memastikan bahwa model anak benar-benar dimiliki oleh model induk (mencegah *Broken Object Level Authorization* / IDOR):

```php
// Otomatis memvalidasi bahwa $lesson adalah milik $course
Route::get('/courses/{course}/lessons/{lesson}', [LessonController::class, 'show'])->scopeBindings();
```

##### C. Fallback Routes
Menangani URL yang tidak terdaftar dengan halaman 404 kustom yang elegan:
```php
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
```

---

#### 2. Controllers Pattern: Resource vs Invokable

##### A. Resource Controller & Shallow Nesting
Resource controller menyediakan 7 aksi standar RESTful (`index, create, store, show, edit, update, destroy`).

Untuk relasi bertingkat, gunakan **shallow nesting** agar URI tidak terlalu panjang:
```php
// URL: /posts/{post}/comments (index, create, store)
// URL: /comments/{comment} (show, edit, update, destroy)
Route::resource('posts.comments', CommentController::class)->shallow();
```

##### B. Single Action / Invokable Controller
Jika sebuah proses memiliki alur bisnis spesifik dan kompleks (misal: proses pembayaran, ekspor laporan, atau aktivasi akun), jangan tumpuk ke dalam controller biasa. Gunakan **Invokable Controller**:

```bash
php artisan make:controller CheckoutOrderController --invokable
```

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutOrderController extends Controller
{
    public function __invoke(Request $request)
    {
        // Controller ini hanya memiliki SATU tanggung jawab tunggal (Single Responsibility)
        return response()->json(['message' => 'Pesanan berhasil diproses.']);
    }
}
```

---

#### 3. Form Request Validation (Separation of Concerns)

> [!CAUTION]
> **Anti-Pattern:** Melakukan validasi inline puluhan baris di dalam Controller (`$request->validate([...])`) adalah penyebab utama controller menjadi kotor dan sulit diuji (*Fat Controller*).

Pindahkan seluruh aturan validasi ke kelas **Form Request**:
```bash
php artisan make:request StoreStudentRequest
```

Anatomi kelas Form Request:
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    // 1. Otorisasi: Apakah pengguna saat ini berhak mengirim request ini?
    public function authorize(): bool
    {
        return true; // Atau logika pengecekan hak akses pengguna
    }

    // 2. Pra-pemrosesan Data (Sanitasi sebelum divalidasi)
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim($this->email)),
            'phone' => preg_replace('/[^0-9]/', '', $this->phone),
        ]);
    }

    // 3. Aturan Validasi
    public function rules(): array
    {
        return [
            'nim'   => ['required', 'string', 'size:14', 'unique:students,nim'],
            'name'  => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email:rfc,dns', 'unique:students,email'],
            'gpa'   => ['required', 'numeric', 'between:0.00,4.00'],
        ];
    }

    // 4. Kustomisasi Pesan Error ke Bahasa Indonesia
    public function messages(): array
    {
        return [
            'nim.required'   => 'NIM wajib diisi.',
            'nim.unique'     => 'NIM ini sudah terdaftar di sistem.',
            'gpa.between'    => 'IPK harus berada di rentang 0.00 hingga 4.00.',
        ];
    }
}
```

Di Controller, Anda cukup men-typehint Form Request tersebut:
```php
public function store(StoreStudentRequest $request)
{
    // Jika sampai di baris ini, data DIJAMIN 100% SUDAH VALID!
    $validatedData = $request->validated();
    
    Student::create($validatedData);

    return redirect()->route('students.index')->with('success', 'Data mahasiswa berhasil disimpan.');
}
```

---

#### 4. Custom Middleware di Laravel 11/12

Middleware berfungsi sebagai lapisan penyaring (*HTTP filter*) sebelum request menyentuh controller.

##### Langkah Membuat Middleware:
```bash
php artisan make:middleware EnsureProfileIsComplete
```

Isi berkas `app/Http/Middleware/EnsureProfileIsComplete.php`:
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika user belum mengisi nomor telepon, arahkan ke halaman lengkapi profil
        if ($user && empty($user->phone_number)) {
            return redirect()->route('profile.edit')
                ->with('warning', 'Harap lengkapi nomor telepon profil Anda terlebih dahulu.');
        }

        return $next($request);
    }
}
```

##### Pendaftaran Middleware di Laravel 11 / 12 (`bootstrap/app.php`):
Pada Laravel 11/12, middleware didaftarkan melalui method `withMiddleware` di `bootstrap/app.php`:

```php
// bootstrap/app.php
use App\Http\Middleware\EnsureProfileIsComplete;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
        // Daftarkan alias middleware di sini:
        $middleware->alias([
            'profile.complete' => EnsureProfileIsComplete::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })->create();
```

##### Penggunaan Middleware pada Route Group:
```php
Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/scholarship/apply', [ScholarshipController::class, 'create'])->name('scholarship.apply');
});
```

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

Gunakan project latihan `praktikum-01-weblanjut` yang telah dibuat pada pertemuan sebelumnya atau buat baru.

#### Langkah 1: Pelajari Berkas Studi Kasus
Amati perbandingan kode pada folder `studi-kasus/`:
1. [01-fat-controller-bad.php](studi-kasus/01-fat-controller-bad.php): Contoh kode controller yang kotor.
2. [02-clean-controller-formrequest.php](studi-kasus/02-clean-controller-formrequest.php): Contoh refactoring bersih dengan Form Request & Middleware.

---

#### Langkah 2: Membuat Invokable Controller
Jalankan perintah:
```bash
php artisan make:controller ApplyScholarshipController --invokable
```
Buka file `app/Http/Controllers/ApplyScholarshipController.php`, modifikasi method `__invoke`:
```php
namespace App\Http\Controllers;

use App\Http\Requests\ScholarshipApplicationRequest;
use Illuminate\Http\JsonResponse;

class ApplyScholarshipController extends Controller
{
    public function __invoke(ScholarshipApplicationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran beasiswa berhasil diterima untuk diverifikasi.',
            'data'    => $payload,
        ], 201);
    }
}
```

---

#### Langkah 3: Membuat Form Request
Jalankan perintah:
```bash
php artisan make:request ScholarshipApplicationRequest
```
Buka `app/Http/Requests/ScholarshipApplicationRequest.php`, lengkapi aturan validasi:
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
            'nim' => strtoupper(trim($this->nim ?? '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'nim'             => ['required', 'string', 'min:8', 'max:20'],
            'nama_lengkap'    => ['required', 'string', 'min:3'],
            'ipk'             => ['required', 'numeric', 'between:3.00,4.00'],
            'penghasilan_ortu'=> ['required', 'numeric', 'min:0'],
            'semester'        => ['required', 'integer', 'between:3,8'],
        ];
    }

    public function messages(): array
    {
        return [
            'ipk.between' => 'Syarat minimal IPK untuk beasiswa ini adalah 3.00.',
            'semester.between' => 'Beasiswa hanya terbuka untuk mahasiswa semester 3 hingga 8.',
        ];
    }
}
```

---

#### Langkah 4: Mendaftarkan Route & Pengecekan
Tambahkan route di `routes/web.php`:
```php
use App\Http\Controllers\ApplyScholarshipController;

Route::post('/beasiswa/daftar', ApplyScholarshipController::class)
    ->name('beasiswa.daftar');
```

Jalankan perintah untuk memverifikasi daftar route yang aktif:
```bash
php artisan route:list --path=beasiswa
```

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-02.md](TUGAS-02.md).
