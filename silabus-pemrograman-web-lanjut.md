# RENCANA PEMBELAJARAN SEMESTER (RPS)
## MATA KULIAH: PEMROGRAMAN WEB LANJUT (ADVANCED WEB PROGRAMMING)

---

### I. IDENTITAS MATA KULIAH
| Item | Informasi |
| :--- | :--- |
| **Program Studi** | S1 Teknik Informatika / Informatika |
| **Nama Mata Kuliah** | Pemrograman Web Lanjut (*Advanced Web Programming*) |
| **Bobot SKS** | 3 SKS (1 SKS Teori, 2 SKS Praktikum / Lab) |
| **Semester** | Ganjil / Genap (Semester 5 atau 6) |
| **Mata Kuliah Prasyarat** | Pemrograman Web Dasar (PHP & MySQL Dasar), Basis Data, Pemrograman Berorientasi Objek (PBO) |
| **Profil Lulusan / Peran** | Full-stack Web Developer, Backend Engineer, API Specialist |

---

### II. DESKRIPSI SINGKAT MATA KULIAH
Mata kuliah ini dirancang sebagai jembatan strategis bagi mahasiswa yang telah memahami dasar-dasar pemrograman web (HTML, CSS, JS, Native PHP, serta pengantar MVC dan sekilas Laravel) menuju standar industri modern (*industry-grade development*). 

Fokus utama mata kuliah ini bukan sekadar *"bisa membuat CRUD"*, melainkan penguasaan arsitektur aplikasi berbasis web modern:
1. **Clean Architecture & Design Pattern** pada ekosistem modern PHP/Laravel (Service Layer, DTO, Repository).
2. **Pembangunan RESTful API & API Security** yang scalable dan secure (Sanctum/JWT, Resource Collection, Rate Limiting).
3. **Integrasi Frontend Modern** (Inertia.js dengan React/Vue atau SPA API-driven).
4. **Asynchronous Architecture** (Queue, Background Jobs, Task Scheduling, dan WebSockets/Real-time broadcasting).
5. **Software Quality & Deployment**: Automated Testing (Pest/PHPUnit), OWASP Security Best Practices, serta dasar CI/CD dan Deployment ke Cloud/VPS.

---

### III. CAPAIAN PEMBELAJARAN LULUSAN (CPL) & CPMK

#### Capaian Pembelajaran Lulusan (CPL) yang Dibebankan:
- **CPL 1 (Sikap & Etika):** Menunjukkan kemandirian, tanggung jawab tim, dan kepatuhan terhadap kode etik pengembangan perangkat lunak (security & data privacy).
- **CPL 2 (Pengetahuan):** Menguasai konsep teoretis arsitektur web modern, komunikasi jaringan protokol HTTP/REST, serta pola desain perangkat lunak (*design patterns*).
- **CPL 3 (Keterampilan Khusus):** Mampu merancang, mengimplementasikan, menguji, dan men-deploy aplikasi web skala menengah-besar menggunakan framework modern dengan standar kode bersih (*clean code*).

#### Capaian Pembelajaran Mata Kuliah (CPMK):
- **CPMK 1:** Mahasiswa mampu mengidentifikasi perbedaan arsitektur native vs framework serta mengonfigurasi lingkungan pengembangan modern berbasis dependency management (Composer, PHP 8.x).
- **CPMK 2:** Mahasiswa mampu mendesain skema basis data kompleks, mengoptimalkan query, dan mengimplementasikan relasi relasional menggunakan Object Relational Mapping (ORM) tanpa *N+1 query problem*.
- **CPMK 3:** Mahasiswa mampu menerapkan arsitektur kode bersih (*Separation of Concerns*) dengan memanfaatkan Form Request, Service Layer, dan Authorization Policies.
- **CPMK 4:** Mahasiswa mampu merancang dan mengamankan RESTful API berstandar industri dengan token-based authentication (Sanctum), rate limiting, dan dokumentasi API.
- **CPMK 5:** Mahasiswa mampu mengimplementasikan asynchronous processing (Queues, Jobs), event-driven architecture, dan real-time communications (WebSockets).
- **CPMK 6:** Mahasiswa mampu menulis pengujian otomatis (*automated testing*), menerapkan mitigasi kerentanan keamanan web (OWASP Top 10), dan mengotomatisasi proses deployment aplikasi ke server produksi.

