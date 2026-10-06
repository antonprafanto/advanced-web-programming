<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

/**
 * ANTI-PATTERN: HARDCODED IF-ROLE CHECK (BURUK & RAPUH)
 * 
 * Kelemahan Fatal:
 * 1. Logika otorisasi di-hardcode di dalam controller method menggunakan string matching ($user->role == 'admin').
 * 2. Logika kepemilikan ($course->instructor_id == $user->id) ditulis ulang di update(), delete(), publish(), dsb.
 * 3. Jika bisnis ingin menambahkan role baru ("reviewer" atau "asdos"), puluhan file controller harus diedit manual!
 * 4. Tampilan Blade dipenuhi `@if(auth()->user()->role == 'dosen' || auth()->user()->role == 'admin')`.
 */
class CourseControllerNaive extends Controller
{
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $user = auth()->user();

        // Pengecekan manual berulang (Anti-Pattern)
        if ($user->role !== 'admin') {
            if ($user->role === 'dosen') {
                if ($course->instructor_id !== $user->id) {
                    abort(403, 'Anda bukan pemilik kursus ini!');
                }
            } else {
                abort(403, 'Akses ditolak!');
            }
        }

        $course->update($request->all());

        return redirect()->back();
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $user = auth()->user();

        // Copy-paste logika yang sama persis (Melanggar DRY Principle)
        if ($user->role !== 'admin') {
            if ($user->role === 'dosen') {
                if ($course->instructor_id !== $user->id) {
                    abort(403, 'Anda bukan pemilik kursus ini!');
                }
            } else {
                abort(403, 'Akses ditolak!');
            }
        }

        $course->delete();

        return redirect()->back();
    }
}
