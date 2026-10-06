# MODUL PRAKTIKUM 03 (EDISI LENGKAP & REVISI)
## Topik: Database Engineering: Advanced Migration, Seeding, Model Factory, & ACID Concurrency

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang dan memodifikasi skema basis data relasional kompleks tanpa kehilangan data (*altering tables, foreign key cascade, composite indexing, soft deletes, dan polymorphic columns*).
2. Memahami perbedaan fatal dan risiko perintah migrasi (`migrate`, `migrate:rollback`, vs `migrate:fresh` di server produksi).
3. Menguasai **Model Factory**: State Transformations, Sequence, Factory Relationships (`has()` & `for()`), Lifecycle Hooks (`afterCreating`), serta lokalisasi data Indonesia (**Faker `id_ID`**).
4. Menerapkan strategi **High-Performance Seeding** (*batch insertion, chunking, idempotent seeding, dan bypass foreign key constraints saat truncate*).
5. Mengimplementasikan jaminan **ACID Database Transactions** dan **Pessimistic Locking (`lockForUpdate()`)** untuk mencegah *race condition* pada akses data konkuren (misal: berebut stok barang terakhir).

---

### II. TEORI & KONSEP KUNCI

#### 1. Advanced Migration Architecture & Siklus Skema

##### A. Relasi Foreign Key & Cascade Rules
```php
// Otomatis membuat kolom unsignedBigInteger 'category_id' yang merujuk ke tabel 'categories'
$table->foreignId('category_id')
      ->constrained()
      ->cascadeOnUpdate()
      ->cascadeOnDelete(); // atau ->nullOnDelete() jika data anak ingin dipertahankan
```

##### B. Indexing & Composite Keys (Optimasi Performa)
```php
// Index tunggal untuk kolom pencarian rutin
$table->string('email')->index();

// Composite Index: Sangat efisien untuk query multi-kolom
// SELECT * FROM orders WHERE user_id = ? AND status = ?
$table->index(['user_id', 'status']);

// Composite Unique Constraint: Mencegah duplikasi kombinasi kolom
// Siswa hanya boleh mengambil mata kuliah tertentu sekali per semester
$table->unique(['student_id', 'course_id', 'semester_id']);
```

##### C. Mengubah Skema pada Tabel yang Sudah Berisi Data (Altering Tables)
> [!CAUTION]
> **Jangan Pernah Mengedit File Migration Lama yang Sudah Dijalankan di Produksi!**  
> Buat migration baru dengan flag `--table`:
> ```bash
> php artisan make:migration add_phone_and_avatar_to_users_table --table=users
> ```

Wajib mengimplementasikan method `down()` secara sempurna agar dapat di-rollback tanpa merusak struktur:
```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone', 20)->nullable()->after('email');
        $table->string('avatar')->default('default.png')->after('phone');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['phone', 'avatar']);
    });
}
```

##### D. Perbedaan Perintah Migrasi & Peringatan Produksi
- `php artisan migrate`: Menjalankan file migration baru yang belum pernah dieksekusi.
- `php artisan migrate:rollback`: Membatalkan batch migrasi terakhir dengan mengeksekusi method `down()`.
- `php artisan migrate:fresh`: **BAHAYA!** Men-drop seluruh tabel di database dan menjalankan ulang dari awal. **HARAM dijalankan di server produksi!**

---

#### 2. Database Factories & Faker Localization

##### A. Konfigurasi Lokalisasi Faker Indonesia
Buka berkas `.env`:
```env
FAKER_LOCALE=id_ID
```
Data nama, alamat jalan, kota, dan nomor HP yang dihasilkan akan bernilai realistis lokal Indonesia.

##### B. Factory States & Lifecycle Hooks
```php
namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'invoice_code' => 'INV-' . strtoupper(fake()->bothify('????-#####')),
            'total_amount' => fake()->numberBetween(50_000, 2_000_000),
            'status'       => 'pending',
            'created_at'   => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    // State 1: Pesanan Lunas
    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    // Hook: Otomatis generate 3 Item Pesanan setiap kali 1 Order dibuat
    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            OrderItem::factory()->count(3)->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
```

##### C. Factory Relationships yang Bersih (`has` & `for`)
Laravel menyediakan sintaks intuitif untuk relasi antar-factory:
```php
// Membuat 1 User yang otomatis memiliki 5 Order
$user = User::factory()
    ->has(Order::factory()->count(5)->paid())
    ->create();

// Membuat 1 Order yang terkait dengan User tertentu
$order = Order::factory()
    ->for($user)
    ->create();
```

---

#### 3. High-Performance Seeding Strategy

##### A. Penanganan Truncate dengan Foreign Key Constraints
Ketika mengosongkan tabel pada seeder di database relasional (MySQL / PostgreSQL), RDBMS akan menolak truncate jika ada tabel anak yang merujuknya.  
Solusinya:
```php
use Illuminate\Support\Facades\Schema;

Schema::disableForeignKeyConstraints();
User::truncate();
Order::truncate();
Schema::enableForeignKeyConstraints();
```

