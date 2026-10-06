<?php

namespace App\Http\Controllers\Modern;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\IdentityDocument;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Intervention\Image\Laravel\Facades\Image;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * STUDI KASUS ENTERPRISE: Secure Multi-Disk Media Management & Signed URLs
 * 
 * Sesuai Standar Industri Modern Laravel 11.x / 12.x
 */
class SecureMediaController extends Controller
{
    /**
     * PRAKTIK BAIK 1: Upload Avatar Publik dengan Optimasi WebP
     */
    public function updateAvatar(Request $request)
    {
        // 1. Validasi ketat menggunakan Rule File Object
        $request->validate([
            'avatar' => [
                'required',
                File::image()
                    ->min(10) // minimal 10 KB
                    ->max(2 * 1024) // maksimal 2 MB
                    ->dimensions(File::image()->dimensions()->minWidth(200)->minHeight(200)),
            ],
        ]);

        $user = $request->user();
        $file = $request->file('avatar');

        // 2. Optimasi Gambar dengan Intervention Image: Auto Crop 400x400 & Convert to WebP
        $optimizedImage = Image::read($file)
            ->cover(400, 400)
            ->toWebp(quality: 80);

        // 3. Generate nama berkas acak (UUID) dengan ekstensi .webp
        $filename = 'avatars/' . Str::uuid() . '.webp';

        // 4. Hapus avatar lama di disk publik jika ada
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        // 5. Simpan stream binary ke disk 'public'
        Storage::disk('public')->put($filename, (string) $optimizedImage);

        // 6. Simpan path relatif ke database
        $user->update(['avatar_path' => $filename]);

        return response()->json([
            'message' => 'Avatar berhasil diperbarui dan dikonversi ke format WebP.',
            'avatar_url' => Storage::disk('public')->url($filename),
        ]);
    }

    /**
     * PRAKTIK BAIK 2: Upload Dokumen Rahasia (KTP/Ijazah) ke Disk Privat
     */
    public function uploadSensitiveDocument(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:ktp,ijazah,transkrip'],
            'document' => [
                'required',
                File::types(['pdf', 'jpg', 'jpeg', 'png'])
                    ->max(5 * 1024), // Maksimal 5 MB
            ],
        ]);

        $user = $request->user();
        /** @var UploadedFile $file */
        $file = $request->file('document');

        // Simpan ke disk 'local' (storage/app/private/documents/{user_id}/)
        // Nama berkas dibuat acak otomatis oleh hashName()
        $path = $file->store("documents/{$user->id}", 'local');

        $document = IdentityDocument::create([
            'user_id' => $user->id,
            'type' => $request->input('type'),
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size_bytes' => $file->getSize(),
        ]);

        // Generate Temporary Signed URL yang berlaku selama 30 menit
        $signedDownloadUrl = URL::temporarySignedRoute(
            'documents.secure-download',
            now()->addMinutes(30),
            ['document' => $document->id]
        );

        return response()->json([
            'message' => 'Dokumen rahasia aman tersimpan di storage privat terisolasi.',
            'document_id' => $document->id,
            'expires_in' => '30 Menit',
            'temporary_download_url' => $signedDownloadUrl,
        ], 201);
    }

    /**
     * PRAKTIK BAIK 3: Streaming Unduhan Berkas Privat dengan Proteksi Signed URL & Policy
     */
    public function streamDownload(Request $request, IdentityDocument $document): StreamedResponse
    {
        // 1. Verifikasi tanda tangan URL digital (middleware 'signed' di router)
        // Jika signature diubah, otomatis return HTTP 403 Forbidden.

        // 2. Otorisasi kepemilikan dokumen via Policy
        Gate::authorize('view', $document);

        // 3. Cek keberadaan berkas fisik di disk privat
        if (! Storage::disk('local')->exists($document->storage_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di server penyimpanan.');
        }

        // 4. Streaming berkas langsung ke client tanpa membongkar path asli direktori server
        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
                'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            ]
        );
    }

    /**
     * PRAKTIK BAIK 4: Upload Sertifikat/Media dengan Watermarking Dinamis & Konversi WebP
     */
    public function uploadMediaWithWatermark(Request $request)
    {
        $request->validate([
            'image' => ['required', File::image()->max(5 * 1024)],
            'watermark_text' => ['nullable', 'string', 'max:50'],
        ]);

        $file = $request->file('image');
        $watermark = $request->input('watermark_text', 'ACADEMIC VERIFIED');

        // 1. Baca gambar dan resize proporsional
        $img = Image::read($file)->scaleDown(width: 1200);

        // 2. Tempelkan watermark teks transparan di sudut kanan bawah
        $img->text($watermark, $img->width() - 30, $img->height() - 30, function ($font) {
            $font->size(24);
            $font->color('rgba(255, 255, 255, 0.6)');
            $font->align('right');
            $font->valign('bottom');
        });

        // 3. Konversi ke WebP (membersihkan EXIF metadata tersembunyi)
        $encodedWebp = $img->toWebp(quality: 85);

        $filename = 'certificates/' . Str::uuid() . '.webp';
        Storage::disk('public')->put($filename, (string) $encodedWebp);

        return response()->json([
            'message' => 'Berkas media berhasil di-watermark dan dikonversi ke format WebP.',
            'media_url' => Storage::disk('public')->url($filename),
        ]);
    }
}