---

### IV. STRATEGI & METODE PEMBELAJARAN
1. **Case-Based Learning (CBL):** Membedah studi kasus nyata (misal: penanganan lonjakan transaksi, kerentanan kebocoran data, pemrosesan antrean ribuan invoice).
2. **Project-Based Learning (PjBL):** Mahasiswa membangun produk web / API utuh secara berkelompok (2-3 orang) dari minggu pertama hingga minggu ke-16 (*Capstone Project*).
3. **Live Coding & Code Review:** Sesi interaktif membedah pola penulisan kode (*idiomatic code*), refactoring, serta analisis *code smells*.

---

### V. RINCIAN RENCANA PEMBELAJARAN MINGGUAN (16 MINGGU)

```
SEMESTER PROGRESSION ROADMAP:
[Mg 1-4: Core Mastery & ORM] ──> [Mg 5-7: Architecture, Auth & Files] ──> [Mg 8: UTS Milestone 1]
                                                                                │
[Mg 16: UAS Showcase] <── [Mg 14-15: Testing & DevOps] <── [Mg 11-13: Frontend, Queue, Realtime] <── [Mg 9-10: REST API & Sanctum]
```

---

#### MINGGU 1: Transisi dari Native PHP ke Modern PHP & Ekosistem Laravel
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu menganalisis kelemahan pemrograman web prosedural/native, memahami fitur modern PHP 8.x (type-hinting, match expression, constructor promotion), serta mengorkestrasi dependensi menggunakan Composer.
- **Materi Pembelajaran:**
  - Evaluasi kode native PHP: Masalah spaghetti code, maintainability, dan celah keamanan umum.
  - Modern PHP 8.x Features: Named arguments, Typed Properties, Nullsafe operator, Attributes.
  - Setup Modern Development Environment: PHP 8.x, Composer, Laravel Installer, Git Workflow.
  - Anatomi Framework Laravel: Siklus hidup request (*Request Lifecycle*), Kernel, Service Providers, dan konfigurasi `.env`.
- **Aktivitas & Praktikum:** Setup project baru Laravel versi LTS/terbaru, eksplorasi lifecycle request via `artisan`, dan Git commit convention standar.
- **Bentuk Penilaian:** Verifikasi instalasi environment dan tugas mandiri: refactor 1 fungsi native PHP menjadi PHP 8.x OOP.

---

#### MINGGU 2: Advanced Routing, Request Handling & Custom Middleware
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu merancang routing aplikasi yang terstruktur, mengamankan request flow dengan custom middleware, dan memvalidasi payload request secara terisolasi.
- **Materi Pembelajaran:**
  - Advanced Routing: Explicit & Implicit Route Model Binding, Scoped Bindings, Fallback Routes, Subdomain Routing.
  - Controllers Pattern: Single Action Controller (`__invoke`), Resource Controller, Nested Resources.
  - Form Request Validation: Custom rules, dynamic error messages, authorization logic di Form Request.
  - Middleware Pipeline: Global vs Route vs Group Middleware, Terminable Middleware, Custom Auth & Role Guard Middleware.
- **Aktivitas & Praktikum:** Membuat sistem pendaftaran multi-step dengan validasi kompleks pada Form Request dan proteksi route menggunakan custom middleware (misal: `EnsureProfileIsComplete`).
- **Bentuk Penilaian:** Praktikum modul validasi & middleware.

---

#### MINGGU 3: Database Engineering: Schema Migration, Seeding & Factory
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu merancang skema database relasional tingkat lanjut, mengelola versi basis data (migrations), dan mengenerate jutaan dummy data secara efisien untuk keperluan simulasi.
- **Materi Pembelajaran:**
  - Advanced Migration: Foreign key cascades, composite primary/indexes, soft deletes, polymorphic columns.
  - Database Factories & Faker: State methods, sequence data, relasi bertingkat di model factory.
  - Seeding Strategy: Idempotent seeder, chunking data insertion untuk performa.
  - Database Transactions: Penanganan *race condition* dan integritas data ACID (`DB::transaction`).
- **Aktivitas & Praktikum:** Merancang skema e-commerce/akademik kompleks dengan minimal 8 tabel berelasi, seed data 5.000 record menggunakan Factory.
- **Bentuk Penilaian:** Evaluasi skema database, file migration, dan integritas seeder.

