# MODUL PRAKTIKUM 05 (EDISI LENGKAP & REVISI)
## Topik: Enterprise Architecture: Service Layer, DTO, Repository Pattern & Dependency Injection

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Mendiagnosis dan mengeliminasi anti-pattern **Fat Controller** dengan menerapkan prinsip *Single Responsibility Principle* (SRP).
2. Membangun **Data Transfer Objects (DTO)** bertipe ketat (*strongly-typed*) menggunakan fitur modern PHP 8.x (*readonly class & named constructors*).
3. Merancang dan mengimplementasikan **Service Layer** murni yang terisolasi dari lapisan HTTP (*HTTP-agnostic*).
4. Menerapkan **Repository Pattern** untuk memisahkan logika persistensi kueri basis data dari logika proses bisnis.
5. Memanfaatkan **Laravel Service Container**: perbedaan binding `bind()`, `singleton()`, dan `scoped()`.
6. Mengeliminasi boilerplate `try-catch` pada Controller menggunakan **Renderable Domain Exceptions**.

---

### II. TEORI & KONSEP KUNCI

#### 1. Masalah "Fat Controller": Mengapa Berbahaya di Skala Enterprise?

Pada aplikasi pemula, sebuah method Controller sering kali menampung semua hal:
```
[HTTP Controller]
  ├── Validasi Input
  ├── Pengecekan Hak Akses
  ├── Kalkulasi Diskon & Biaya
  ├── Manipulasi Database (Order, Items, Stok)
  ├── Panggilan API Payment Gateway Pihak Ketiga
  ├── Pengiriman Email & Notifikasi
  └── Pembuatan Response Redirect / JSON
```

**Dampak Buruk Arsitektur Ini:**
1. **Tidak Reusable:** Jika fitur yang sama ingin dipanggil dari **Mobile API**, **Cron Job / CLI Artisan**, atau **Background Queue**, kodenya terpaksa di-*copy-paste*.
2. **Sangat Sulit Diuji (*Untestable*):** Anda tidak bisa menguji alur kalkulasi diskon tanpa harus memalsukan (*mocking*) seluruh siklus HTTP Request.
3. **Melanggar Prinsip SOLID:** Terutama *Single Responsibility Principle* (Controller memiliki terlalu banyak alasan untuk berubah).

---

#### 2. Pola Arsitektur Berlapis Lengkap (Enterprise Layered Architecture)

Standar pemisahan tanggung jawab pada aplikasi enterprise:

```
[HTTP Request] ────────> [Form Request]           (Validasi & Otorisasi HTTP)
                              │
                              ▼
                         [Controller]             (Menerima Request, Mengubah ke DTO)
                              │
                              ▼ (Passing DTO)
                         [Service Layer]          (Business Logic Murni, DB Transaction)
                              │
                              ▼ (Panggil Repository)
                    [Repository Interface]        (Abstraksi Akses Data)
                              │
                              ▼
                    [Eloquent Repository]         (Kueri ORM ke Database)
```

---

#### 3. Data Transfer Objects (DTO)

> [!WARNING]
> **Bahaya Mengirim Array Asosiatif Antar-Layer:**  
> Menulis `$data['jml_bayar']` rawan kesalahan ketik (*typo*), tidak memiliki *type-hinting*, dan IDE tidak dapat memberikan *auto-complete*.

DTO adalah struktur data immutable yang bertipe ketat (*strongly-typed*):

```php
namespace App\DTOs;

use App\Http\Requests\EnrollmentRequest;

readonly class EnrollmentData
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public string $paymentMethod,
        public ?string $couponCode = null,
    ) {}

    // Named Constructor: Mengubah Form Request menjadi DTO
    public static function fromRequest(EnrollmentRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            courseId: (int) $request->validated('course_id'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code'),
        );
    }
}
```

---

#### 4. Service Layer Pattern: Aturan Emas

