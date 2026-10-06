# MODUL PRAKTIKUM 03
## Topik: Database Engineering: Schema Migration, Seeding & Model Factory

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang skema basis data relasional tingkat lanjut menggunakan **Advanced Migration** (*foreign key cascade, composite indexing, soft deletes, dan polymorphic columns*).
2. Membangun **Model Factory** lengkap dengan fitur **State Transformations**, **Sequences**, serta lokalisasi data Indonesia (**Faker `id_ID`**).
3. Mengimplementasikan strategi **High-Performance Seeding** (*batch insertion, chunking, dan idempotent seeding*) untuk menghasilkan ribuan data simulasi dalam hitungan detik.
4. Menerapkan jaminan **ACID Database Transactions** (`DB::transaction`) untuk mencegah inkonsistensi data dan *race conditions* pada proses transaksi multi-tabel.

---

### II. TEORI & KONSEP KUNCI

#### 1. Advanced Migration Architecture

Migration adalah sistem kontrol versi (*version control*) untuk skema basis data aplikasi.

##### A. Relasi Foreign Key & Cascade Rules
Penulisan ringkas dan aman di Laravel modern:
```php
// Otomatis membuat foreign key 'category_id' yang merujuk ke tabel 'categories'
$table->foreignId('category_id')
      ->constrained()
      ->cascadeOnUpdate()
      ->cascadeOnDelete(); // atau ->nullOnDelete() jika kolom nullable
```

##### B. Indexing & Composite Keys (Optimasi Performa Query)
Index sangat krusial ketika tabel memuat puluhan ribu record:
```php
// Index tunggal untuk kolom yang sering dicari / difilter
$table->string('email')->index();

// Composite Index: Sangat efisien untuk query multi-kolom
// Contoh: SELECT * FROM orders WHERE user_id = 1 AND status = 'completed';
$table->index(['user_id', 'status']);

// Composite Unique Constraint: Mencegah duplikasi kombinasi kolom
// Contoh: Mahasiswa tidak boleh mengambil mata kuliah yang sama di semester yang sama
$table->unique(['student_id', 'course_id', 'academic_year']);
```

##### C. Soft Deletes
Menyembunyikan record tanpa menghapusnya secara fisik dari hard drive (menambahkan kolom `deleted_at`):
```php
$table->softDeletes();
```
Di Model:
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model {
    use SoftDeletes;
}
```

##### D. Polymorphic Columns
Menyimpan relasi ke berbagai tipe model dalam satu struktur kolom (misal: Komentar bisa dimiliki oleh `Post`, `Video`, atau `Product`):
```php
// Menghasilkan kolom 'commentable_type' (string) dan 'commentable_id' (unsignedBigInteger)
$table->morphs('commentable');
```

---

#### 2. Database Factories & Faker Localization

Factory bertindak sebagai cetak biru (*blueprint*) untuk memproduksi data tiruan realistis untuk testing dan simulasi.

##### A. Mengatur Faker ke Bahasa Indonesia (`id_ID`)
Agar nama, alamat, nomor telepon, dan kota yang dihasilkan bernilai realistis lokal Indonesia:
Buka berkas `config/app.php` (atau atur di `.env`):
```env
FAKER_LOCALE=id_ID
```

##### B. Anatomi Model Factory & State Transformations
Buat factory via artisan:
```bash
php artisan make:factory OrderFactory --model=Order
```

```php
namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(), // Otomatis generate User jika tidak disediakan
            'invoice_code' => 'INV-' . strtoupper(fake()->bothify('????-#####')),
            'total_amount' => fake()->numberBetween(50_000, 2_000_000),
            'status'       => 'pending',
            'created_at'   => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    // State 1: Pesanan Lunas
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    // State 2: Pesanan Dibatalkan
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
```

Pemanggilan di Seeder:
```php
// Membuat 50 pesanan yang sudah berstatus 'paid'
Order::factory()->count(50)->paid()->create();
```

##### C. Factory Sequences
Mengisi data yang berulang atau berurutan:
```php
$users = User::factory()
    ->count(10)
    ->sequence(
        ['role' => 'admin'],
        ['role' => 'dosen'],
        ['role' => 'mahasiswa'],
    )
    ->create();