---

#### MINGGU 4: Deep Dive Eloquent ORM: Complex Relationships & Query Optimization
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu memodelkan relasi kompleks (Many-to-Many with pivot attributes, Polymorphic) serta mengatasi problem performa database (*N+1 Problem*).
- **Materi Pembelajaran:**
  - Relasi Kompleks: Many-to-Many dengan custom pivot model, Has-Many-Through, One-to-Many Polymorphic, Many-to-Many Polymorphic (contoh: Taggable, Commentable).
  - Query Optimization: Eager loading (`with()`), Lazy eager loading (`loadMissing()`), `withCount()`, subquery selects (`addSelect()`).
  - Mendeteksi & Mengatasi *N+1 Query Problem* menggunakan Laravel Debugbar / Telescope.
  - Local & Global Query Scopes: Soft deleting, tenant filtering, published scopes.
- **Aktivitas & Praktikum:** Profiling performa query sebelum dan sesudah eager loading pada dataset ribuan record; implementasi relasi polymorphic pada fitur komentar/like.
- **Bentuk Penilaian:** Rubrik perbandingan performa query (waktu eksekusi & memory usage).

---

#### MINGGU 5: Enterprise Architecture: Service Layer, DTO & Repository Pattern
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu menerapkan prinsip SOLID dan mereduksi kompleksitas kode dengan memisahkan business logic dari controller (*Fat Controller anti-pattern*).
- **Materi Pembelajaran:**
  - Masalah *Fat Controller, Skinny Model*: Dampak terhadap maintainability dan unit testing.
  - Service Layer Pattern: Mengisolasi alur proses bisnis murni (contoh: checkout, pendaftaran mahasiswa, kalkulasi tagihan).
  - Data Transfer Objects (DTO): Struktur data bertipe (*strongly typed*) antar-layer.
  - Inversion of Control & Dependency Injection: Laravel Service Container, Binding Interfaces to Implementations.
- **Aktivitas & Praktikum:** Refactoring modul CRUD monolitik menjadi arsitektur berlapis: `Controller -> FormRequest -> DTO -> Service -> Model`.
- **Bentuk Penilaian:** Review kode hasil refactoring berbasis prinsip Single Responsibility Principle (SRP).

---

#### MINGGU 6: Advanced Authentication, Authorization, RBAC & Security Scaffolding
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu menerapkan otentikasi aman dan sistem otorisasi multi-role berbasis kebijakan (*Policy* dan *Role-Based Access Control* / RBAC).
- **Materi Pembelajaran:**
  - Modern Auth Starter Kits: Laravel Breeze (Blade/Inertia).
  - Authorization Mechanism: Gates vs Policies. Kapan menggunakan Gate dan kapan menggunakan Policy.
  - Role-Based Access Control (RBAC): Implementasi manual vs penggunaan package industri (`spatie/laravel-permission`).
  - Fitur Pengamanan Tambahan: Email verification, Password reset flow, Two-Factor Authentication (2FA) concept.
- **Aktivitas & Praktikum:** Mengimplementasikan sistem RBAC dengan minimal 3 level aktor (Admin, Pengajar, Mahasiswa) di mana tiap role memiliki hak aksi yang diatur ketat via Model Policy.
- **Bentuk Penilaian:** Uji fungsionalitas pembatasan hak akses antar-peran.

---

#### MINGGU 7: File Management, Media Handling, & Review Milestone Proyek
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu mengelola asset media secara aman pada multi-disk storage (lokal & cloud), melakukan validasi keamanan file upload, dan mematangkan arsitektur proyek tengah semester.
- **Materi Pembelajaran:**
  - File Storage Abstraction: Konsep Filesystem Flysystem, Local disk, Public disk (`storage:link`), dan Cloud Object Storage (S3 / Supabase Storage).
  - Keamanan Upload File: Validasi MIME-type, malware prevention, hashing file names, secure download via signed URLs.
  - Image Processing on the Fly: Resize, watermark, dan konversi ke WebP menggunakan library modern (Intervention Image).
  - Konsolidasi materi & persiapan evaluasi UTS.
