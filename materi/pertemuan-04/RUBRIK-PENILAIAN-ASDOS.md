# PEDOMAN PENILAIAN & RUBRIK EVALUASI (UNTUK DOSEN & ASDOS)
## PERTEMUAN 04: Polymorphic Relations, Eliminasi N+1 Query & Local Scopes
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

### I. PANDUAN PENILAIAN UMUM
- Dokumen ini adalah acuan resmi bagi Dosen dan Asisten Dosen (Asdos) dalam memeriksa submission [TUGAS-04.md](TUGAS-04.md).
- Total Nilai Maksimal: **100 Poin**.
- Mahasiswa wajib menyertakan bukti screenshot log kueri pada branch `feat/pertemuan-04`.

---

### II. RUBRIK DETAIL PENILAIAN

#### BAGIAN A: Relasi Polimorfik Ganda (Maks. 40 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Skema Polimorfik Comments & Tags** | 15 Poin | Tabel `comments` menggunakan `morphs('commentable')` dan tabel pivot `taggables` menggunakan `morphs('taggable')` beserta foreign key `tag_id`. |
| **Definisi Relasi di Model** | 15 Poin | Model `Comment` menggunakan `morphTo()`, Model `Article`/`Video` menggunakan `morphMany(Comment::class, 'commentable')` dan `morphToMany(Tag::class, 'taggable')`. |
| **Strict Morph Map di AppServiceProvider** | 10 Poin | Mendaftarkan pemetaan alias string (`article` dan `video`) via `Relation::enforceMorphMap()`. |

---

#### BAGIAN B: Eliminasi N+1 Query & Benchmarking Log (Maks. 35 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Aktivasi `preventLazyLoading`** | 10 Poin | `Model::preventLazyLoading(! app()->isProduction())` aktif dan tidak memicu exception saat endpoint diakses. |
| **Eager Loading & Agregasi `withCount`** | 15 Poin | Mengambil relasi via `with(['author', 'category', 'tags'])` dan menghitung komentar via `withCount('comments')`. Pengurangan nilai jika menghitung komentar via `$item->comments->count()` di Blade/RAM. |
| **Laporan Query Log Terbukti Efisien** | 10 Poin | Jumlah kueri yang tercatat di `DB::getQueryLog()` konsisten sedikit ($\le$ 5 kueri SQL) untuk 25 data lengkap. |

---

#### BAGIAN C: Local Query Scopes Reusable (Maks. 25 Poin)

| Kriteria Penilaian | Poin Maks. | Indikator Penilaian |
| :--- | :---: | :--- |
| **Scope `published()`** | 12.5 Poin | Method `scopePublished($query)` menyaring status publikasi dan tanggal tayang dengan benar. |
| **Scope Dinamis `trending()`** | 12.5 Poin | Method `scopeTrending($query, $minViews)` menerima argumen dinamis dan menyaring data dengan benar. |

---

### III. KUNCI JAWABAN STANDAR REFERENSI

#### 1. Registrasi Strict Morph Map & Prevent Lazy Loading (`app/Providers/AppServiceProvider.php`):
```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Models\Article;
use App\Models\Video;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // 1. Cegah N+1 Query Problem di environment development
        Model::preventLazyLoading(! $this->app->isProduction());

        // 2. Strict Morph Map untuk relasi polimorfik
        Relation::enforceMorphMap([
            'article' => Article::class,
            'video'   => Video::class,
        ]);
    }
}
```

#### 2. Definisi Model Polimorfik & Scope (`app/Models/Article.php`):
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    // Relasi Polimorfik One-to-Many ke Komentar
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Relasi Polimorfik Many-to-Many ke Tag
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Local Scope: Artikel Terbit
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')
              ->where('published_at', '<=', now());
    }

    // Local Scope Dinamis: Artikel Trending
    public function scopeTrending(Builder $query, int $minViews = 500): void
    {
        $query->where('views_count', '>=', $minViews);
    }
}
```

#### 3. Endpoint Query Teroptimasi (`app/Http/Controllers/MediaController.php`):
```php
namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    public function trending(): JsonResponse
    {
        DB::enableQueryLog();

        // Kueri Eager Loading Bebas N+1 Problem
        $articles = Article::published()
            ->trending(1000)
            ->with(['author:id,name,email', 'tags:id,name,slug'])
            ->withCount('comments')
            ->latest('views_count')
            ->take(25)
            ->get();

        return response()->json([
            'status'      => 'success',
            'query_count' => count(DB::getQueryLog()),
            'data'        => $articles,
        ]);
    }
}
```

---

### IV. PANDUAN PENGURANGAN NILAI (PENALTY)
- Memunculkan error `LazyLoadingViolationException` saat endpoint diuji: Pengurangan 20 poin.
- Menghitung komentar dengan cara memuat relasi ke RAM (`$item->comments->count()`): Pengurangan 15 poin.
- Tidak menyertakan bukti log query di `README.md`: Pengurangan 15 poin.
- Plagiarisme kode: **Nilai 0**.
