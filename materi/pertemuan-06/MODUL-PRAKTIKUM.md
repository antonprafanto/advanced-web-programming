# MODUL PRAKTIKUM 06
## Topik: Advanced Authentication, Authorization Policies & RBAC (Spatie)

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Membedakan secara fundamental antara **Authentication (AuthN)** dan **Authorization (AuthZ)** pada arsitektur web modern.
2. Membangun sistem otorisasi native Laravel menggunakan **Gates** (aksi global) dan **Policies** (otorisasi terikat model).
3. Menerapkan mekanisme **Super Admin Bypass** menggunakan hook `Gate::before()`.
4. Mengimplementasikan **Role-Based Access Control (RBAC)** skala industri menggunakan package `spatie/laravel-permission` (User $\leftrightarrow$ Role $\leftrightarrow$ Permission).
5. Mengaktifkan fitur pengamanan akun tingkat lanjut: **Email Verification (`MustVerifyEmail`)**, **Password Confirmation (`password.confirm`)**, dan konsep **Two-Factor Authentication (2FA)**.

---

### II. TEORI & KONSEP KUNCI

#### 1. Authentication (AuthN) vs Authorization (AuthZ)

| Aspek | Authentication (AuthN) | Authorization (AuthZ) |
| :--- | :--- | :--- |
| **Pertanyaan Inti** | *"Siapa Anda?"* (Verifikasi Identitas) | *"Apa yang boleh Anda lakukan?"* (Hak Akses) |
| **Mekanisme** | Email + Password, 2FA, OAuth, Token JWT | Roles, Permissions, Policies, Gates |
| **Contoh Kasus** | Pengguna berhasil login ke sistem | Dosen A hanya boleh mengedit kursus miliknya sendiri |

Starter kit modern seperti **Laravel Breeze** menyediakan fondasi autentikasi yang aman secara default: perlindungan *session fixation*, *login rate limiting / throttling* (mencegah brute-force), dan hashing password menggunakan algoritma **Bcrypt** atau **Argon2id**.

---

#### 2. Native Authorization: Kapan Menggunakan Gate vs Policy?

##### A. Gates (Aksi Global Non-Model)
Gates sangat cocok untuk otorisasi aksi yang tidak terikat pada satu model database tertentu (misal: hak mengakses panel dashboard admin atau fitur backup server).

Didaftarkan di `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\Gate;
use App\Models\User;

public function boot(): void
{
    // Gate Global: Akses Dashboard Administrator
    Gate::define('access-admin-panel', function (User $user) {
        return $user->hasRole('admin');
    });

    // Super Admin Bypass: Admin selalu lolos semua pengecekan Gate & Policy
    Gate::before(function (User $user, string $ability) {
        if ($user->hasRole('super-admin')) {
            return true; // Bypass otomatis
        }
    });
}
```

##### B. Policies (Otorisasi Terikat Model)
Policies mengorganisir logika otorisasi untuk model Eloquent tertentu (seperti `Course`, `Article`, `Order`):
```bash
php artisan make:policy CoursePolicy --model=Course
```

Isi berkas `app/Policies/CoursePolicy.php`:
```php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    // Siapa yang boleh melihat detail kursus?
    public function view(User $user, Course $course): bool
    {
        // Kursus publik boleh dilihat siapa saja, kursus draft hanya pemiliknya
        return $course->status === 'published' || $course->instructor_id === $user->id;
    }

    // Siapa yang boleh membuat kursus baru?
    public function create(User $user): bool
    {
        return $user->can('create courses');
    }

    // Siapa yang boleh mengedit kursus ini?
    public function update(User $user, Course $course): bool
    {
        // Hanya dosen pemilik kursus yang boleh mengedit
        return $user->id === $course->instructor_id;
    }

    // Siapa yang boleh menghapus?
    public function delete(User $user, Course $course): bool
    {
        return $user->id === $course->instructor_id && $course->enrollments()->doesntExist();
    }
}
```

##### C. Cara Memanggil Otorisasi di Berbagai Layer
1. **Di Controller:**
   ```php
   public function edit(Course $course)
   {
       Gate::authorize('update', $course); // Otomatis melempar 403 Forbidden jika ditolak
       return view('courses.edit', compact($course));
   }
   ```
2. **Di Form Request:**
   ```php
   public function authorize(): bool
   {
       $course = $this->route('course');
       return $this->user()->can('update', $course);
   }
   ```
3. **Di Tampilan Blade:**
   ```blade
   @can('update', $course)
       <a href="{{ route('courses.edit', $course) }}" class="btn btn-primary">Edit Kursus</a>
   @endcan
   ```

---

#### 3. Role-Based Access Control (RBAC) via `spatie/laravel-permission`

> [!WARNING]
> **Mengapa Kolom `role` Enum di Tabel Users Tidak Cukup?**  
> Pada aplikasi kampus/enterprise, seorang pengguna bisa memiliki multi-peran secara bersamaan (misal: Bapak Budi adalah **Dosen**, sekaligus **Kaprodi**, dan bertindak sebagai **Reviewer Jurnal**).  
> Pendekatan kolom tunggal `users.role = 'dosen'` akan gagal mengakomodasi skenario ini!

##### A. Arsitektur Relasi Spatie RBAC
Package ini menerapkan skema ternormalisasi:
- `roles` (Admin, Dosen, Mahasiswa)
- `permissions` (create-course, publish-course, grade-assignment)
- `model_has_roles` & `role_has_permissions`

##### B. Instalasi & Setup
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

