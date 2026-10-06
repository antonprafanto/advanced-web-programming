# TUGAS PRAKTIKUM 06
## Topik: Role-Based Access Control (Spatie), Model Policies & Pengamanan Akun

---

### I. DESKRIPSI TUGAS
Tugas ini bertujuan melatih mahasiswa dalam merancang sistem keamanan otorisasi tingkat lanjut: mengintegrasikan package industri `spatie/laravel-permission` untuk tata kelola multi-role, menerapkan kebijakan akses berbasis kepemilikan data (*resource ownership*) via Model Policies, serta mengamankan alur transaksi sensitif menggunakan verifikasi email.

---

### II. SOAL STUDI KASUS: SISTEM LMS MULTI-ROLE KAMPUS

Anda diminta membangun modul otorisasi hak akses untuk Learning Management System (LMS) kampus dengan 3 level aktor:

#### Bagian A: Arsitektur Spatie RBAC & Seeding (Bobot 35%)
1. Pasang package `spatie/laravel-permission` dan tambahkan trait `HasRoles` pada Model `User`.
2. Buat Seeder mandiri `RoleAndPermissionSeeder` yang mengenerate:
   - **Permissions:**
     - `manage users`
     - `create courses`
     - `edit courses`
     - `delete courses`
     - `enroll courses`
     - `grade assignments`
   - **Roles:**
     - `super-admin`: Hak akses absolut (tidak perlu diberi permission manual, otomatis bypass via Gate hook).
     - `dosen`: Memiliki permission `create courses`, `edit courses`, `delete courses`, dan `grade assignments`.
     - `mahasiswa`: Memiliki permission `enroll courses`.
3. Buat minimal 3 akun dummy (1 Super Admin, 1 Dosen, 1 Mahasiswa) lengkap dengan assignment role masing-masing.

---

#### Bagian B: Model Policy Kepemilikan Data (`CoursePolicy`) (Bobot 40%)
1. Buat Policy: `php artisan make:policy CoursePolicy --model=Course`.
2. Terapkan logika otorisasi:
   - `update(User $user, Course $course)`:
     - Dosen hanya boleh mengedit kursus jika kolom `instructor_id` pada kursus cocok dengan ID user dosen tersebut.
   - `delete(User $user, Course $course)`:
     - Dosen hanya boleh menghapus kursus jika ia adalah pemiliknya DAN kursus tersebut belum memiliki mahasiswa terdaftar (`enrollments()->count() === 0`).
3. Daftarkan hook **Super Admin Bypass** di `AppServiceProvider::boot()` menggunakan `Gate::before()` agar akun dengan role `super-admin` selalu otomatis diizinkan pada seluruh aksi.

---

#### Bagian C: Proteksi Rute, Controller & Email Verification (Bobot 25%)
1. **Email Verification:**
   - Aktifkan interface `MustVerifyEmail` pada Model `User`.
   - Pasang middleware `verified` pada rute pendaftaran kursus (`POST /courses/{course}/enroll`).
2. **Controller Otorisasi:**
   - Gunakan `Gate::authorize('update', $course)` pada controller `CourseController@update` dan `CourseController@destroy`.
   - Buktikan bahwa Dosen B akan mendapatkan respons **HTTP 403 Forbidden** jika mencoba mengedit kursus milik Dosen A.

---

### III. KETENTUAN PENGUMPULAN
1. Tugas dikerjakan pada repositori praktikum masing-masing mahasiswa di branch `feat/pertemuan-06`.
2. Gunakan format *Conventional Commits* (misal: `feat: pasang spatie rbac dan buat seeder roles`, `feat: buat course policy dan super admin bypass`).
3. Sertakan file `README.md` yang memuat bukti tangkapan layar (*screenshot*):
   - Bukti eksekusi seeder roles & permissions di terminal.
   - Bukti pengujian otorisasi sukses (Dosen A mengedit kursus miliknya).
   - Bukti respons gagal **HTTP 403 Forbidden** (Dosen B mencoba mengedit kursus milik Dosen A).
   - Bukti Super Admin berhasil mengedit kursus milik siapa saja (Bypass).
4. Batas pengumpulan: H-1 sebelum Pertemuan 07 dimulai.
