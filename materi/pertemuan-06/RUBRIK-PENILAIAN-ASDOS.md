# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 06: Role-Based Access Control (Spatie), Model Policies & Email Verification
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-06.md](TUGAS-06.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan riwayat commit Git aktif di branch `feat/pertemuan-06`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Arsitektur Spatie RBAC & Seeding (Maks. 35 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Instalasi Spatie & Trait HasRoles** | 10 Poin | Berhasil mempublikasikan migrasi `spatie/laravel-permission`, menjalankan migrasi tabel relasi peran, dan menambahkan trait `HasRoles` pada Model `User`. |
| **Penyusunan RoleAndPermissionSeeder** | 15 Poin | Membuat seeder yang mendaftarkan seluruh permissions dan roles dengan relasi tepat (`super-admin`, `dosen`, `mahasiswa`). Menggunakan method `givePermissionTo()`. |
| **Pembuatan Dummy Users & Role Assignment** | 10 Poin | Membuat minimal 3 user dummy yang masing-masing diberikan role via `assignRole()` secara benar. |

---

#### BAGIAN B: Model Policy Kepemilikan Data & Super Admin Bypass (Maks. 40 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Logika CoursePolicy::update** | 15 Poin | Mengimplementasikan validasi kepemilikan resource: `$user->id === $course->instructor_id`. Dosen lain ditolak otomatis. |
| **Logika CoursePolicy::delete** | 15 Poin | Menerapkan aturan ganda: kepemilikan resource DAN verifikasi bahwa belum ada mahasiswa yang terdaftar (`$course->enrollments()->count() === 0`). |
| **Super Admin Bypass (`Gate::before`)** | 10 Poin | Mengimplementasikan hook `Gate::before()` di `AppServiceProvider::boot()` agar role `super-admin` selalu me-return `true` tanpa harus diberi permissions satu per satu. |

---

#### BAGIAN C: Proteksi Rute, Controller & Email Verification (Maks. 25 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Aktivasi Email Verification** | 10 Poin | Mengimplementasikan kontrak `MustVerifyEmail` pada Model `User` dan memproteksi rute pendaftaran kursus dengan middleware `verified`. |
| **Otorisasi Controller & Respons HTTP 403** | 10 Poin | Menggunakan `Gate::authorize()` atau method `authorize()` di dalam Controller. Dosen tidak berhak berhasil mendapatkan HTTP 403 Forbidden. |
| **Dokumentasi Pengujian (Screenshot Terminal & HTTP)** | 5 Poin | Menyertakan bukti screenshot eksekusi seeder, uji sukses otorisasi, uji gagal HTTP 403, dan uji bypass super-admin. |

---

### III. SANKSI DAN PENGURANGAN NILAI (PENALTIES)

| Pelanggaran | Pengurangan |
| :--- | :---: |
| Tidak menggunakan branch `feat/pertemuan-06` | -10 Poin |
| Hardcode role check di controller menggunakan `if ($user->role !== 'admin')` alih-alih Policy/Spatie | -25 Poin |
| Super admin diberi ribuan permission manual di seeder alih-alih menggunakan `Gate::before()` hook | -10 Poin |
| Tidak mengaktifkan `MustVerifyEmail` pada Model `User` | -10 Poin |
| Commit Git tidak deskriptif / hanya 1 commit instan ("done all") | -15 Poin |
| Keterlambatan pengumpulan tugas | -10 Poin / hari |

---

### IV. KUNCI JAWABAN STANDAR REFERENSI

#### 1. Seeder: `database/seeders/RoleAndPermissionSeeder.php`
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Buat Permissions
        $permissions = [
            'manage users',
            'create courses',
            'edit courses',
            'delete courses',
            'enroll courses',
            'grade assignments',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm);
        }

        // 2. Buat Roles & Sync Permissions
        $roleSuperAdmin = Role::findOrCreate('super-admin');
        
        $roleDosen = Role::findOrCreate('dosen');
        $roleDosen->givePermissionTo([
            'create courses',
            'edit courses',
            'delete courses',
            'grade assignments',
        ]);

        $roleMahasiswa = Role::findOrCreate('mahasiswa');
        $roleMahasiswa->givePermissionTo([
            'enroll courses',
        ]);

        // 3. Buat Dummy Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@kampus.ac.id'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($roleSuperAdmin);

        $dosenA = User::firstOrCreate(
            ['email' => 'dosen.a@kampus.ac.id'],
            [
                'name' => 'Dr. Budi Santoso, M.Kom',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $dosenA->assignRole($roleDosen);

        $dosenB = User::firstOrCreate(
            ['email' => 'dosen.b@kampus.ac.id'],
            [
                'name' => 'Siti Rahma, M.T',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $dosenB->assignRole($roleDosen);

        $mhs = User::firstOrCreate(
            ['email' => 'mhs@kampus.ac.id'],
            [
                'name' => 'Ahmad Yusuf',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );
        $mhs->assignRole($roleMahasiswa);
    }
}
```

#### 2. Model User: `app/Models/User.php`
```php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

#### 3. Course Policy: `app/Policies/CoursePolicy.php`
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
        return $user->id === $course->instructor_id
            ? Response::allow()
            : Response::deny('Anda bukan dosen pengampu kursus ini.');
    }

    /**
     * Tentukan apakah user boleh menghapus kursus.
     */
    public function delete(User $user, Course $course): Response
    {
        if ($user->id !== $course->instructor_id) {
            return Response::deny('Anda tidak memiliki izin menghapus kursus ini.');
        }

        if ($course->enrollments()->count() > 0) {
            return Response::deny('Kursus tidak dapat dihapus karena sudah memiliki mahasiswa terdaftar.');
        }

        return Response::allow();
    }
}
```

#### 4. Super Admin Bypass di Service Provider: `app/Providers/AppServiceProvider.php`
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Bypass semua pemeriksaan otorisasi jika user memiliki role super-admin
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });
    }
}
```

#### 5. Controller Otorisasi: `app/Http/Controllers/CourseController.php`
```php
namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseController extends Controller
{
    public function update(Request $request, Course $course)
    {
        // Memeriksa policy CoursePolicy@update
        Gate::authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $course->update($validated);

        return response()->json([
            'message' => 'Kursus berhasil diperbarui.',
            'data' => $course,
        ]);
    }

    public function destroy(Course $course)
    {
        // Memeriksa policy CoursePolicy@delete
        Gate::authorize('delete', $course);

        $course->delete();

        return response()->json([
            'message' => 'Kursus berhasil dihapus.',
        ]);
    }
}
```

#### 6. Proteksi Rute: `routes/web.php`
```php
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::put('/courses/{course}', [CourseController::class, 'update'])
        ->name('courses.update');
        
    Route::delete('/courses/{course}', [CourseController::class, 'destroy'])
        ->name('courses.destroy');

    // Rute pendaftaran wajib melewati email verification
    Route::post('/courses/{course}/enroll', [CourseController::class, 'enroll'])
        ->middleware(['verified'])
        ->name('courses.enroll');
});
```