Tambahkan trait `HasRoles` di `app/Models/User.php`:
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles; // Mengaktifkan fungsionalitas RBAC
    // ...
}
```

##### C. Seeding Peran & Izin di `DatabaseSeeder.php`
```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// 1. Buat Permissions
$permCreate = Permission::create(['name' => 'create courses']);
$permPublish = Permission::create(['name' => 'publish courses']);
$permEnroll = Permission::create(['name' => 'enroll courses']);

// 2. Buat Roles & Hubungkan Permission
$dosenRole = Role::create(['name' => 'dosen']);
$dosenRole->givePermissionTo([$permCreate, $permPublish]);

$mhsRole = Role::create(['name' => 'mahasiswa']);
$mhsRole->givePermissionTo($permEnroll);

$adminRole = Role::create(['name' => 'super-admin']);
// Super admin otomatis punya semua izin via Gate::before

// 3. Assign Role ke Pengguna
$dosenUser = User::factory()->create(['email' => 'dosen@kampus.ac.id']);
$dosenUser->assignRole('dosen');
```

##### D. Proteksi Middleware di Laravel 11/12
Daftarkan alias di `bootstrap/app.php`:
```php
->withMiddleware(function ($middleware) {
    $middleware->alias([
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);
})
```

Terapkan di rute:
```php
Route::middleware(['auth', 'role:dosen'])->group(function () {
    Route::resource('courses', CourseController::class);
});
```

---

#### 4. Fitur Pengamanan Akun Lanjutan

##### A. Email Verification (`MustVerifyEmail`)
Memastikan akun didaftarkan menggunakan alamat email asli milik pengguna:
```php
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}
```
Proteksi rute agar hanya bisa diakses user yang sudah klik link verifikasi email:
```php
Route::get('/transaksi', [TransactionController::class, 'index'])->middleware(['auth', 'verified']);
```

##### B. Password Confirmation (`password.confirm`)
Mencegah penyusup yang mengakses laptop user yang lupa logout untuk mengubah data sensitif (misal: nomor rekening penarikan dana atau password):
```php
Route::get('/settlement/withdraw', [WithdrawController::class, 'create'])
    ->middleware(['auth', 'password.confirm']);
```
Laravel otomatis menampilkan form meminta pengguna memasukkan ulang password sebelum mengizinkan aksi.

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Instalasi Package & Setup Model
Jalankan di terminal:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```
Buka model `app/Models/User.php`, tambahkan `use HasRoles;`.

---

#### Langkah 2: Membuat Policy untuk Model Course
Jalankan artisan:
```bash
php artisan make:policy CoursePolicy --model=Course
```

Lengkapi logika pengecekan di `app/Policies/CoursePolicy.php`:
```php
namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    /**
     * Tentukan apakah user boleh mengedit kursus.
     */
    public function update(User $user, Course $course): Response
    {
        // Catatan: Kita TIDAK PERLU menulis "|| $user->hasRole('super-admin')" di sini!
        // Super admin otomatis lolos lewat Gate::before() hook di AppServiceProvider.
        return $user->id === $course->instructor_id
            ? Response::allow()
            : Response::deny('Akses ditolak: Anda bukan dosen pengampu kursus ini.');
    }

    /**
     * Tentukan apakah user boleh menghapus kursus.
     */
    public function delete(User $user, Course $course): Response
    {
        if ($user->id !== $course->instructor_id) {
            return Response::deny('Akses ditolak: Anda tidak memiliki izin menghapus kursus milik dosen lain.');
        }

        if ($course->enrollments()->count() > 0) {
            return Response::deny('Kursus tidak dapat dihapus karena sudah memiliki mahasiswa aktif yang terdaftar.');
        }

        return Response::allow();
    }
}
```

---

#### Langkah 3: Konfigurasi Super Admin Bypass di `AppServiceProvider`
Buka `app/Providers/AppServiceProvider.php`, daftarkan hook di method `boot()`:
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Super Admin Bypass Hook:
        // Jika callback mengembalikan nilai `true`, seluruh pengecekan Gate & Policy otomatis lolos!
        // Jika mengembalikan `null`, Laravel akan melanjutkan evaluasi ke Policy/Gate terkait.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
```

> [!TIP]
> **Gotcha Cache Spatie:** Spatie menyimpan izin dan peran pengguna di memori cache aplikasi. Jika Anda mengubah permission di database atau seeder tetapi hak akses tidak berubah, jalankan perintah reset cache:
> ```bash
> php artisan permission:cache-reset
> ```

---

#### Langkah 4: Pengujian Hak Akses di Controller
Buka `app/Http/Controllers/CourseController.php`:
```php
namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function update(Request $request, Course $course)
    {
        // Memeriksa izin via CoursePolicy::update
        Gate::authorize('update', $course);

        $course->update($request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric',
        ]));

        return response()->json([
            'status'  => 'success',
            'message' => 'Kursus berhasil diperbarui.',
            'data'    => $course,
        ]);
    }
}
```

---

#### Langkah 5: Uji Coba Multi-Role via `php artisan tinker`
Masuk ke tinker:
```bash
php artisan tinker
```
Uji coba interaktif hak akses:
```php
// Buat Role
$roleDosen = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'dosen']);
$roleMhs = Spatie\Permission\Models\Role::firstOrCreate(['name' => 'mahasiswa']);

// Ambil User
$dosen = App\Models\User::first();
$dosen->assignRole('dosen');

// Cek Role
$dosen->hasRole('dosen'); // Return true
$dosen->hasRole('mahasiswa'); // Return false
```

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-06.md](TUGAS-06.md).