```

---

#### 3. High-Performance Seeding Strategy

> [!WARNING]
> **Masalah Seeder Lambat:** Memanggil `Model::create()` di dalam perulangan loop 5.000 kali memakan waktu menit bahkan jam karena Laravel menjalankan 5.000 query `INSERT` terpisah serta memicu event observer di setiap baris.

##### Solusi: Batch Insertion via Chunking
```php
// Siapkan data dalam memory koleksi
$records = [];
for ($i = 1; $i <= 5000; $i++) {
    $records[] = [
        'name'       => fake()->name(),
        'email'      => fake()->unique()->safeEmail(),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

// Masukkan dalam batch potongan 500 record per query INSERT
collect($records)->chunk(500)->each(function ($chunk) {
    \DB::table('users')->insert($chunk->toArray());
});
```
*Hasil:* 5.000 record berhasil di-insert hanya dalam waktu 1-2 detik!

##### Idempotent Seeding
Agar seeder aman dijalankan berulang-ulang tanpa menghasilkan data ganda (*duplicate key error*), gunakan `firstOrCreate` atau `updateOrCreate`:
```php
Role::firstOrCreate(
    ['name' => 'super_admin'],
    ['display_name' => 'Super Administrator']
);
```

---

#### 4. Database Transactions & Jaminan ACID

Dalam transaksi bisnis nyata, satu aktivitas pengguna sering melibatkan lebih dari satu tabel.  
**Contoh Kasus:** Pengguna melakukan *Checkout*:
1. Tabel `orders`: Menambahkan 1 record pesanan.
2. Tabel `order_items`: Menambahkan rincian barang.
3. Tabel `products`: Mengurangi stok produk.
4. Tabel `wallets`: Mengurangi saldo pengguna.

Jika langkah 1 sampai 3 berhasil, tetapi server mati di langkah 4, maka toko rugi (barang keluar tanpa pemotongan saldo). **Inilah pelanggaran prinsip Atomicity (A dalam ACID).**

##### Penerapan Transaksi Otomatis (`DB::transaction`)
```php
use Illuminate\Support\Facades\DB;

try {
    DB::transaction(function () use ($orderData, $items, $user) {
        // 1. Simpan Header Order
        $order = Order::create($orderData);

        // 2. Simpan Items & Kurangi Stok
        foreach ($items as $item) {
            $order->items()->create($item);

            $product = Product::lockForUpdate()->find($item['product_id']);
            
            if ($product->stock < $item['quantity']) {
                throw new \Exception("Stok produk {$product->name} tidak mencukupi!");
            }

            $product->decrement('stock', $item['quantity']);
        }

        // 3. Potong Saldo Dompet
        $user->wallet()->decrement('balance', $order->total_amount);
    }, 5); // Angka 5 adalah jumlah percobaan ulang otomatis jika terjadi database deadlock

    return response()->json(['message' => 'Transaksi berhasil diproses secara aman.']);

} catch (\Throwable $e) {
    // Jika ada error/exception di dalam closure di atas, SELURUH perubahan otomatis di-ROLLBACK!
    return response()->json(['error' => $e->getMessage()], 422);
}
```

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Merancang Skema Migration
Jalankan perintah pembuatan migration:
```bash
php artisan make:migration create_courses_and_enrollments_tables
```

Buka file migration yang baru dibuat di `database/migrations/`:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Kategori
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 2. Tabel Kursus
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->integer('price')->default(0);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->softDeletes();
            $table->timestamps();

            // Composite Index untuk query pencarian kursus aktif pada kategori tertentu
            $table->index(['category_id', 'status']);
        });

        // 3. Tabel Pendaftaran (Enrollments)
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            // Composite Unique: Mencegah user mendaftar di kursus yang sama 2 kali
            $table->unique(['user_id', 'course_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('categories');
    }
};
```

Jalankan migrasi:
```bash
php artisan migrate
```

---

#### Langkah 2: Membuat Factory dengan States
Buat Model & Factory Course:
```bash
php artisan make:factory CourseFactory
```

Isi berkas `database/factories/CourseFactory.php`:
```php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'category_id' => Category::factory(),
            'title'       => $title,
            'slug'        => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 999),
            'description' => fake()->paragraph(),
            'price'       => fake()->randomElement([0, 99000, 149000, 299000]),
            'status'      => 'published',
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'price' => 0,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'status' => 'draft',
        ]);
    }
}
```

---

#### Langkah 3: Eksekusi High-Performance Seeding
Buka `database/seeders/DatabaseSeeder.php`:
```php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Kategori Master
        $categories = collect(['Web Development', 'Mobile Apps', 'Cyber Security', 'Data Science', 'Cloud DevOps'])
            ->map(function ($name) {
                return Category::firstOrCreate(
                    ['name' => $name],
                    ['slug' => \Str::slug($name)]
                );
            });

        // 2. Buat 50 Kursus dengan relasi Kategori acak
        $categories->each(function ($category) {
            Course::factory()->count(10)->create([
                'category_id' => $category->id,
            ]);
        });

        // 3. Tambahkan 10 kursus gratis khusus
        Course::factory()->count(10)->free()->create();
    }
}
```

Jalankan seeder:
```bash
php artisan db:seed
```

Verifikasi data yang berhasil digenerate via `php artisan tinker`:
```php
App\Models\Course::count();
App\Models\Course::where('price', 0)->count();
```

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-03.md](TUGAS-03.md).