> [!IMPORTANT]
> **Aturan Emas Service Layer (HTTP-Agnostic):**
> 1. **Dilarang keras meng-injeksi `Request $request` ke dalam Service!** Service hanya menerima DTO atau tipe data primitif.
> 2. **Dilarang keras memanggil helper `response()`, `redirect()`, atau `session()` di dalam Service!**
> 3. Service hanya bertugas mengeksekusi logika bisnis, memanipulasi database, dan me-return Model / DTO / boolean atau melempar Exception jika gagal.

---

#### 5. Repository Pattern: Abstraksi Data Persistence

Repository Pattern memisahkan logika proses bisnis (Service) dari detail kueri database (ORM).

1. **Repository Interface (Kontrak):**
```php
namespace App\Contracts\Repositories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

interface CourseRepositoryInterface
{
    public function findActive(int $id): ?Course;
    public function getPopularCourses(int $limit = 10): Collection;
}
```

2. **Implementasi Eloquent Repository:**
```php
namespace App\Repositories;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function findActive(int $id): ?Course
    {
        return Course::where('id', $id)
            ->where('status', 'published')
            ->first();
    }

    public function getPopularCourses(int $limit = 10): Collection
    {
        return Course::published()
            ->withCount('comments')
            ->orderByDesc('views_count')
            ->take($limit)
            ->get();
    }
}
```

> [!TIP]
> **Kapan Repository Pattern Tepat vs Over-Engineering?**
> - **Tepat:** Pada aplikasi skala menengah-besar, ketika kueri database sangat kompleks, saat menerapkan caching decorator, atau saat menerapkan Domain-Driven Design (DDD).
> - **Over-Engineering:** Jika repository hanya membungkus kueri standar seperti `$this->model->find($id)` atau `$this->model->all()`. Eloquent sendiri sudah merupakan Active Record ORM yang sangat kuat.

---

#### 6. Inversion of Control (IoC): `bind()` vs `singleton()` vs `scoped()`

Pendaftaran di `app/Providers/AppServiceProvider.php`:
- **`$this->app->bind()`**: Membuat instance baru setiap kali di-inject (cocok untuk service stateless).
  ```php
  $this->app->bind(PaymentGatewayInterface::class, MidtransPaymentGateway::class);
  ```
- **`$this->app->singleton()`**: Membuat instance sekali, lalu di-cache dan digunakan kembali sepanjang request lifecycle (cocok untuk service koneksi API, logger, atau driver stateful).
  ```php
  $this->app->singleton(CourseRepositoryInterface::class, EloquentCourseRepository::class);
  ```
- **`$this->app->scoped()`**: Instance bertahan selama lifecycle satu request, namun di-reset pada request berikutnya (sangat krusial untuk Laravel Octane / Queue Worker).

---

#### 7. Renderable Domain Exceptions (Eliminasi Boilerplate `try-catch`)

Alih-alih menulis blok `try-catch` berulang-ulang di setiap method Controller, buatlah **Custom Renderable Exception**:

```php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseAlreadyEnrolledException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $this->getMessage(),
        ], 422);
    }
}
```
Ketika Service melempar `throw new CourseAlreadyEnrolledException("Anda sudah terdaftar!");`, Laravel secara otomatis merender respons JSON HTTP 422 tanpa perlu blok `try-catch` di Controller!

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Merancang Kontrak Repository & Payment Gateway
Buat berkas kontrak di `app/Contracts/PaymentGatewayInterface.php`:
```php
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount, string $method): string;
}
```

Buat implementasi dummy di `app/Services/Payment/DummyPaymentGateway.php`:
```php
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;

class DummyPaymentGateway implements PaymentGatewayInterface
{
    public function charge(int $amount, string $method): string
    {
        return 'PAY-DUMMY-' . strtoupper(\Str::random(8));
    }
}
```

Buat kontrak repository di `app/Contracts/Repositories/CourseRepositoryInterface.php`:
```php
namespace App\Contracts\Repositories;

use App\Models\Course;

interface CourseRepositoryInterface
{
    public function findPublished(int $id): ?Course;
}
```

