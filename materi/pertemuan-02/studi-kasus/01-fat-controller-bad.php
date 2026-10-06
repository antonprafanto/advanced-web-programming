<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * CONTOH ANTI-PATTERN: FAT CONTROLLER (BURUK & SULIT DI-TEST)
 * 
 * Kesalahan Arsitektur:
 * 1. Pengecekan otorisasi manual di method controller (harus didelegasikan ke Middleware/Policy).
 * 2. Sanitasi data manual menggunakan fungsi native (trim, strtolower, dsb).
 * 3. Validasi inline puluhan baris mengotori controller.
 * 4. Penanganan pesan error kustom yang manual dan tidak konsisten.
 * 5. Controller mengetahui terlalu banyak detail teknis (melanggar Single Responsibility Principle).
 */
class StudentRegistrationControllerLegacy extends Controller
{
    public function register(Request $request)
    {
        // 1. Otorisasi manual di dalam controller (Buruk!)
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses untuk mendaftarkan mahasiswa.');
        }

        // 2. Sanitasi data manual di controller (Buruk!)
        $nim = strtoupper(trim($request->input('nim', '')));
        $email = strtolower(trim($request->input('email', '')));
        $nama = strip_tags(trim($request->input('nama', '')));

        // 3. Validasi inline yang panjang (Buruk!)
        $validated = $request->validate([
            'nim' => 'required|string|size:14|unique:students,nim',
            'nama' => 'required|string|min:3|max:100',
            'email' => 'required|email|unique:students,email',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tanggal_lahir' => 'required|date|before:today',
            'ipk_terakhir' => 'required|numeric|between:0,4.00',
        ], [
            'nim.required' => 'NIM wajib diisi dong!',
            'nim.unique' => 'NIM ini sudah dipakai orang lain.',
            'email.required' => 'Email tidak boleh kosong!',
            'ipk_terakhir.between' => 'IPK harus di antara 0 sampai 4.',
        ]);

        // 4. Proses penyimpanan langsung
        $student = Student::create([
            'nim' => $nim,
            'nama' => $nama,
            'email' => $email,
            'jurusan_id' => $validated['jurusan_id'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'ipk_terakhir' => $validated['ipk_terakhir'],
        ]);

        return redirect('/students')->with('success', 'Mahasiswa berhasil didaftarkan!');
    }
}
