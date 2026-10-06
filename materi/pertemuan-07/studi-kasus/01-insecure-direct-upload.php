<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * STUDI KASUS ANTI-PATTERN: Insecure Direct File Upload
 * 
 * JANGAN PERNAH MENGGUNAKAN KODE INI DI LINGKUNGAN PRODUKSI!
 * Kode ini merepresentasikan kebiasaan lama (warisan native PHP) yang sangat berbahaya.
 */
class InsecureFileUploadController extends Controller
{
    /**
     * CONTOH 1: Upload Avatar Rentan RCE (Remote Code Execution) & Path Traversal
     */
    public function uploadAvatarBad(Request $request, User $user)
    {
        // KESALAHAN 1: Validasi rapuh. Hanya mengandalkan ekstensi teks biasa
        // Penyerang bisa membypass dengan ekstensi ganda seperti: 'shell.php.jpg' atau 'shell.phtml'
        $request->validate([
            'foto' => 'required',
        ]);

        $file = $request->file('foto');

        // KESALAHAN 2: Menggunakan nama asli berkas dari klien tanpa sanitasi!
        // Jika nama berkas adalah "../../public/index.php", berkas indeks framework bisa tertimpa!
        // Jika dua user mengunggah berkas bernama 'avatar.jpg', berkas user pertama tertimpa.
        $originalName = $file->getClientOriginalName();

        // KESALAHAN 3: Memindahkan berkas secara mentah langsung ke dalam direktori publik
        // Web server (Nginx/Apache) akan langsung mengeksekusi script jika berkas mengandung PHP code!
        $file->move(public_path('uploads/avatars'), $originalName);

        $user->avatar_url = url('uploads/avatars/' . $originalName);
        $user->save();

        return response()->json([
            'status' => 'success',
            'url' => $user->avatar_url,
            'warning' => 'Celah keamanan RCE terbuka lebar di URL ini!'
        ]);
    }

    /**
     * CONTOH 2: Kebocoran Data Privasi Dokumen Rahasia (KTP / Slip Gaji)
     */
    public function uploadKtpBad(Request $request, User $user)
    {
        $file = $request->file('ktp');

        // KESALAHAN FATAL: Dokumen KTP disimpan di direktori publik!
        // Siapapun yang menebak nama berkas 'ktp_123.jpg' di Google Search atau via brute-force
        // dapat mengunduh KTP seluruh mahasiswa tanpa memerlukan login/otorisasi apapun!
        $filename = 'ktp_' . $user->id . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/documents'), $filename);

        return response()->json([
            'message' => 'KTP berhasil diupload tapi data privasi bocor ke publik!',
            'public_url' => url('uploads/documents/' . $filename),
        ]);
    }
}
