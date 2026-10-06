<?php

declare(strict_types=1);

/**
 * BEST PRACTICE: MODEL POLICIES + SPATIE RBAC
 * 
 * Keunggulan Arsitektur:
 * 1. Logika otorisasi terpusat di kelas Policy tunggal (CoursePolicy).
 * 2. Menggunakan package industri spatie/laravel-permission untuk mengelola Roles & Permissions.
 * 3. Gate::before() otomatis mem-bypass hak akses untuk Super Admin (Zero Boilerplate).
 * 4. Controller sangat bersih: cukup memanggil Gate::authorize('update', $course).
 */

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Memeriksa apakah pengguna berhak mengedit kursus.
     */
    public function update(User $user, Course $course): Response
    {
        // 1. Cek permission global Spatie
        if (! $user->can('edit courses')) {
            return Response::deny('Anda tidak memiliki permission untuk mengedit kursus.');
        }

        // 2. Cek kepemilikan resource (hanya dosen pengampu)
        if ($user->id !== $course->instructor_id) {
            return Response::deny('Anda bukan dosen pengampu kursus ini.');
        }

        return Response::allow();
    }

    /**
     * Memeriksa apakah pengguna berhak menghapus kursus.
     */
    public function delete(User $user, Course $course): Response
    {
        if (! $user->can('delete courses')) {
            return Response::deny('Anda tidak memiliki permission untuk menghapus kursus.');
        }

        if ($user->id !== $course->instructor_id) {
            return Response::deny('Anda tidak berhak menghapus kursus milik dosen lain.');
        }

        if ($course->enrollments()->exists()) {
            return Response::deny('Kursus tidak dapat dihapus karena sudah memiliki mahasiswa terdaftar.');
        }

        return Response::allow();
    }
}

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class CourseControllerClean extends Controller
{
    public function update(Request $request, Course $course): JsonResponse
    {
        // 1 Baris Elegan: Jika gagal, Laravel otomatis melempar HTTP 403 Forbidden
        Gate::authorize('update', $course);

        $course->update($request->validate([
            'title' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Kursus berhasil diperbarui.',
            'data'    => $course,
        ]);
    }

    public function destroy(Course $course): JsonResponse
    {
        Gate::authorize('delete', $course);

        $course->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kursus berhasil dihapus.',
        ]);
    }
}
