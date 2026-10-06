<?php

declare(strict_types=1);

/**
 * CONTOH BEST PRACTICE: CLEAN CONTROLLER, FORM REQUEST & SINGLE RESPONSIBILITY
 * 
 * Keunggulan Arsitektur:
 * 1. Otorisasi rute dikawal oleh Middleware (misal: EnsureUserIsAdmin).
 * 2. Sanitasi data terisolasi di method prepareForValidation().
 * 3. Aturan validasi terisolasi di Form Request (StoreStudentRegistrationRequest).
 * 4. Controller hanya bertugas menerima payload tervalidasi dan mendelegasikan ke Model/Service.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRegistrationRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna yang sedang login berhak mengeksekusi request ini.
     */
    public function authorize(): bool
    {
        // Otorisasi granular (misal: cek role atau policy)
        return $this->user()?->role === 'admin';
    }

    /**
     * Sanitasi data otomatis sebelum aturan validasi dieksekusi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nim'   => strtoupper(trim((string) $this->nim)),
            'nama'  => strip_tags(trim((string) $this->nama)),
            'email' => strtolower(trim((string) $this->email)),
        ]);
    }

    /**
     * Aturan validasi ketat.
     */
    public function rules(): array
    {
        return [
            'nim'           => ['required', 'string', 'size:14', 'unique:students,nim'],
            'nama'          => ['required', 'string', 'min:3', 'max:100'],
            'email'         => ['required', 'email:rfc,dns', 'unique:students,email'],
            'jurusan_id'    => ['required', 'integer', 'exists:jurusan,id'],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'ipk_terakhir'  => ['required', 'numeric', 'between:0.00,4.00'],
        ];
    }

    /**
     * Pesan error ramah pengguna dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            'nim.required'        => 'NIM mahasiswa wajib diisi.',
            'nim.size'            => 'Format panjang NIM harus tepat 14 karakter.',
            'nim.unique'          => 'NIM ini telah terdaftar pada pangkalan data.',
            'ipk_terakhir.between'=> 'Nilai IPK harus berada pada rentang 0.00 sampai 4.00.',
        ];
    }
}

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRegistrationRequest;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;

/**
 * Controller Ramping (Skinny Controller)
 */
class RegisterStudentController extends Controller
{
    /**
     * Single Action Controller (__invoke)
     */
    public function __invoke(StoreStudentRegistrationRequest $request): RedirectResponse
    {
        // $request->validated() HANYA mengembalikan data yang lolos aturan validasi
        $student = Student::create($request->validated());

        return redirect()
            ->route('students.index')
            ->with('success', "Mahasiswa {$student->nama} berhasil didaftarkan!");
    }
}
