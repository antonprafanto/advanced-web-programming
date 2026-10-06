# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 07: File Management, Media Handling & Cloud Storage Abstraction
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-07.md](TUGAS-07.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan riwayat commit Git aktif di branch `feat/pertemuan-07`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Form Request & Penyimpanan Berkas Privat (Maks. 35 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Validasi Form Request File Object** | 15 Poin | Menggunakan aturan validasi modern `File::types(['pdf', 'jpg', 'png'])->max(3 * 1024)`. Validasi MIME-type berjalan di sisi server. |
| **Penyimpanan di Disk Privat & UUID Hashing** | 15 Poin | Berkas tersimpan di disk privat (`storage/app/private/documents/{user_id}/`). Nama berkas di-hash dengan UUID, **TIDAK** menggunakan nama asli dari klien. |
| **Persistensi Metadata ke Database** | 5 Poin | Berhasil menyimpan metadata (`user_id`, `type`, `original_filename`, `storage_path`, `mime_type`, `file_size_bytes`) ke tabel `identity_documents`. |

---

#### BAGIAN B: Secure Download via Temporary Signed URLs & Policy (Maks. 40 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Pembuatan Temporary Signed URL** | 10 Poin | Menggunakan `URL::temporarySignedRoute()` dengan durasi kedaluwarsa waktu (15 menit). |
| **Proteksi Middleware `signed`** | 10 Poin | Rute unduhan dilindungi middleware `signed`. Memodifikasi query parameter menghasilkan HTTP 403 Invalid Signature. |
| **Otorisasi Kepemilikan via `DocumentPolicy`** | 10 Poin | Mahasiswa lain tidak bisa mengunduh berkas milik orang lain kecuali berstatus admin/verifikator. |
| **Streaming File Download** | 10 Poin | Mengalirkan berkas menggunakan `Storage::disk('local')->download()` dengan menyajikan nama asli berkas kepada pengguna. |

---

#### BAGIAN C: Avatar Optimization & Konversi Format WebP (Maks. 25 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Auto Crop Persegi, Watermark & Konversi WebP** | 15 Poin | Memotong foto menjadi persegi (400x400 px), membubuhkan watermark teks/logo transparan, dan mengonversi format menjadi `.webp` dengan kompresi kualitas 80% (membersihkan metadata EXIF). |
| **Garbage Collection (Hapus Avatar Lama)** | 10 Poin | Menghapus berkas avatar fisik lama di disk publik sebelum menyimpan avatar baru agar disk server tidak penuh dengan berkas usang (*zombie files*). |

---

### III. SANKSI DAN PENGURANGAN NILAI (PENALTIES)

| Pelanggaran | Pengurangan |
| :--- | :---: |
| Tidak menggunakan branch `feat/pertemuan-07` | -10 Poin |
| Dokumen rahasia (KTP/Ijazah) disimpan di folder `public/` atau disk publik | -30 Poin (Celah Keamanan Fatal) |
| Menggunakan nama asli klien tanpa hashing UUID (`getClientOriginalName()` disimpan langsung ke filesystem) | -15 Poin |
| Mengabaikan middleware `signed` pada rute unduhan privat | -20 Poin |
| Avatar baru ditimpa tanpa menghapus avatar lama (menumpuk sampah di disk) | -10 Poin |
| Commit Git tidak teratur / hanya 1 commit tunggal | -15 Poin |
| Keterlambatan pengumpulan tugas | -10 Poin / hari |

---

### IV. KUNCI JAWABAN STANDAR REFERENSI

#### 1. Form Request: `app/Http/Requests/UploadDocumentRequest.php`
```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:ktp,ijazah,kartu_keluarga'],
            'document' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(3 * 1024), // 3 MB
            ],
        ];
    }
}
```

#### 2. Model Policy: `app/Policies/DocumentPolicy.php`
```php
namespace App\Policies;

use App\Models\IdentityDocument;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    public function view(User $user, IdentityDocument $document): Response
    {
        // Hanya pemilik berkas atau admin/verifikator yang boleh mengunduh
        if ($user->id === $document->user_id || $user->hasRole(['super-admin', 'verifikator'])) {
            return Response::allow();
        }

        return Response::deny('Anda tidak memiliki izin mengakses dokumen identitas ini.');
    }
}
```

#### 3. Controller Dokumen Privat & Signed URL: `app/Http/Controllers/DocumentController.php`
```php
namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentRequest;
use App\Models\IdentityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function store(UploadDocumentRequest $request)
    {
        $user = $request->user();
        $file = $request->file('document');

        // Generate nama file acak berbasis UUID
        $safeFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Simpan ke disk privat: storage/app/private/documents/{user_id}/
        $path = $file->storeAs("documents/{$user->id}", $safeFileName, 'local');

        $document = IdentityDocument::create([
            'user_id' => $user->id,
            'type' => $request->validated('type'),
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size_bytes' => $file->getSize(),
        ]);

        // Buat Temporary Signed URL berlaku 15 menit
        $temporaryDownloadUrl = URL::temporarySignedRoute(
            'documents.download',
            now()->addMinutes(15),
            ['document' => $document->id]
        );

        return response()->json([
            'message' => 'Dokumen berhasil diunggah dengan aman ke disk privat.',
            'data' => $document,
            'download_url' => $temporaryDownloadUrl,
        ], 201);
    }

    public function download(Request $request, IdentityDocument $document): StreamedResponse
    {
        // 1. Verifikasi otorisasi kepemilikan
        Gate::authorize('view', $document);

        // 2. Pastikan file ada di storage server
        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        // 3. Streaming download
        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
                'Cache-Control' => 'no-cache, private',
            ]
        );
    }
}
```

#### 4. Service Avatar dengan Intervention Image & WebP: `app/Services/AvatarService.php`
```php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class AvatarService
{
    public function updateAvatar(User $user, UploadedFile $file): string
    {
        // 1. Hapus avatar lama di disk publik jika ada
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // 2. Crop 400x400, bubuhkan watermark, dan konversi ke WebP
        $image = Image::read($file);
        $image->cover(400, 400);

        // Tempelkan watermark teks semi-transparan di sudut kanan bawah
        $image->text('VERIFIED', 380, 380, function ($font) {
            $font->size(18);
            $font->color('rgba(255, 255, 255, 0.6)');
            $font->align('right');
            $font->valign('bottom');
        });

        $encodedWebp = $image->toWebp(quality: 80);

        // 3. Simpan dengan ekstensi .webp di storage/app/public/avatars/
        $filename = 'avatars/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($filename, (string) $encodedWebp);

        // 4. Update model user
        $user->update(['avatar_path' => $filename]);

        return Storage::disk('public')->url($filename);
    }
}
```