Buat implementasi di `app/Repositories/EloquentCourseRepository.php`:
```php
namespace App\Repositories;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Course;

class EloquentCourseRepository implements CourseRepositoryInterface
{
    public function findPublished(int $id): ?Course
    {
        return Course::where('id', $id)->where('status', 'published')->first();
    }
}
```

Daftarkan seluruh binding di `app/Providers/AppServiceProvider.php`:
```php
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DummyPaymentGateway;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Repositories\EloquentCourseRepository;

public function register(): void
{
    $this->app->bind(PaymentGatewayInterface::class, DummyPaymentGateway::class);
    $this->app->singleton(CourseRepositoryInterface::class, EloquentCourseRepository::class);
}
```

---

#### Langkah 2: Membuat Data Transfer Object (DTO)
Buat berkas `app/DTOs/EnrollmentData.php`:
```php
namespace App\DTOs;

readonly class EnrollmentData
{
    public function __construct(
        public int $userId,
        public int $courseId,
        public string $paymentMethod,
        public ?string $couponCode = null
    ) {}
}
```

---

#### Langkah 3: Membuat Renderable Domain Exception
Buat berkas `app/Exceptions/EnrollmentBusinessException.php`:
```php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentBusinessException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $this->getMessage(),
        ], 422);
    }
}
```

---

#### Langkah 4: Membuat Service Layer
Buat berkas `app/Services/CourseEnrollmentService.php`:
```php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\DTOs\EnrollmentData;
use App\Exceptions\EnrollmentBusinessException;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentService
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function enroll(EnrollmentData $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1. Ambil Kursus via Repository
            $course = $this->courseRepository->findPublished($data->courseId);
            if (!$course) {
                throw new EnrollmentBusinessException("Kursus tidak ditemukan atau belum dipublikasikan.");
            }

            // 2. Cek Duplikasi
            $alreadyEnrolled = Enrollment::where('user_id', $data->userId)
                ->where('course_id', $data->courseId)
                ->exists();

            if ($alreadyEnrolled) {
                throw new EnrollmentBusinessException("Anda sudah terdaftar di kursus ini.");
            }

            // 3. Kalkulasi Diskon Kupon
            $finalPrice = $course->price;
            if ($data->couponCode === 'HEMAT50') {
                $finalPrice = (int) ($course->price * 0.5);
            }

            // 4. Pembayaran via Payment Gateway
            $paymentRef = $this->paymentGateway->charge($finalPrice, $data->paymentMethod);

            // 5. Simpan Transaksi Pendaftaran
            return Enrollment::create([
                'user_id'           => $data->userId,
                'course_id'         => $data->courseId,
                'amount_paid'       => $finalPrice,
                'payment_reference' => $paymentRef,
                'status'            => 'active',
            ]);
        });
    }
}
```

---

#### Langkah 5: Membuat Skinny Controller Bersih (Tanpa `try-catch`)
Buat controller invokable `app/Http/Controllers/EnrollCourseController.php`:
```php
namespace App\Http\Controllers;

use App\DTOs\EnrollmentData;
use App\Http\Requests\EnrollCourseRequest;
use App\Services\CourseEnrollmentService;
use Illuminate\Http\JsonResponse;

class EnrollCourseController extends Controller
{
    public function __construct(
        private CourseEnrollmentService $enrollmentService
    ) {}

    public function __invoke(EnrollCourseRequest $request): JsonResponse
    {
        $dto = new EnrollmentData(
            userId: (int) auth()->id(),
            courseId: (int) $request->validated('course_id'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code')
        );

        // Panggil Service secara langsung! Exception otomatis di-render oleh Laravel
        $enrollment = $this->enrollmentService->enroll($dto);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pendaftaran kursus berhasil diproses.',
            'data'    => $enrollment,
        ], 201);
    }
}
```
*Hasil:* Controller ini hanya **12 baris kode**, bebas `try-catch` kotor, sangat mudah dibaca, dan 100% patuh pada prinsip *Single Responsibility*.

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-05.md](TUGAS-05.md).
