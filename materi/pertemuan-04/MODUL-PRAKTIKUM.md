# MODUL PRAKTIKUM 04 (EDISI LENGKAP & REVISI)
## Topik: Deep Dive Eloquent ORM: Complex Relationships, Query Optimization, & N+1 Prevention

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang dan mengimplementasikan relasi basis data kompleks: **Many-to-Many dengan Custom Pivot Model**, **Has-Many-Through**, serta **Polymorphic Relations** (One-to-Many & Many-to-Many).
2. Mengamankan integritas tipe polimorfik menggunakan **Strict Morph Map** (`Relation::enforceMorphMap`).
3. Mengidentifikasi, mengukur, dan mengeliminasi masalah performa **N+1 Query Problem** menggunakan **Eager Loading**, **Constrained Eager Loading**, **Subquery Selects (`addSelect`)**, dan `withCount()`.
4. Mengaudit performa aplikasi menggunakan **Laravel Debugbar** dan mengaktifkan **Strict Mode (`Model::preventLazyLoading()`)** di lingkungan lokal.
5. Membangun **Local Scopes** dan **Global Scopes** untuk menghasilkan kueri data yang *clean*, modular, dan reusable.

---

### II. TEORI & KONSEP KUNCI

#### 1. Relasi Kompleks pada Eloquent ORM

##### A. Many-to-Many dengan Custom Pivot Model
Tabel pivot sering kali memiliki atribut tambahan (misal: tanggal penugasan, status, catatan). Alih-alih menggunakan pivot standar bawaan, kita buat model pivot kustom turunan `Illuminate\Database\Eloquent\Relations\Pivot`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    protected $table = 'role_user';

    protected $casts = [
        'assigned_at' => 'datetime',
        'is_active'   => 'boolean',
    ];
}
```

Definisi di Model `User`:
```php
public function roles()
{
    return $this->belongsToMany(Role::class)
        ->using(RoleUser::class)
        ->withPivot(['assigned_at', 'is_active'])
        ->withTimestamps();
}
```

##### B. Relasi Has-Many-Through
Mengakses relasi jarak jauh melalui model perantara.  
**Contoh:** Satu `Department` memiliki banyak `Lecturer`, dan setiap `Lecturer` memiliki banyak `Course`. Department bisa langsung mengakses seluruh Course:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Department extends Model
{
    public function courses(): HasManyThrough
    {
        // hasManyThrough(TargetModel, IntermediateModel)
        return $this->hasManyThrough(Course::class, Lecturer::class);
    }
}
```

##### C. Relasi Polimorfik (One-to-Many & Many-to-Many)
1. **One-to-Many Polymorphic (Contoh: Komentar untuk Post & Video):**
   - Migration: `$table->morphs('commentable');` (kolom `commentable_id` & `commentable_type`).
   - Model `Comment`: `$this->morphTo();`
   - Model `Post` / `Video`: `$this->morphMany(Comment::class, 'commentable');`

2. **Many-to-Many Polymorphic (Contoh: Tagging untuk Post & Video):**
   - Migration tabel pivot `taggables`:
     ```php
     Schema::create('taggables', function (Blueprint $table) {
         $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
         $table->morphs('taggable'); // taggable_id & taggable_type
         $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
     });
     ```
   - Model `Post` / `Video`:
     ```php
     public function tags()
     {
         return $this->morphToMany(Tag::class, 'taggable');
     }
     ```
   - Model `Tag`:
     ```php
     public function posts()
     {
         return $this->morphedByMany(Post::class, 'taggable');
     }
     ```

##### D. Best Practice: Strict Morph Map
Secara default, Laravel menyimpan nama class lengkap (misal: `App\Models\Post`) di kolom `commentable_type`. Ini sangat berbahaya jika struktur folder atau namespace di-refactor!

**Solusi:** Daftarkan alias morph di `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Database\Eloquent\Relations\Relation;

public function boot(): void
{
    Relation::enforceMorphMap([
        'post'  => \App\Models\Post::class,
        'video' => \App\Models\Video::class,
    ]);
}
```

---

#### 2. The Notorious N+1 Query Problem

##### A. Mengapa N+1 Query Terjadi?
Bayangkan Anda ingin menampilkan daftar 50 buku beserta nama penulisnya di halaman web.

*Kode Naif (Lazy Loading):*
```php
// Controller:
$books = Book::all(); // Query 1: Mengambil 50 buku

// Blade View:
@foreach ($books as $book)
    <p>{{ $book->title }} - Penulis: {{ $book->author->name }}</p> 
    {{-- Query N: Dieksekusi 1 kali per iterasi buku (50 query tambahan!) --}}
@endforeach
```
**Total:** $1 + 50 = 51$ Query SQL ke database hanya untuk menampilkan 1 halaman sederhana!

##### B. Deteksi Otomatis dengan `preventLazyLoading()`
Buka `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    // Melempar exception jika terjadi Lazy Loading di local development
    Model::preventLazyLoading(! app()->isProduction());
}
```

---

#### 3. Strategi Optimasi Eager Loading

##### A. Basic & Nested Eager Loading
Mengambil data relasi sekaligus menggunakan klausa `WHERE IN`:
```php
// Mengambil 50 buku dan seluruh penulisnya hanya dalam 2 query SQL:
$books = Book::with('author')->get();

// Nested Eager Loading: Ambil postingan beserta komentar dan user penulisnya:
$posts = Post::with(['author', 'comments.user'])->get();
```

