# MODUL PRAKTIKUM 05
## Topik: Enterprise Architecture: Service Layer, DTO & Dependency Injection

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Mendiagnosis dan mengeliminasi anti-pattern **Fat Controller** dengan menerapkan prinsip *Single Responsibility Principle* (SRP).
2. Merancang dan mengimplementasikan **Service Layer** murni yang terisolasi dari lapisan HTTP (*HTTP-agnostic*).
3. Membangun **Data Transfer Objects (DTO)** bertipe ketat (*strongly-typed*) menggunakan fitur modern PHP 8.x (*readonly class & named constructors*).
4. Menerapkan pola **Action Classes** untuk mengenkapsulasi proses bisnis spesifik.
5. Memanfaatkan **Laravel Service Container** untuk menerapkan **Inversion of Control (IoC)** dan **Dependency Injection (DI)** berbasis Interface.

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

#### 2. Pola Arsitektur Berlapis (Layered Architecture)

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
                              ▼
                         [Eloquent Model / DB]    (Penyimpanan Data)
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

Contoh Kerangka Service Layer:
```php
namespace App\Services;

use App\DTOs\EnrollmentData;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function enroll(EnrollmentData $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            // 1. Validasi Logika Bisnis (Cek apakah sudah pernah daftar)
            // 2. Kalkulasi Biaya & Diskon Kupon
            // 3. Proses Pembayaran via Payment Gateway
            // 4. Simpan ke Database
            // 5. Kembalikan instance Enrollment
        });
    }
}
```

---

#### 5. Action Classes Pattern (Single-Action Services)

Jika sebuah Service mulai terlalu besar (*Fat Service*), gunakan **Action Classes**. Satu kelas hanya bertanggung jawab terhadap satu aksi spesifik:
- `ApplyCouponAction`
- `ProcessPaymentAction`
- `SendEnrollmentNotificationAction`

Setiap Action Class memiliki satu method publik: `execute()`:
```php
namespace App\Actions;

use App\Models\Course;

class CalculateDiscountAction
{
    public function execute(Course $course, ?string $couponCode): int
    {
        if ($couponCode === 'DISKON50') {
            return (int) ($course->price * 0.5);
        }

        return $course->price;
    }
}
```

---

#### 6. Inversion of Control (IoC) & Interface Binding

Alih-alih bergantung langsung pada library konkret (misal: Midtrans SDK), kita buat abstraksi interface agar sistem mudah diuji dan diganti sewaktu-waktu:

1. **Definisi Interface:**
```php
namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(int $amount, string $paymentMethod): string;
}
```

2. **Implementasi Konkret:**
```php
namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;

class MidtransPaymentGateway implements PaymentGatewayInterface
{
    public function charge(int $amount, string $paymentMethod): string
    {
        // Panggilan riil ke API Midtrans
        return 'TRX-' . strtoupper(uniqid());
    }
}
```

3. **Binding di `app/Providers/AppServiceProvider.php`:**
```php
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\MidtransPaymentGateway;

public function register(): void
{
    // Daftarkan ke Laravel Service Container
    $this->app->bind(PaymentGatewayInterface::class, MidtransPaymentGateway::class);
}
```
Ketika Service membutuhkan `PaymentGatewayInterface`, Laravel otomatis menyuntikkan `MidtransPaymentGateway` secara otomatis (*Automatic Dependency Injection*).

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Merancang Interface & Kontrak
Buat folder `app/Contracts/` dan berkas `PaymentGatewayInterface.php`:
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

Daftarkan di `app/Providers/AppServiceProvider.php`:
```php
use App\Contracts\PaymentGatewayInterface;
use App\Services\Payment\DummyPaymentGateway;

public function register(): void
{
    $this->app->bind(PaymentGatewayInterface::class, DummyPaymentGateway::class);
}
```

---

#### Langkah 2: Membuat Data Transfer Object (DTO)
Buat folder `app/DTOs/` dan berkas `EnrollmentData.php`:
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

#### Langkah 3: Membuat Service Layer
Buat folder `app/Services/` dan berkas `CourseEnrollmentService.php`:
```php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\EnrollmentData;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;

class CourseEnrollmentService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function enroll(EnrollmentData $data): Enrollment
    {
        return DB::transaction(function () use ($data) {
            $course = Course::findOrFail($data->courseId);

            // 1. Cek duplikasi pendaftaran
            $existing = Enrollment::where('user_id', $data->userId)
                ->where('course_id', $data->courseId)
                ->first();

            if ($existing) {
                throw new \DomainException("Anda sudah terdaftar pada kursus ini.");
            }

            // 2. Kalkulasi Biaya
            $finalPrice = $course->price;
            if ($data->couponCode === 'HEMAT20') {
                $finalPrice = (int) ($course->price * 0.8);
            }

            // 3. Eksekusi Pembayaran via Gateway
            $paymentRef = $this->paymentGateway->charge($finalPrice, $data->paymentMethod);

            // 4. Simpan Pendaftaran
            return Enrollment::create([
                'user_id'            => $data->userId,
                'course_id'          => $data->courseId,
                'amount_paid'        => $finalPrice,
                'payment_reference'  => $paymentRef,
                'status'             => 'active',
            ]);
        });
    }
}
```

---

#### Langkah 4: Membuat Skinny Controller
Buat controller invokable:
```bash
php artisan make:controller EnrollCourseController --invokable
```

Buka `app/Http/Controllers/EnrollCourseController.php`:
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
        // 1. Transformasi Form Request ke DTO
        $dto = new EnrollmentData(
            userId: (int) auth()->id(),
            courseId: (int) $request->validated('course_id'),
            paymentMethod: (string) $request->validated('payment_method'),
            couponCode: $request->validated('coupon_code')
        );

        try {
            // 2. Delegasikan ke Service Layer
            $enrollment = $this->enrollmentService->enroll($dto);

            // 3. Kembalikan Response HTTP
            return response()->json([
                'status'  => 'success',
                'message' => 'Pendaftaran kursus berhasil diproses.',
                'data'    => $enrollment,
            ], 201);

        } catch (\DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
```
*Amati bahwa Controller ini sangat ramping, bersih, dan hanya berfokus pada protokol HTTP!*

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-05.md](TUGAS-05.md).
