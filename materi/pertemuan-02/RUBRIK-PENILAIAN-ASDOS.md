# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 02: Form Request Validation, Custom Middleware & Clean Controller
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-02.md](TUGAS-02.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan riwayat commit Git aktif di branch `feat/pertemuan-02`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Form Request `SubmitThesisProposalRequest` (Maks. 50 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Logika Otorisasi (`authorize`)** | 10 Poin | Mengimplementasikan pemeriksaan status user yang tepat. |
| **Sanitasi Data (`prepareForValidation`)** | 15 Poin | Menerapkan `prepareForValidation()` untuk mengubah NIM menjadi kapital dan judul menjadi Title Case (`ucwords`/`Str::title`) serta membuang tag HTML. |
| **Kelengkapan Aturan Validasi (`rules`)** | 15 Poin | Menerapkan seluruh aturan validasi dengan benar: tipe data, batas minimal SKS (100), rentang IPK (3.00-4.00), dan mime-type PDF berkas draft (max 2048 KB). |
| **Kustomisasi Pesan Bahasa Indonesia (`messages`)** | 10 Poin | Pesan kesalahan berbahasa Indonesia yang informatif dan relevan. |

---

#### BAGIAN B: Custom Middleware `EnsureEligibleForThesis` (Maks. 30 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Logika Penanganan Middleware** | 15 Poin | Middleware memeriksa status akademik user dan menghentikan request jika user tidak aktif/cuti dengan status HTTP 403 atau redirect. |
| **Registrasi di Laravel 11/12 (`bootstrap/app.php`)** | 15 Poin | Middleware didaftarkan secara benar menggunakan `$middleware->alias([...])` di file `bootstrap/app.php`. |

---

#### BAGIAN C: Invokable Controller & Routing (Maks. 20 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Skinny Controller (`__invoke`)** | 10 Poin | Controller ramping, tidak melakukan validasi manual lagi, hanya mengambil `$request->validated()` dan mengembalikan JSON HTTP 201. |
| **Route Registration & Naming** | 10 Poin | Rute `POST /thesis/apply` menggunakan middleware alias dan memiliki nama rute `thesis.apply`. |

---

### III. KUNCI JAWABAN STANDAR REFERENSI

#### 1. Form Request: `app/Http/Requests/SubmitThesisProposalRequest.php`
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SubmitThesisProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nim' => strtoupper(trim((string) $this->nim)),
            'judul_proposal' => Str::title(strip_tags(trim((string) $this->judul_proposal))),
        ]);
    }

    public function rules(): array
    {
        return [
            'nim'              => ['required', 'string', 'size:14'],
            'judul_proposal'   => ['required', 'string', 'min:20', 'max:255'],
            'sks_tempuh'       => ['required', 'integer', 'min:100'],
            'ipk_terakhir'     => ['required', 'numeric', 'between:3.00,4.00'],
            'berkas_draft_pdf' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.size'                 => 'Panjang NIM harus tepat 14 karakter.',
            'judul_proposal.min'       => 'Judul proposal minimal memuat 20 karakter.',
            'sks_tempuh.min'           => 'Syarat pengajuan skripsi minimal telah menempuh 100 SKS.',
            'ipk_terakhir.between'     => 'IPK minimal pengajuan adalah 3.00.',
            'berkas_draft_pdf.mimes'   => 'Format berkas draft wajib berupa dokumen PDF.',
            'berkas_draft_pdf.max'     => 'Ukuran berkas PDF maksimal adalah 2 MB.',
        ];
    }
}
```

#### 2. Middleware: `app/Http/Middleware/EnsureEligibleForThesis.php`
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEligibleForThesis
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->status_akademik, ['cuti', 'non-aktif'], true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Status akademik Anda tidak aktif/sedang cuti. Anda belum diizinkan mengajukan skripsi.',
            ], 403);
        }

        return $next($request);
    }
}
```

#### 3. Registrasi Middleware: `bootstrap/app.php`
```php
use App\Http\Middleware\EnsureEligibleForThesis;

return Application::configure(basePath: dirname(__DIR__))
    // ...
    ->withMiddleware(function ($middleware) {
        $middleware->alias([
            'thesis.eligible' => EnsureEligibleForThesis::class,
        ]);
    })
    // ...
```

#### 4. Invokable Controller: `app/Http/Controllers/SubmitThesisProposalController.php`
```php
namespace App\Http\Controllers;

use App\Http\Requests\SubmitThesisProposalRequest;
use Illuminate\Http\JsonResponse;

class SubmitThesisProposalController extends Controller
{
    public function __invoke(SubmitThesisProposalRequest $request): JsonResponse
    {
        // Simpan file pdf ke storage jika diperlukan:
        // $path = $request->file('berkas_draft_pdf')->store('proposals');

        return response()->json([
            'status'  => 'success',
            'message' => 'Proposal tugas akhir berhasil diajukan.',
            'data'    => $request->safe()->except(['berkas_draft_pdf']),
        ], 201);
    }
}
```

---

### IV. PANDUAN PENGURANGAN NILAI (PENALTY)
- Keterlambatan pengumpulan: Pengurangan 10 poin per hari.
- Tidak menyertakan bukti screenshot pengujian di `README.md`: Pengurangan 15 poin.
- Validasi masih ditulis langsung (*inline*) di dalam Controller: Pengurangan 20 poin.
- Plagiarisme kode antar mahasiswa: **Nilai 0**.