##### B. Eager Loading Spesifik Kolom (*Sparse Fieldsets*)
> [!WARNING]
> **Jebakan Klasik:** Saat membatasi kolom relasi (misal: `author:name`), Anda **WAJIB menyertakan kolom `id`** (dan foreign key jika ada), jika tidak relasi akan menghasilkan `null`!
> ```php
> // ❌ SALAH: Relasi author akan bernilai NULL!
> Book::with('author:name')->get();
> 
> // ✅ BENAR: Kolom 'id' disertakan
> Book::with('author:id,name,avatar')->get();
> ```

##### C. Constrained Eager Loading (Penyaringan Relasi)
```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('is_approved', true)
          ->latest()
          ->take(5);
}])->get();
```

##### D. Agregasi Efisien: `withCount()`, `withSum()`, `withAvg()`
Alih-alih memuat seluruh baris relasi ke RAM hanya untuk dihitung (`$post->comments->count()`), gunakan `withCount()` yang dieksekusi langsung di SQL:
```php
$posts = Post::withCount('comments')->get();
// Nilai otomatis tersedia di atribut: $post->comments_count
```

##### E. Subquery Selects (`addSelect()`)
Teknik senior untuk mengambil satu data spesifik dari tabel relasi tanpa harus me-load objek relasi penuh atau melakukan JOIN tabel yang berat:
```php
use App\Models\Comment;

$posts = Post::addSelect([
    'latest_comment_body' => Comment::select('content')
        ->whereColumn('commentable_id', 'posts.id')
        ->where('commentable_type', 'post')
        ->latest()
        ->take(1)
])->get();

// Nilai langsung dapat diakses sebagai atribut virtual:
// $post->latest_comment_body
```

---

#### 4. Reusable Query: Local Scopes & Global Scopes

##### A. Local Query Scopes
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    // Local Scope 1: Postingan Terbit
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->whereNotNull('published_at');
    }

    // Local Scope 2: Dinamis dengan parameter
    public function scopePopular(Builder $query, int $minViews = 1000): void
    {
        $query->where('views_count', '>=', $minViews);
    }
}
```
Pemanggilan:
```php
$trendingPosts = Post::published()->popular(5000)->latest()->get();
```

##### B. Global Query Scopes & Anonymous Global Scope
Filter otomatis yang diterapkan pada setiap kueri model:
```php
// Anonymous Global Scope di method booted()
protected static function booted(): void
{
    static::addGlobalScope('active', function (Builder $builder) {
        $builder->where('is_active', true);
    });
}
```
Bypass global scope saat dibutuhkan:
```php
$allRecords = Post::withoutGlobalScope('active')->get();
```

---

#### 5. Audit Kueri dengan Laravel Debugbar

Untuk memantau jumlah kueri dan penggunaan memori secara visual di browser:
```bash
composer require barryvdh/laravel-debugbar --dev
```
Setelah terpasang, saat membuka aplikasi di browser, panel Debugbar akan muncul di bagian bawah layar:
- Tab **Queries**: Menampilkan seluruh kueri SQL, waktu eksekusi dalam milidetik, dan **menandai kueri duplikat/N+1 dengan warna merah** secara otomatis.

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Aktivasi Strict Mode di `AppServiceProvider`
Buka `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

public function boot(): void
{
    // Cegah N+1 di local development
    Model::preventLazyLoading(! $this->app->isProduction());

    // Strict Morph Map
    Relation::enforceMorphMap([
        'course'  => \App\Models\Course::class,
    ]);
}
```

---

#### Langkah 2: Merancang Skema Polimorfik Komentar
Buat migration tabel komentar polimorfik:
```bash
php artisan make:migration create_comments_table
```

```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('content');
    $table->morphs('commentable'); // commentable_type dan commentable_id
    $table->boolean('is_approved')->default(true);
    $table->timestamps();

    // Composite index untuk pencarian cepat
    $table->index(['commentable_type', 'commentable_id', 'is_approved']);
});
```
Jalankan migrasi:
```bash
php artisan migrate
```

---

#### Langkah 3: Menghubungkan Relasi Polimorfik
Buka `app/Models/Comment.php`:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = ['user_id', 'content', 'is_approved'];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Buka `app/Models/Course.php`:
```php
use Illuminate\Database\Eloquent\Relations\MorphMany;

public function comments(): MorphMany
{
    return $this->morphMany(Comment::class, 'commentable');
}
```

---

#### Langkah 4: Benchmarking Profiling Query Log
Buka `routes/web.php`, buat rute pengujian untuk menghitung jumlah query:
```php
use App\Models\Course;
use Illuminate\Support\Facades\DB;

Route::get('/benchmark-query', function () {
    DB::enableQueryLog();
    $startTime = microtime(true);

    // Kueri Teroptimasi: Eager loading kategori, komentar, dan hitung jumlah komentar
    $courses = Course::with(['category:id,name,slug', 'comments.user:id,name'])
        ->withCount('comments')
        ->published()
        ->take(30)
        ->get();

    $executionTime = microtime(true) - $startTime;
    $queries = DB::getQueryLog();

    return response()->json([
        'total_data'     => $courses->count(),
        'total_query'    => count($queries),
        'execution_time' => round($executionTime * 1000, 2) . ' ms',
        'query_log'      => $queries,
    ]);
});
```

Akses `http://127.0.0.1:8000/benchmark-query` dan amati bahwa meskipun mengambil 30 kursus beserta relasi berjenjangnya, jumlah kueri yang dieksekusi tetap konstan dan sangat minim (hanya 3-4 kueri SQL)!

---

### IV. LEMBAR TUGAS MANDIRI
Kerjakan soal penugasan terstruktur yang tercantum pada [TUGAS-04.md](TUGAS-04.md).
