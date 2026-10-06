# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 05: Service Layer, DTO & Dependency Injection
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-05.md](TUGAS-05.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan riwayat commit Git aktif di branch `feat/pertemuan-05`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Data Transfer Object (DTO) (Maks. 25 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Penerapan Fitur Modern PHP 8.x** | 15 Poin | Menggunakan `readonly class`, Constructor Property Promotion, dan seluruh properti memiliki tipe data yang eksplisit. |
| **Named Constructor `fromRequest()`** | 10 Poin | Mengimplementasikan method statis `fromRequest()` untuk memetakan data tervalidasi dari Form Request menjadi objek DTO secara aman. |

---

#### BAGIAN B: Interface Abstraksi & Dependency Injection (Maks. 25 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Perancangan Interface Kontrak** | 10 Poin | Membuat interface `PaymentGatewayInterface` dengan signature method yang bersih. |
| **Implementasi Konkret & Service Container** | 15 Poin | Membuat kelas implementasi konkret dan mendaftarkan binding interface di `AppServiceProvider::register()` menggunakan `$this->app->bind()`. |

---

#### BAGIAN C: Service Layer HTTP-Agnostic (Maks. 35 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Isolasi Logika Bisnis (HTTP-Agnostic)** | 15 Poin | Service murni independen dari HTTP: **TIDAK** memanggil `$request`, `response()`, `redirect()`, atau `session()`. Hanya menerima DTO dan me-return Model. |
| **Jaminan ACID & Pessimistic Locking** | 10 Poin | Seluruh alur dibungkus dalam `DB::transaction()` dan pengecekan kuota menggunakan `lockForUpdate()`. |
| **Penanganan Error Domain (`DomainException`)** | 10 Poin | Melempar exception bisnis jika kuota tidak cukup alih-alih me-return error JSON langsung di service. |

---

#### BAGIAN D: Skinny Controller (Maks. 15 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Kerapian & Keringkasan Controller** | 15 Poin | Controller sangat ramping ($\le 25$ baris), hanya bertindak sebagai orkestrator (menerima request -> ubah ke DTO -> panggil service -> return response). |

---

### III. KUNCI JAWABAN STANDAR REFERENSI

#### 1. DTO: `app/DTOs/TicketBookingData.php`
```php
namespace App\DTOs;

use App\Http\Requests\BookTicketRequest;

readonly class TicketBookingData
{
    public function __construct(
        public int $userId,
        public int $ticketTierId,
        public int $quantity,
        public string $paymentMethod,
        public ?string $referralCode = null
    ) {}

    public static function fromRequest(BookTicketRequest $request): self
    {
        return new self(
            userId: (int) $request->user()->id,
            ticketTierId: (int) $request->validated('ticket_tier_id'),
            quantity: (int) $request->validated('quantity'),
            paymentMethod: (string) $request->validated('payment_method'),
            referralCode: $request->validated('referral_code')
        );
    }
}
```

#### 2. Service Layer: `app/Services/TicketBookingService.php`
```php
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\TicketBookingData;
use App\Models\TicketTier;
use App\Models\BookingTransaction;
use Illuminate\Support\Facades\DB;
use DomainException;

class TicketBookingService
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    public function book(TicketBookingData $data): BookingTransaction
    {
        return DB::transaction(function () use ($data) {
            // 1. Kunci baris kuota tiket untuk mencegah race condition
            $tier = TicketTier::lockForUpdate()->findOrFail($data->ticketTierId);

            if ($tier->available_quota < $data->quantity) {
                throw new DomainException("Kuota tiket {$tier->name} tidak mencukupi permintaan Anda.");
            }

            // 2. Hitung Total Pembayaran
            $totalAmount = $tier->price * $data->quantity;

            // 3. Potong Kuota
            $tier->decrement('available_quota', $data->quantity);

            // 4. Eksekusi Pembayaran via Abstraksi Interface
            $paymentRef = $this->paymentGateway->charge($totalAmount, $data->paymentMethod);

            // 5. Simpan Transaksi
            return BookingTransaction::create([
                'user_id'           => $data->userId,
                'ticket_tier_id'    => $data->ticketTierId,
                'quantity'          => $data->quantity,
                'total_amount'      => $totalAmount,
                'payment_reference' => $paymentRef,
                'status'            => 'paid',
            ]);
        }, 5);
    }
}
```

#### 3. Controller Ramping: `app/Http/Controllers/BookTicketController.php`
```php
namespace App\Http\Controllers;

use App\DTOs\TicketBookingData;
use App\Http\Requests\BookTicketRequest;
use App\Services\TicketBookingService;
use Illuminate\Http\JsonResponse;
use DomainException;

class BookTicketController extends Controller
{
    public function __construct(
        private TicketBookingService $bookingService
    ) {}

    public function __invoke(BookTicketRequest $request): JsonResponse
    {
        $dto = TicketBookingData::fromRequest($request);

        try {
            $transaction = $this->bookingService->book($dto);

            return response()->json([
                'status'  => 'success',
                'message' => 'Tiket berhasil dipesan!',
                'data'    => $transaction,
            ], 201);

        } catch (DomainException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
```

---

### IV. PANDUAN PENGURANGAN NILAI (PENALTY)
- Masih menyuntikkan `Request` atau memanggil `response()`/`redirect()` di dalam Service: **Pengurangan 20 poin**.
- Tidak menggunakan DTO (masih mengirim array mentah `$request->all()`): **Pengurangan 15 poin**.
- Controller masih gemuk (> 40 baris kode): **Pengurangan 10 poin**.
- Plagiarisme kode: **Nilai 0**.