- **Aktivitas & Praktikum:** Membangun fitur upload dokumen sensitif (KTP/Ijazah) yang hanya bisa diunduh oleh pemilik melalui Temporary Signed URL; review progress proyek UTS.
- **Bentuk Penilaian:** Penilaian modul upload file dan asistensi proposal arsitektur UTS.

---

#### MINGGU 8: UJIAN TENGAH SEMESTER (UTS)
- **Bentuk Evaluasi:** Evaluasi Proyek Tahap 1 (*Midterm Project Defense / Code Review*).
- **Indikator Penilaian:**
  - Penerapan clean architecture (Form Request, Service Layer, tidak ada logic menumpuk di Controller).
  - Desain database & ORM yang optimal (bebas N+1 query problem, relasi polymorphic/many-to-many berjalan baik).
  - Implementasi RBAC dan Authorization Policies yang solid.
  - Pengelolaan media storage yang aman.
  - Kerapian commit Git dan dokumentasi README.

---

#### MINGGU 9: RESTful API Engineering & Eloquent API Resources
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu merancang arsitektur API berstandar internasional, mengelola serialisasi JSON payload secara konsisten, dan menerapkan versioning API.
- **Materi Pembelajaran:**
  - Prinsip RESTful Architecture: HTTP Verbs (GET, POST, PUT, PATCH, DELETE), Status Codes (200, 201, 204, 400, 401, 403, 404, 422, 500).
  - API Versioning Strategy: URL path versioning (`/api/v1/`), header versioning.
  - Eloquent API Resources & Resource Collections: Data transformation, data wrapping, hiding sensitive fields, conditional attributes.
  - Standarisasi JSON Response Structure: format sukses, pagination metadata, dan standarisasi penanganan pesan error.
- **Aktivitas & Praktikum:** Mengonversi modul backend yang ada menjadi endpoint RESTful API v1 lengkap dengan pagination dan format response standar.
- **Bentuk Penilaian:** Validasi kesesuaian endpoint API via Postman / Insomnia Collection.

---

#### MINGGU 10: API Security, Authentication (Sanctum/JWT), & Rate Limiting
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu mengamankan komunikasi API menggunakan token-based authentication, mencegah serangan brute-force dengan rate limiter, serta mengatur CORS.
- **Materi Pembelajaran:**
  - Statefull vs Stateless Authentication.
  - Laravel Sanctum: Personal Access Tokens (mobile app/third-party) vs SPA Cookie-based Session.
  - Token Abilities & Token Scopes: Memberikan granular permission pada access token.
  - Rate Limiting & Throttling: Menghalau serangan DDOS/brute-force pada API endpoints.
  - Cross-Origin Resource Sharing (CORS) configuration.
- **Aktivitas & Praktikum:** Membangun sistem login API, issuing token dengan abilities khusus, penanganan token revocation/logout, dan pengujian rate limiting (429 Too Many Requests).
- **Bentuk Penilaian:** Uji penetrasi sederhana API (autentikasi, otorisasi token, dan limitasi request).

---

#### MINGGU 11: Modern Frontend Integration: Monolith Modern dengan Inertia.js (React / Vue)
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu menghubungkan Laravel backend dengan framework frontend reaktif modern (React / Vue) menggunakan paradigma Inertia.js tanpa kompleksitas overhead client-side routing.
- **Materi Pembelajaran:**
  - Paradigma Web Modern: Traditional MPA vs SPA vs Monolith Modern (Inertia.js).
  - Arsitektur Inertia.js: Bagaimana Inertia menjembatani controller Laravel langsung dengan komponen React/Vue (tanpa membuat REST API manual).
  - Shared Data, Inertia Props, Flash Messages, dan Form Handling di frontend.
  - Pengenalan singkat alternatif ekosistem: Laravel Livewire (Fullstack PHP) vs Inertia.js (Kapan memilih salah satu).
- **Aktivitas & Praktikum:** Membangun dashboard interaktif berbasis Inertia.js + React/Vue yang menampilkan data reaktif, modal form, dan validasi error secara seamless.
- **Bentuk Penilaian:** Evaluasi integrasi interaktivitas UI frontend dengan data dari Laravel backend.

---

