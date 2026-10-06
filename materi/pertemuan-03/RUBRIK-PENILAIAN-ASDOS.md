# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 03: Advanced Migration, Model Factory, Seeding & Transaksi ACID
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-03.md](TUGAS-03.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan bukti screenshot eksekusi di `README.md` pada branch `feat/pertemuan-03`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Perancangan Skema & Migration 8 Tabel (Maks. 40 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Kelengkapan 8 Tabel Relasional** | 15 Poin | Seluruh 8 tabel (`users, categories, products, product_images, orders, order_items, payments, product_reviews`) berhasil dibuat dan termigrasi tanpa error. |
| **Integritas Foreign Key & Cascades** | 10 Poin | Foreign key menggunakan `foreignId()->constrained()` dengan aturan cascade yang tepat (misal: order_items terhapus jika order dihapus). |
| **Indexing & Unique Constraints** | 10 Poin | Memiliki minimal 2 Composite Index dan 1 Composite Unique (misal: ulasan unik per user dan produk). |
| **Implementasi Soft Deletes** | 5 Poin | Tabel `products` menggunakan `$table->softDeletes()` dan model menggunakan trait `SoftDeletes`. |

---

#### BAGIAN B: Model Factory & State Transformations (Maks. 30 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Lokalisasi Faker Indonesia (`id_ID`)** | 10 Poin | Data nama orang, kota, atau alamat yang digenerate menggunakan lokalisasi Indonesia. |
| **Implementasi State di ProductFactory** | 10 Poin | State `outOfStock()` dan `discounted()` berjalan sesuai spesifikasi saat dipanggil. |
| **Implementasi State di OrderFactory** | 10 Poin | State `paid()` dan `cancelled()` berjalan sesuai spesifikasi. |

---

#### BAGIAN C: High-Speed Seeding & Transaksi ACID (Maks. 30 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Kecepatan Batch Seeding 5.000 Data** | 15 Poin | Menggunakan teknik chunk batch insert. Waktu eksekusi cepat (< 10 detik). Pengurangan nilai jika masih memakai loop `Model::create()` satu per satu (> 30 detik). |
| **Integritas Transaksi ACID (`DB::transaction`)** | 15 Poin | Transaksi berhasil di-rollback secara otomatis ketika terjadi simulasi stok tidak mencukupi (tidak ada record menggantung / *orphan data*). |

---

### III. KUNCI JAWABAN STANDAR REFERENSI

#### 1. Implementasi Factory State (`database/factories/ProductFactory.php`):
```php
namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'name'        => ucwords($name),
            'slug'        => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 9999),
            'price'       => fake()->numberBetween(10_000, 500_000),
            'stock'       => fake()->numberBetween(5, 100),
            'is_active'   => true,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    public function discounted(): static
    {
        return $this->state(fn (array $attrs) => [
            'price' => (int) ($attrs['price'] * 0.8), // Diskon 20%
        ]);
    }
}
```

#### 2. Implementasi High-Speed Batch Seeding (`database/seeders/ProductSeeder.php`):
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryId = DB::table('categories')->value('id') ?? 1;
        $now = now();
        $batch = [];

        for ($i = 1; $i <= 5000; $i++) {
            $name = fake()->words(3, true);
            $batch[] = [
                'category_id' => $categoryId,
                'name'        => ucwords($name),
                'slug'        => Str::slug($name) . "-$i-" . Str::random(5),
                'price'       => fake()->numberBetween(15_000, 300_000),
                'stock'       => fake()->numberBetween(10, 50),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];
        }

        // Chunk per 500 baris dalam satu transaksi
        DB::transaction(function () use ($batch) {
            collect($batch)->chunk(500)->each(function ($chunk) {
                DB::table('products')->insert($chunk->toArray());
            });
        });
    }
}
```

#### 3. Implementasi Transaksi ACID Checkout:
```php
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;

class CheckoutService
{
    public function execute(int $userId, array $cartItems): Order
    {
        return DB::transaction(function () use ($userId, $cartItems) {
            $totalAmount = 0;

            // 1. Buat Header Order
            $order = Order::create([
                'user_id'      => $userId,
                'invoice_code' => 'INV-' . strtoupper(Str::random(10)),
                'status'       => 'pending',
                'total_amount' => 0,
            ]);

            // 2. Loop Items & Lock Baris Database
            foreach ($cartItems as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \DomainException("Stok produk {$product->name} habis/tidak mencukupi.");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'price'      => $product->price,
                    'quantity'   => $item['quantity'],
                    'subtotal'   => $subtotal,
                ]);

                // Kurangi Stok
                $product->decrement('stock', $item['quantity']);
            }

            $order->update(['total_amount' => $totalAmount]);

            return $order;
        }, 5);
    }
}
```

---

### IV. PANDUAN PENGURANGAN NILAI (PENALTY)
- Seeder 5.000 data memakan waktu > 30 detik (karena memakai looping single insert): Pengurangan 15 poin.
- Tidak menyertakan bukti screenshot terminal di `README.md`: Pengurangan 15 poin.
- Foreign key tidak memiliki cascade rule / relasi rusak: Pengurangan 10 poin.
- Plagiarisme kode: **Nilai 0**.
