# MODUL PRAKTIKUM 06
## Topik: Advanced Authentication, Authorization Policies & RBAC (Spatie)
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

## DAFTAR ISI
1. [Tujuan Pembelajaran](#1-tujuan-pembelajaran)
2. [Modern Authentication Starter Kits: Laravel Breeze](#2-modern-authentication-starter-kits-laravel-breeze)
3. [Authentication (AuthN) vs Authorization (AuthZ)](#3-authentication-authn-vs-authorization-authz)
4. [Native Authorization: Gates vs Model Policies](#4-native-authorization-gates-vs-model-policies)
5. [Role-Based Access Control: Implementasi Manual vs Spatie Package](#5-role-based-access-control-implementasi-manual-vs-spatie-package)
6. [Fitur Pengamanan Akun Lanjutan](#6-fitur-pengamanan-akun-lanjutan)
   - [A. Email Verification (MustVerifyEmail)](#a-email-verification-mustverifyemail)
   - [B. Password Reset Flow & Token Expiration](#b-password-reset-flow--token-expiration)
   - [C. Password Confirmation (password.confirm)](#c-password-confirmation-passwordconfirm)
   - [D. Konsep Two-Factor Authentication (2FA / TOTP)](#d-konsep-two-factor-authentication-2fa--totp)
7. [Langkah Praktikum Laboratorium](#7-langkah-praktikum-laboratorium)
8. [Lembar Tugas Mandiri](#8-lembar-tugas-mandiri)

---

## 1. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Memahami arsitektur *authentication scaffolding* modern menggunakan **Laravel Breeze**.
2. Membedakan secara fundamental antara **Authentication (AuthN)** dan **Authorization (AuthZ)**.
3. Membangun sistem otorisasi native Laravel menggunakan **Gates** (aksi global) dan **Policies** (otorisasi terikat model).
4. Menerapkan mekanisme **Super Admin Bypass** menggunakan hook `Gate::before()`.
5. Membandingkan implementasi **RBAC Manual** (tabel pivot `role_user`) dengan package industri **`spatie/laravel-permission`**.
6. Mengimplementasikan RBAC Spatie lengkap dengan *seeder*, *roles*, *permissions*, dan penanganan *cache*.
7. Mengaktifkan fitur pengamanan akun: **Email Verification**, **Password Reset Flow**, **Password Confirmation**, serta memahami algoritma **Two-Factor Authentication (2FA/TOTP)**.

---

## 2. MODERN AUTHENTICATION STARTER KITS: LARAVEL BREEZE

Pada era native PHP atau Laravel versi awal, developer sering membuat form login dan controller registrasi secara manual. Praktik ini rawan melewatkan celah keamanan standar seperti *session fixation*, ketiadaan *rate limiting* (brute-force attack), penanganan *CSRF token*, dan algoritma hashing yang lemah.

Laravel menyediakan starter kit resmi **Laravel Breeze** yang menyajikan implementasi autentikasi minimalis, aman, dan elegan.

### A. Mengapa Memilih Laravel Breeze?
- **Minimalis & Transparan:** Seluruh controller, rute, dan tampilan dipublikasikan langsung ke dalam direktori aplikasi Anda (`app/Http/Controllers/Auth/`), sehingga Anda memiliki kendali 100% untuk memodifikasi kodenya.
- **Fleksibilitas Frontend:** Mendukung Blade + Tailwind CSS, Vue (Inertia.js), React (Inertia.js), hingga API-only mode (Next.js/Nuxt).
- **Perbandingan Ekosistem Starter Kit Laravel:**

| Fitur / Starter Kit | Laravel Breeze | Laravel Jetstream / Fortify | Custom Auth Manual |
| :--- | :--- | :--- | :--- |
| **Kompleksitas** | Ringan & Mudah Dipelajari | Tinggi (Fitur tim, API token Sanctum) | Berisiko tinggi salah konfigurasi |
| **Struktur Kode** | Controller standar di `app/Http/Controllers/Auth/` | Aksi logika tersembunyi di vendor Fortify | Tergantung kerapian developer |
| **Two-Factor Auth** | Konseptual / Tambahan package | Built-in bawaan | Harus bangun dari nol |
| **Rekomendasi Pembelajaran** | ⭐ **Sangat Direkomendasikan (Standar S1)** | Untuk aplikasi enterprise kompleks | ❌ Hindari di lingkungan produksi |

### B. Perintah Instalasi Breeze:
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
php artisan migrate
npm install && npm run build
```

### C. Anatomi Controller yang Dihasilkan di `app/Http/Controllers/Auth/`:
1. `AuthenticatedSessionController.php`: Menangani form login, autentikasi kredensial via `LoginRequest` (dilengkapi proteksi *rate limiting / throttling* 5 percobaan gagal per menit), dan regenerasi session ID saat logout.
2. `RegisteredUserController.php`: Menangani pendaftaran user baru, validasi password kuat (`Password::defaults()`), hashing password via Bcrypt/Argon2id, dan memicu event `Registered` (untuk mengirim email verifikasi).
3. `PasswordResetLinkController.php` & `NewPasswordController.php`: Menangani siklus lupa kata sandi.
4. `VerifyEmailController.php` & `EmailVerificationNotificationController.php`: Menangani verifikasi tautan email.
5. `ConfirmablePasswordController.php`: Memvalidasi ulang kata sandi sebelum aksi sensitif.

---

## 3. AUTHENTICATION (AuthN) VS AUTHORIZATION (AuthZ)

```
                            [ REQUEST MASUK ]
                                    │
                                    ▼
                ┌───────────────────────────────────────┐
                │        AUTHENTICATION (AuthN)         │
                │     "Siapa Anda sebenarnya?"          │
                │  (Email, Password, Token, Session)    │
                └───────────────────┬───────────────────┘
                                    │
                                    ├── [Gagal] ──> 401 Unauthorized / Redirect Login
                                    ▼ [Sukses: User Dikenali]
                ┌───────────────────────────────────────┐
                │         AUTHORIZATION (AuthZ)         │
                │    "Apa yang boleh Anda lakukan?"     │
                │      (Roles, Permissions, Policy)     │
                └───────────────────┬───────────────────┘
                                    │
                                    ├── [Ditolak] ──> 403 Forbidden
                                    ▼ [Diizinkan]
                            [ EKSEKUSI CONTROLLER ]
```

| Aspek | Authentication (AuthN) | Authorization (AuthZ) |
| :--- | :--- | :--- |
| **Fokus Pertanyaan** | *"Siapa Anda?"* | *"Apakah Anda berhak melakukan aksi ini?"* |
| **Entitas Utama** | `User`, Password Hash, Session, API Token | `Role`, `Permission`, `Policy`, `Gate` |
| **HTTP Status Code** | **401 Unauthorized** (Belum login / token tidak valid) | **403 Forbidden** (Sudah login, tapi hak akses ditolak) |
| **Contoh Kasus** | Mahasiswa berhasil masuk dengan email & password | Mahasiswa ditolak saat mencoba mengedit nilai ujian mahasiswa lain |

---

## 4. NATIVE AUTHORIZATION: GATES VS MODEL POLICIES

Laravel menyediakan dua mekanisme otorisasi bawaan:

### A. Gates (Otorisasi Aksi Global Non-Model)
Gates berbentuk closure/callback yang didaftarkan di `AppServiceProvider::boot()`. Gates ideal untuk otorisasi yang tidak terikat pada satu objek model basis data tertentu.

```php
use Illuminate\Support\Facades\Gate;
use App\Models\User;

public function boot(): void
{
    // Gate Global: Akses Dashboard Administrator Kampus
    Gate::define('access-admin-panel', function (User $user) {
        return $user->hasRole('admin');
    });

    // Super Admin Bypass Pattern:
    // Seluruh Gate & Policy otomatis lolos jika user memiliki role 'super-admin'
    Gate::before(function (User $user, string $ability) {
        return $user->hasRole('super-admin') ? true : null;
    });
}
```

### B. Policies (Otorisasi Berbasis Kepemilikan Model)
Policies adalah kelas PHP yang mengelompokkan aturan otorisasi untuk model Eloquent tertentu (misal: `Course`, `Article`, `StudentGrade`).

Buat policy dengan perintah artisan:
```bash
php artisan make:policy CoursePolicy --model=Course
```

Implementasi di `app/Policies/CoursePolicy.php`:
```php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Dosen hanya boleh mengedit kursus miliknya sendiri.
     */
    public function update(User $user, Course $course): Response
    {
        return $user->id === $course->instructor_id
            ? Response::allow()
            : Response::deny('Akses Ditolak: Anda bukan dosen pengampu kursus ini.');
    }

    /**
     * Dosen boleh menghapus kursus jika miliknya DAN belum ada mahasiswa terdaftar.
     */
    public function delete(User $user, Course $course): Response
    {
        if ($user->id !== $course->instructor_id) {
            return Response::deny('Akses Ditolak: Anda tidak memiliki izin menghapus kursus milik dosen lain.');
        }

        if ($course->enrollments()->exists()) {
            return Response::deny('Kursus tidak dapat dihapus karena sudah memiliki mahasiswa terdaftar.');
        }

        return Response::allow();
    }
}
```

### C. Cara Memanggil Otorisasi di Berbagai Layer:
1. **Di Controller:**
   ```php
   public function update(Request $request, Course $course)
   {
       Gate::authorize('update', $course); // Otomatis throw 403 Forbidden dengan pesan dari Policy jika gagal
       $course->update($request->validated());
   }
   ```
2. **Di Form Request:**
   ```php
   public function authorize(): bool
   {
       return $this->user()->can('update', $this->route('course'));
   }
   ```
3. **Di Tampilan Blade:**
   ```blade
   @can('update', $course)
       <a href="{{ route('courses.edit', $course) }}" class="btn btn-warning">Edit Kursus</a>
   @endcan
   ```

---

## 5. ROLE-BASED ACCESS CONTROL: IMPLEMENTASI MANUAL VS SPATIE PACKAGE

### A. Pendekatan 1: RBAC Manual (Pivot Table `role_user`)
Pada aplikasi skala kecil dengan 2-3 role statis, pengembang dapat membuat skema relasi manual:
- Tabel `roles` (`id`, `name`)
- Tabel pivot `role_user` (`user_id`, `role_id`)
- Relasi di Model `User`:
  ```php
  public function roles(): BelongsToMany
  {
      return $this->belongsToMany(Role::class);
  }

  public function hasRole(string $roleName): bool
  {
      return $this->roles->contains('name', $roleName);
  }
  ```
- Custom Middleware `EnsureUserHasRole`:
  ```php
  public function handle(Request $request, Closure $next, string $role): Response
  {
      if (! $request->user()?->hasRole($role)) {
          abort(403, 'Akses ditolak untuk peran ini.');
      }
      return $next($request);
  }
  ```

> [!WARNING]
> **Kelemahan RBAC Manual di Skala Menengah/Besar:**
> 1. Tidak memiliki konsep *Permissions* granular (hanya membatasi berbasis Role, sulit mengatur variasi izin detail).
> 2. Query relasi `role_user` dieksekusi berulang-ulang tanpa sistem caching memori.
> 3. Sulit membangun panel manajemen hak akses dinamis di mana Super Admin dapat mencentang izin baru lewat antarmuka web.

### B. Pendekatan 2: Package Industri `spatie/laravel-permission`
Package `spatie/laravel-permission` adalah standar de facto di industri Laravel. Package ini mengadopsi model **NIST RBAC** yang memisahkan antara Peran (*Roles*) dan Izin (*Permissions*):

```
[ User ] <──(M:N)──> [ Role ] <──(M:N)──> [ Permission ]
   │                                              ▲
   └────────────────────(M:N)─────────────────────┘
                 (Direct Permissions)
```

- **Tabel Basis Data yang Dihasilkan Spatie:**
  1. `roles` (nama peran: `super-admin`, `dosen`, `mahasiswa`)
  2. `permissions` (nama izin atomik: `create courses`, `publish courses`, `grade assignments`)
  3. `model_has_roles` (relasi user ke role polymorphic)
  4. `role_has_permissions` (relasi role ke daftar izin)
  5. `model_has_permissions` (izin langsung ke pengguna tertentu / *override*)

### C. Tabel Komparasi Komprehensif:

| Kriteria Evaluasi | RBAC Manual (Tabel Sederhana) | Spatie Laravel-Permission |
| :--- | :--- | :--- |
| **Granularitas Akses** | Kasar (Hanya level Role) | Sangat Halus (Roles + Atomic Permissions) |
| **Direct Permissions** | ❌ Sulit (harus modifikasi skema DB) | ✅ Bawaan (`$user->givePermissionTo()`) |
| **Sistem Caching** | ❌ Manual (rentan query berulang) | ✅ Otomatis (Cache tag / Redis / File cache) |
| **Blade Directives** | ❌ Harus buat directive manual | ✅ Bawaan (`@role`, `@hasanyrole`, `@can`) |
| **Integrasi Laravel Gate** | ❌ Perlu mapping manual | ✅ Otomatis terdaftar ke Laravel Gate |
| **Rekomendasi Penggunaan** | Prototipe sederhana 1-2 tabel | Proyek Komersial / Enterprise / Capstone |

---

## 6. FITUR PENGAMANAN AKUN LANJUTAN

### A. Email Verification (`MustVerifyEmail`)
Mencegah pendaftaran akun bot dan memastikan alamat email pengguna valid:
```php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```
Pasang middleware `verified` pada rute yang membutuhkan akun terverifikasi:
```php
Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])
    ->middleware(['auth', 'verified']);
```

### B. Password Reset Flow & Token Expiration
Laravel mengamankan alur lupa kata sandi dengan mekanisme kriptografis:
1. Pengguna memasukkan email di form `/forgot-password`.
2. Laravel menghasilkan token acak 64 karakter, melakukan hashing SHA-256, dan menyimpannya di tabel `password_reset_tokens`.
3. Email dikirim dengan tautan aman bertanda tangan digital (*signed token*).
4. Tautan memiliki masa kedaluwarsa waktu (default: 60 menit) yang diatur pada `config/auth.php`:
   ```php
   'passwords' => [
       'users' => [
           'provider' => 'users',
           'table' => 'password_reset_tokens',
           'expire' => 60, // Menit sebelum token hangus
           'throttle' => 60, // Delay detik antar permintaan reset
       ],
   ],
   ```
5. Saat pengguna memasukkan password baru, token diverifikasi, password baru di-hash via `Hash::make()`, token dihapus dari database, dan `remember_token` dirotasi untuk memutus sesi login lama.

### C. Password Confirmation (`password.confirm`)
Melindungi tindakan sensitif (misal: mengganti rekening bank, mengubah password, atau menghapus akun) dari pihak ketiga yang mengakses laptop user yang lupa di-lock:
```php
Route::get('/profile/payout-settings', [PayoutController::class, 'edit'])
    ->middleware(['auth', 'password.confirm']);
```
Jika sesi konfirmasi telah melewati batas waktu (default 3 jam / 10800 detik), Laravel otomatis menampilkan modal meminta password sebelum mengizinkan user mengakses rute tersebut.

### D. Konsep Two-Factor Authentication (2FA / TOTP)
Two-Factor Authentication (2FA) menggabungkan dua faktor bukti identitas:
1. **Faktor Pengetahuan (*Something You Know*):** Password akun.
2. **Faktor Kepemilikan (*Something You Have*):** Smartphone dengan aplikasi autentikator (Google Authenticator, Microsoft Authenticator, Aegis).

#### Cara Kerja Algoritma TOTP (RFC 6238):
1. **Shared Secret Key:** Saat mengaktifkan 2FA, server membuat kunci rahasia acak 32 karakter Base32 (misal: `JBSWY3DPEHPK3PXP`) yang disimpan di database user (terenkripsi).
2. **Penyajian QR Code:** Server menyajikan URI standar ke dalam bentuk QR Code:
   ```
   otpauth://totp/SIAKAD:budi@kampus.ac.id?secret=JBSWY3DPEHPK3PXP&issuer=SIAKAD
   ```
3. **Kalkulasi 6-Digit OTP:**
   Aplikasi di smartphone dan server menghitung kode numerik berbasis waktu saat ini ($T$) dengan interval 30 detik:
   $$T = \left\lfloor \frac{\text{Current Unix Timestamp}}{30} \right\rfloor$$
   $$\text{OTP} = \text{Truncate}(\text{HMAC-SHA1}(\text{Secret Key}, T)) \pmod{10^6}$$
4. **Verifikasi Drift Window:** Server memverifikasi kecocokan kode dengan toleransi window $\pm 30$ detik untuk mengantisipasi perbedaan sinkronisasi jam antara ponsel pengguna dan server.
5. **Recovery Codes:** Server menghasilkan 8–10 kode darurat sekali pakai (*single-use hashed recovery codes*) yang wajib dicatat pengguna jika ponselnya hilang atau rusak.

---

## 7. LANGKAH PRAKTIKUM LABORATORIUM

### Langkah 1: Instalasi Package Spatie & Konfigurasi User Model
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```
Buka `app/Models/User.php`:
```php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasRoles;
    // ...
}
```

### Langkah 2: Membuat Role & Permission Seeder
Buat file `database/seeders/RoleAndPermissionSeeder.php`:
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cache izin Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat permissions
        Permission::findOrCreate('create courses');
        Permission::findOrCreate('edit courses');
        Permission::findOrCreate('delete courses');
        Permission::findOrCreate('enroll courses');

        // 3. Buat roles dan pasang permissions
        $roleDosen = Role::findOrCreate('dosen');
        $roleDosen->givePermissionTo(['create courses', 'edit courses', 'delete courses']);

        $roleMhs = Role::findOrCreate('mahasiswa');
        $roleMhs->givePermissionTo(['enroll courses']);

        Role::findOrCreate('super-admin'); // Hak akses absolut otomatis via Gate::before
    }
}
```

### Langkah 3: Konfigurasi Super Admin Bypass Hook
Buka `app/Providers/AppServiceProvider.php`:
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super admin otomatis bypass seluruh pengecekan otorisasi
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
```

### Langkah 4: Mendaftarkan Middleware Alias (Laravel 11/12)
Buka `bootstrap/app.php`:
```php
->withMiddleware(function ($middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    ]);
})
```

---

## 8. LEMBAR TUGAS MANDIRI
Selesaikan seluruh instruksi penugasan mingguan yang tercantum pada [TUGAS-06.md](TUGAS-06.md).