#### MINGGU 12: Asynchronous Processing: Queue, Background Jobs & Task Scheduling
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu mendesain sistem komputasi latar belakang (*background processing*) untuk menjaga latensi aplikasi tetap rendah pada operasi berat.
- **Materi Pembelajaran:**
  - Konsep Synchronous vs Asynchronous Processing: Mengapa user tidak boleh menunggu proses I/O berat.
  - Queue Architecture: Queue drivers (Database, Redis), dispatching jobs, job payloads.
  - Handling Failures: Retry attempts, timeout, exponential backoff, dead letter queue (`failed_jobs`).
  - Task Scheduling: Otomasi cron job melalui Laravel Console Scheduler (laporan harian, sinkronisasi berkala).
- **Aktivitas & Praktikum:** Membuat sistem pengiriman email massal / pembuatan file PDF laporan keuangan berat yang di-dispatch ke Background Queue dengan Redis/Database driver.
- **Bentuk Penilaian:** Demonstrasi antrean proses: eksekusi job, monitoring queue worker, dan penanganan failed job.

---

#### MINGGU 13: Real-Time Web: Event-Driven Architecture & WebSockets
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu mengimplementasikan arsitektur *event-driven* dan komunikasi dua arah secara *real-time* menggunakan WebSockets.
- **Materi Pembelajaran:**
  - Konsep Event-Driven: Events, Listeners, dan Event Subscribers.
  - HTTP Polling vs Server-Sent Events (SSE) vs WebSockets.
  - Broadcasting Events di Laravel: Public Channels, Private Channels, dan Presence Channels.
  - Implementasi WebSocket server: Laravel Reverb (native WebSocket server di ekosistem Laravel) atau Pusher / Soketi, dan client-side Laravel Echo.
- **Aktivitas & Praktikum:** Membangun fitur notifikasi instan *real-time* atau *live chat / order tracking* yang langsung muncul di layar pengguna tanpa refresh halaman.
- **Bentuk Penilaian:** Demonstrasi fungsionalitas real-time broadcast antar multi-user session.

---

#### MINGGU 14: Automated Testing & Software Quality Assurance (Pest / PHPUnit)
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu menulis kode pengujian otomatis (*unit & feature test*) untuk menjamin kestabilan dan regresi sistem.
- **Materi Pembelajaran:**
  - Prinsip Software Testing: Unit Test vs Feature/Integration Test vs E2E Test.
  - Framework Testing Modern: Pengenalan PEST PHP (sintaks elegan) & PHPUnit.
  - Database Testing: Database transactions, `RefreshDatabase` trait, seeding dalam test.
  - HTTP Tests & Mocking: Testing endpoint API (status code, JSON structure), mocking external service/job.
  - Konsep dasar Test-Driven Development (TDD): Red - Green - Refactor.
- **Aktivitas & Praktikum:** Menulis minimal 10 test case otomatis (mencakup happy path dan failure path) untuk modul autentikasi dan transaksi API.
- **Bentuk Penilaian:** Laporan eksekusi testing dan cakupan (*code coverage*) pengujian.

---

