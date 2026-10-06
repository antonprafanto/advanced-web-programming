# TUGAS PRAKTIKUM 05
## Topik: Refactoring Fat Controller ke Arsitektur Berlapis (DTO, Service Layer & Interface Binding)

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih mahasiswa dalam menerapkan arsitektur perangkat lunak skala enterprise (*Clean Architecture & SOLID Principles*). Mahasiswa ditantang untuk merefaktor modul transaksi yang gemuk dan monolitik menjadi arsitektur berlapis yang modular, *testable*, dan *reusable*.

---

### II. SOAL STUDI KASUS: SISTEM PEMESANAN TIKET KONSER (CHECKOUT)

Diberikan sebuah skenario modul pemesanan tiket konser musik yang awalnya dibuat secara monolitik di controller. Anda diminta merekonstruksi kode tersebut menjadi arsitektur berlapis berstandar industri dengan spesifikasi:

#### Bagian A: Data Transfer Object (DTO) (Bobot 25%)
1. Buat kelas DTO immutable `TicketBookingData` menggunakan fitur PHP 8.x (`readonly class` dengan *Constructor Property Promotion*).
2. DTO wajib memuat properti:
   - `userId` (int)
   - `ticketTierId` (int) - ID kategori tiket (VIP / Festival)
   - `quantity` (int)
   - `paymentMethod` (string)
   - `referralCode` (nullable string)
3. Sediakan named constructor `fromRequest(BookTicketRequest $request): self`.

---

#### Bagian B: Interface Abstraksi & Service Container Binding (Bobot 25%)
1. Buat interface `PaymentGatewayInterface` di namespace `App\Contracts`:
   - Method: `public function charge(int $amount, string $paymentMethod): string;`
2. Buat implementasi konkret (misal: `SimulatedPaymentGateway`) di `App\Services\Payment`.
3. Daftarkan binding interface ke kelas konkret tersebut di dalam method `register()` pada `app/Providers/AppServiceProvider.php`.

---

#### Bagian C: Service Layer HTTP-Agnostic (Bobot 35%)
1. Buat kelas `TicketBookingService` di namespace `App\Services`:
   - Lakukan Dependency Injection untuk `PaymentGatewayInterface` melalui constructor.
2. Method utama: `public function book(TicketBookingData $data): BookingTransaction`
   - **Ketentuan Mutlak:**
     - Wajib membungkus proses dalam `DB::transaction()`.
     - Melakukan pengecekan kuota tiket yang tersisa menggunakan `lockForUpdate()`.
     - Jika kuota tidak cukup, lempar exception `DomainException`.
     - Memotong kuota tiket dan memanggil method `charge` dari payment gateway.
     - Menyimpan record transaksi pemesanan.
     - **Dilarang Keras** menggunakan `$request`, `response()`, atau `session()` di dalam file Service ini!

---

#### Bagian D: Skinny Controller (Bobot 15%)
1. Buat Single Action Controller `BookTicketController` (`__invoke`):
   - Controller dibatasi maksimal **20-25 baris kode**.
   - Hanya bertugas: mengonversi Form Request ke DTO, memanggil Service, menangani `DomainException`, dan me-return JSON response (HTTP 201 jika sukses, HTTP 422 jika gagal).

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-05`.
2. Gunakan format *Conventional Commits* (misal: `feat: buat DTO booking ticket`, `feat: implementasi service layer dan interface binding`).
3. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*):
   - Struktur folder `app/DTOs`, `app/Services`, dan `app/Contracts`.
   - Bukti respons pengujian sukses (HTTP 201) dan skenario gagal kuota habis (HTTP 422).
4. Batas pengumpulan: H-1 sebelum Pertemuan 06 dimulai.