##### B. Batch Chunk Insertion vs Naive Loop
Memanggil `Model::create()` di dalam perulangan loop 5.000 kali akan memicu 5.000 koneksi query INSERT terpisah.

**Teknik Optimal (Chunk Batch Insert):**
```php
$records = [];
for ($i = 1; $i <= 5000; $i++) {
    $records[] = [
        'name'       => fake()->name(),
        'email'      => fake()->unique()->safeEmail(),
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

// Potong per 500 baris dalam 1 transaksi
collect($records)->chunk(500)->each(function ($chunk) {
    \DB::table('users')->insert($chunk->toArray());
});
```

---

#### 4. Transaksi ACID & Penanganan Konkurensi (*Race Condition*)

##### A. Prinsip ACID
- **Atomicity:** Semua langkah berhasil, atau seluruh langkah dibatalkan total.
- **Consistency:** Aturan integritas (saldo $\ge 0$, stok $\ge 0$) tidak boleh dilanggar.
- **Isolation:** Transaksi yang berjalan bersamaan tidak boleh saling mengacaukan data belum ter-commit.
- **Durability:** Data yang sudah di-commit tersimpan permanen di storage.

##### B. Bahaya Concurrency: Berebut Stok Terakhir
Bayangkan stok tiket tersisa **1 buah**. Dua pengguna menekan tombol "Beli" di milidetik yang sama:
- User A mengecek stok: Ada (1).
- User B mengecek stok: Ada (1).
- Keduanya sukses beli, stok menjadi **-1**!

##### C. Solusi: Pessimistic Locking (`lockForUpdate()`)
`lockForUpdate()` memerintahkan database untuk mengunci baris data hingga transaksi selesai. User lain harus menunggu giliran (*antre*):

```php
use Illuminate\Support\Facades\DB;

$order = DB::transaction(function () use ($productId, $quantity, $userId) {
    // Kunci baris produk ini dari modifikasi proses lain hingga transaksi commit
    $product = Product::lockForUpdate()->findOrFail($productId);

    if ($product->stock < $quantity) {
        throw new \Exception("Maaf, stok baru saja habis dibeli pengguna lain!");
    }

    // Potong stok dengan aman
    $product->decrement('stock', $quantity);

    // Buat pesanan
    return Order::create([
        'user_id'    => $userId,
        'product_id' => $product->id,
        'quantity'   => $quantity,
    ]);
}, 5); // Otomatis retry hingga 5 kali jika terjadi Database Deadlock
```

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Eksperimen Uji Kecepatan Seeding
Buka terminal repositori ini dan jalankan perbandingan performa batch insertion:
```bash
php materi/pertemuan-03/studi-kasus/02-optimized-factory-chunk.php
```
Amati bagaimana 5.000 data masuk dalam hitungan milidetik.

---

#### Langkah 2: Merancang Migration dengan Indexing & Foreign Key
Buat migration skema kategori dan kursus:
```bash
php artisan make:migration create_categories_and_courses_tables
```

Lengkapi kode migration:
```php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('slug')->unique();
    $table->timestamps();
});

Schema::create('courses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('slug')->unique();
    $table->integer('price')->default(0);
    $table->integer('stock')->default(100);
    $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
    $table->softDeletes();
    $table->timestamps();

    // Composite Index untuk optimasi query pencarian
    $table->index(['category_id', 'status']);
});
```
Jalankan migrasi:
```bash
php artisan migrate
```

---

#### Langkah 3: Membuat Factory dengan State & Sequence
Buat Model & Factory:
```bash
php artisan make:factory CourseFactory --model=Course
```

Lengkapi `CourseFactory.php`:
```php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'category_id' => Category::factory(),
            'title'       => $title,
            'slug'        => Str::slug($title) . '-' . fake()->unique()->numberBetween(100, 9999),
            'price'       => fake()->randomElement([0, 50000, 150000, 250000]),
            'stock'       => fake()->numberBetween(10, 100),
            'status'      => 'published',
        ];
    }

    public function free(): static
    {
        return $this->state(fn () => ['price' => 0]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
```

---

#### Langkah 4: Menjalankan Seeder Terstruktur
Buka `database/seeders/DatabaseSeeder.php`:
```php
use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Category::truncate();
        Course::truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Kategori Master
        $categories = collect(['Web Development', 'Mobile Apps', 'Cyber Security', 'Cloud Computing'])
            ->map(fn ($name) => Category::create(['name' => $name, 'slug' => \Str::slug($name)]));

        // 2. Kursus Menggunakan Factory
        $categories->each(function ($cat) {
            Course::factory()->count(10)->create(['category_id' => $cat->id]);
            Course::factory()->count(2)->free()->create(['category_id' => $cat->id]);
        });
    }
}
```

Jalankan perintah seeder:
```bash
php artisan db:seed
```

Periksa data di database via `php artisan tinker`:
```php
App\Models\Course::count();
App\Models\Course::where('price', 0)->count();
```

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-03.md](TUGAS-03.md).