#### MINGGU 15: Web Security (OWASP Top 10), Caching, & Production Deployment (CI/CD)
- **Kemampuan Akhir (Sub-CPMK):** Mahasiswa mampu memitigasi kerentanan keamanan web, mengoptimalkan kecepatan server dengan caching, dan men-deploy aplikasi secara otomatis ke server produksi.
- **Materi Pembelajaran:**
  - Web Security Checklist: Mitigasi OWASP Top 10 (CSRF, XSS, SQLi, Mass Assignment, Sensitive Data Exposure, Secure Headers).
  - Performance Optimization: Redis Caching (Query cache, Redis tag cache), Artisan optimization (`config:cache`, `route:cache`, `view:cache`).
  - Dasar CI/CD: Otomasi running test & linter via GitHub Actions.
  - Production Deployment: Konsep containerization (Docker dasar) atau deploy ke Linux VPS (Nginx + PHP-FPM + SSL Let's Encrypt) / Modern PaaS (Railway, Fly.io).
- **Aktivitas & Praktikum:** Audit keamanan aplikasi, konfigurasi pipeline GitHub Actions untuk automated testing, dan deploy versi release ke hosting/VPS cloud publik.
- **Bentuk Penilaian:** Hasil audit security checklist dan live URL deployment yang berfungsi dengan SSL aktif.

---

#### MINGGU 16: UJIAN AKHIR SEMESTER (UAS)
- **Bentuk Evaluasi:** *Final Capstone Project Defense & Showcase*.
- **Mekanisme:** Presentasi kelompok, demonstrasi aplikasi live, peninjauan kode (*code review* menyeluruh), dan sesi tanya-jawab individu.
- **Parameter Penilaian:**
  1. Kompleksitas & fungsionalitas sistem (Clean code, Service Layer, API / Inertia).
  2. Implementasi fitur lanjut (Queue, WebSockets / Real-time, Security).
  3. Kelengkapan Automated Testing (Pest/PHPUnit).
  4. Sukses deployment ke server publik (Production-ready).
  5. Penguasaan materi secara individual saat tanya jawab teknis.

---

### VI. SISTEM PENILAIAN & EVALUASI

Penilaian mengacu pada prinsip Outcome-Based Education (OBE) dengan proporsi:

| Komponen Penilaian | Bobot | Deskripsi |
| :--- | :---: | :--- |
| **Aktivitas Partisipatif & Kuis Lab** | 15% | Keaktifan live-coding, pemecahan masalah di kelas, dan kuis berkala |
| **Tugas Pemrograman Terstruktur** | 20% | Tugas praktikum mingguan (Modul ORM, Refactoring Service Layer, API) |
| **Ujian Tengah Semester (UTS)** | 25% | Proyek Tahap 1: Arsitektur Monolith, Clean Code, Complex Database & RBAC |
| **Ujian Akhir Semester (UAS)** | 40% | Proyek Akhir Tahap 2 (*Capstone*): Full-stack/API, Queue, Real-time, Testing, Live Deploy |
| **TOTAL** | **100%** | |

#### Kriteria Konversi Nilai:
| Rentang Nilai | Nilai Huruf | Bobot | Kategori |
| :---: | :---: | :---: | :--- |
| $\ge 85$ | **A** | 4.00 | Sangat Istimewa (Standar Junior-Mid Engineer) |
| $80 - 84$ | **A-** | 3.75 | Istimewa |
| $75 - 79$ | **B+** | 3.25 | Sangat Baik |
| $70 - 74$ | **B** | 3.00 | Baik (Memenuhi seluruh standar minimal industri) |
| $65 - 69$ | **B-** | 2.75 | Cukup Baik |
| $60 - 64$ | **C+** | 2.25 | Cukup |
| $55 - 59$ | **C** | 2.00 | Kurang |
| $< 55$ | **D / E** | $\le 1.00$ | Tidak Lulus |

---

### VII. TECH STACK & TOOLS YANG DIGUNAKAN

1. **Core Language & Runtime:** PHP 8.2+ / 8.3+
2. **Framework:** Laravel 11.x / 12.x (versi terkini)
3. **Database:** MySQL / PostgreSQL & Redis (Caching & Queue)
4. **Development Tools:** VS Code / PhpStorm, Git & GitHub, Postman / Thunder Client / Insomnia
5. **Frontend Layer:** Blade, Tailwind CSS, Inertia.js (React / Vue)
6. **Testing Suite:** Pest PHP / PHPUnit
7. **Real-time Engine:** Laravel Reverb / Pusher
8. **Deployment Target:** Linux VPS (Ubuntu, Nginx), Docker, atau Cloud PaaS (Railway / Render / Fly.io)

---

### VIII. REFERENSI & RUJUKAN PEMBELAJARAN

1. **Dokumentasi Resmi:**
   - [Laravel Official Documentation](https://laravel.com/docs)
   - [Inertia.js Documentation](https://inertiajs.com/)
   - [Pest PHP Documentation](https://pestphp.com/)
2. **Buku & Panduan Arsitektur:**
   - Martin, Robert C. (2017). *Clean Architecture: A Craftsman's Guide to Software Structure and Design*. Prentice Hall.
   - Pertsch, Brent. (2023). *Domain-Driven Design with Laravel*.
   - Freek Van der Herten & Brent Roose. (2022). *Front Line PHP: Building modern applications with PHP 8*. Spatie.
   - Fowler, Martin. (2002). *Patterns of Enterprise Application Architecture*. Addison-Wesley.
3. **Standar Keamanan:**
   - OWASP Foundation. *OWASP Top 10 Web Application Security Risks*.
