# MODUL PRAKTIKUM 04
## Topik: Deep Dive Eloquent ORM: Complex Relationships & N+1 Query Optimization

---

### I. TUJUAN PEMBELAJARAN
Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1. Merancang dan mengimplementasikan relasi basis data kompleks: **Many-to-Many dengan Custom Pivot Model**, **Has-Many-Through**, serta **Polymorphic Relations** (One-to-Many & Many-to-Many).
2. Mengamankan integritas tipe polimorfik menggunakan **Strict Morph Map** (`Relation::enforceMorphMap`).
3. Mengidentifikasi, mengukur, dan mengeliminasi masalah performa **N+1 Query Problem** menggunakan **Eager Loading**, **Constrained Eager Loading**, dan `withCount()`.
4. Mengaktifkan fitur **Strict Mode & Prevent Lazy Loading** (`Model::preventLazyLoading()`) di lingkungan lokal untuk mendeteksi *code smell* N+1 query secara otomatis.
5. Membangun **Local Scopes** dan **Global Scopes** untuk menghasilkan kueri data yang *clean*, modular, dan reusable.

---

### II. TEORI & KONSEP KUNCI

#### 1. Relasi Kompleks pada Eloquent ORM

##### A. Many-to-Many dengan Custom Pivot Model
Tabel pivot sering kali memiliki atribut tambahan (misal: tanggal penugasan, status, catatan). Alih-alih menggunakan pivot standar bawaan, kita buat model pivot kustom:

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

##### B. Polymorphic Relations (Relasi Polimorfik)
Satu model dapat memiliki relasi ke beberapa model lain menggunakan satu tabel yang sama.

**Kasus Nyata:** Fitur Komentar (`Comment`) yang bisa diberikan pada `Post` artikel maupun `Video` pembelajaran.

1. **Struktur Migration Komentar:**
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('body');
    // Menghasilkan commentable_type (string) dan commentable_id (unsignedBigInteger)
    $table->morphs('commentable'); 
    $table->timestamps();
});
```

2. **Definisi di Model `Comment`:**
```php
public function commentable()
{
    return $this->morphTo();
}
```

3. **Definisi di Model `Post` dan `Video`:**
```php
public function comments()
{
    return $this->morphMany(Comment::class, 'commentable');
}
```

##### C. Best Practice: Strict Morph Map
Secara default, Laravel menyimpan nama class lengkap (misal: `App\Models\Post`) di kolom `commentable_type`. Ini sangat berbahaya jika suatu saat namespace class di-refactor!

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
*Hasil:* Di database hanya tersimpan string pendek `'post'` atau `'video'`.

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
**Total:** $1 + 50 = 51$ Query SQL ke database hanya untuk menampilkan 1 halaman sederhana! Jika ada 1.000 buku, maka ada 1.001 query! Server database akan kehabisan *connection pool* dan aplikasi menjadi sangat lambat.

##### B. Cara Mendeteksi Otomatis: `preventLazyLoading()`
Mulai Laravel modern, kita bisa memerintahkan framework untuk **melempar Exception error** jika ada developer yang tidak sengaja menulis kode N+1 di lokal development:

Buka `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    // Hanya aktif di local / staging, dinonaktifkan di production agar web tidak crash
    Model::preventLazyLoading(! app()->isProduction());
}
```
Jika terjadi lazy loading di Blade/Controller, Laravel langsung memunculkan layar merah:  
*`Attempted to lazy load [author] on model [App\Models\Book] but lazy loading is disabled.`*

---

#### 3. Strategi Optimasi Eager Loading

##### A. Basic & Nested Eager Loading
Eager loading menggunakan `with()` untuk mengambil data relasi sekaligus via klausa `WHERE IN`:
```php
// Mengambil 50 buku dan seluruh penulisnya hanya dalam 2 query SQL!
$books = Book::with('author')->get();

// Nested Eager Loading: Ambil postingan, beserta komentar dan penulis komentarnya:
$posts = Post::with(['author', 'comments.user'])->get();
```

##### B. Constrained Eager Loading (Penyaringan Relasi)
Hanya memuat data relasi anak yang memenuhi kriteria tertentu:
```php
$posts = Post::with(['comments' => function ($query) {
    $query->where('is_approved', true)
          ->latest()
          ->take(5);
}])->get();
```

##### C. Agregasi Efisien: `withCount()`, `withSum()`, `withAvg()`
> [!TIP]
> **Jangan pernah memuat relasi hanya untuk menghitung jumlahnya!**  
> ❌ Buruk: `$post->comments->count()` (memuat ratusan objek komentar ke RAM hanya untuk dihitung).  
> ✅ Optimal: `Post::withCount('comments')->get()` (dihitung langsung di level SQL via subquery `SELECT COUNT(*)`).

Nilai hasil hitung otomatis tersedia di atribut `comments_count`:
```php
@foreach ($posts as $post)
    <span>Jumlah Komentar: {{ $post->comments_count }}</span>
@endforeach
```

---

#### 4. Reusable Query: Local Scopes & Global Scopes

##### A. Local Query Scopes
Menyimpan potongan kueri umum yang sering dipakai ke dalam method model berawalan `scope`:

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

    // Local Scope 2: Berdasarkan Kategori Populer (Menerima Argumen)
    public function scopePopular(Builder $query, int $minViews = 1000): void
    {
        $query->where('views_count', '>=', $minViews);
    }
}
```

Pemanggilan yang elegan dan ekspresif:
```php
$trendingPosts = Post::published()->popular(5000)->latest()->get();
```

##### B. Global Query Scopes
Otomatis menerapkan filter pada *setiap* query yang dipanggil ke model tersebut (misal: filter multi-tenant atau hanya menampilkan akun yang aktif):

```php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ActiveScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('is_active', true);
    }
}
```
Mendaftarkan scope di Model:
```php
use App\Models\Scopes\ActiveScope;

protected static function booted(): void
{
    static::addGlobalScope(new ActiveScope);
}
```
Bypass scope jika admin ingin melihat seluruh data termasuk yang non-aktif:
```php
$allUsers = User::withoutGlobalScope(ActiveScope::class)->get();
```

---

### III. LANGKAH PRAKTIKUM LABORATORIUM

#### Langkah 1: Eksperimen Deteksi N+1 di `AppServiceProvider`
Buka `app/Providers/AppServiceProvider.php`, aktifkan fitur pencegahan lazy loading:
```php
use Illuminate\Database\Eloquent\Model;

public function boot(): void
{
    Model::preventLazyLoading(! $this->app->isProduction());
}
```

---

#### Langkah 2: Merancang Skema Polimorfik Komentar
Buat migration tabel komentar polimorfik:
```bash
php artisan make:migration create_comments_table
```

Isi berkas migrasi:
```php
Schema::create('comments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->text('content');
    $table->morphs('commentable'); // commentable_type dan commentable_id
    $table->boolean('is_approved')->default(true);
    $table->timestamps();

    // Composite index untuk kecepatan pencarian komentar per entitas
    $table->index(['commentable_type', 'commentable_id', 'is_approved']);
});
```
Jalankan migrasi:
```bash
php artisan migrate
```

---

#### Langkah 3: Menghubungkan Relasi Model
Buka model `app/Models/Comment.php`:
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

Buka model `app/Models/Course.php`, tambahkan relasi polimorfik:
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
    $courses = Course::with(['category', 'comments.user'])
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
