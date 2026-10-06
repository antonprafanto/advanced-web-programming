<?php

namespace App\StudiKasusManualRbac;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpFoundation\Response;

/**
 * STUDI KASUS KOMPARASI: IMPLEMENTASI RBAC MANUAL (TANPA PACKAGE)
 * 
 * Modul ini mendemonstrasikan bagaimana RBAC dibangun secara mandiri menggunakan
 * tabel pivot relasi Many-to-Many (N:M) dan Custom Middleware.
 */

// =========================================================================
// 1. SKEMA BASIS DATA MANUAL (MIGRATION)
// =========================================================================
class CreateManualRbacTables extends Migration
{
    public function up(): void
    {
        // Tabel Daftar Peran
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'admin', 'dosen', 'mahasiswa'
            $table->string('label')->nullable(); // Deskripsi manusia: 'Dosen Pengampu'
            $table->timestamps();
        });

        // Tabel Pivot Relasi User <-> Role
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
}

// =========================================================================
// 2. MODEL ELOQUENT
// =========================================================================
class Role extends Model
{
    protected $fillable = ['name', 'label'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}

class User extends Authenticatable
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Memeriksa apakah user memiliki peran tertentu.
     */
    public function hasRole(string $roleName): bool
    {
        // Catatan: Memeriksa koleksi memory (eager loaded) jika relasi sudah di-load
        return $this->roles->contains('name', $roleName);
    }

    /**
     * Memeriksa apakah user memiliki salah satu peran dari array.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }
}

// =========================================================================
// 3. CUSTOM MIDDLEWARE: ENSURE USER HAS ROLE
// =========================================================================
class EnsureUserHasRole
{
    /**
     * Handle incoming request.
     * Penggunaan rute: Route::get('/admin', ...)->middleware('manual_role:admin,dosen');
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. Cek apakah user sudah terotentikasi
        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // 2. Cek apakah user memiliki salah satu peran yang diizinkan
        if (! $user->hasAnyRole($roles)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki peran yang berwenang.');
        }

        return $next($request);
    }
}

// =========================================================================
// 4. ANALISIS KELEMAHAN RBAC MANUAL DIBANDINGKAN SPATIE LARAVEL-PERMISSION:
// =========================================================================
/*
 * 1. Skalabilitas Izin (Atomic Permissions):
 *    - Pada RBAC manual di atas, hak akses hanya dibatasi pada level 'Role'.
 *    - Jika Dosen A boleh 'create' tapi tidak boleh 'publish', RBAC manual terpaksa
 *      membuat role baru ('dosen_senior', 'dosen_asisten') yang memicu "Role Explosion".
 *    - Spatie mengatasi ini dengan memisahkan Role vs Permission secara modular.
 * 
 * 2. Caching Performa:
 *    - RBAC manual menjalankan query SQL ke tabel 'role_user' setiap kali relasi diakses.
 *    - Spatie secara otomatis melakukan caching pada daftar roles & permissions di memori (Redis/Cache).
 * 
 * 3. Direct User Permissions:
 *    - Spatie memungkinkan pemberian izin langsung ke satu akun spesifik ($user->givePermissionTo('override-budget'))
 *      tanpa harus membuat role baru di sistem.
 */
