# KONTRAK PERKULIAHAN & PANDUAN CAPSTONE PROJECT
## MATA KULIAH: PEMROGRAMAN WEB LANJUT (3 SKS)
### Program Studi S1 Teknik Informatika / Informatika

---

### I. KONTRAK PERKULIAHAN & ETIKA AKADEMIK

#### 1. Aturan Kehadiran & Partisipasi
- Kehadiran minimal mahasiswa adalah **75%** dari total pertemuan (maksimal 4 kali ketidakhadiran) sebagai syarat mengikuti evaluasi akhir (UAS).
- Keterlambatan maksimal toleransi adalah 15 menit.
- Perkuliahan berbasis **Hands-on Lab**: Mahasiswa diwajibkan membawa laptop dengan spesifikasi yang memadai untuk menjalankan PHP 8.2+, Node.js, dan database server lokal.

#### 2. Kebijakan Penggunaan AI & Integritas Akademik
- **Penggunaan AI (LLM / Copilot / ChatGPT):**
  - AI diperbolehkan sebagai **asisten belajar** (*pair programmer*), generator ide, pencari referensi sintaks, dan *code explainer*.
  - **Dilarang Keras:** *Blind copy-pasting* kode dari AI tanpa memahami arsitektur dan alur logikanya.
  - Setiap mahasiswa **wajib mampu menjelaskan baris per baris** kode yang mereka submit pada sesi *Code Review* dan wawancara teknis UTS/UAS. Jika tidak mampu menjelaskan kode yang dibuat, nilai komponen tersebut akan dianulir.
- **Plagiarisme Kode:**
  - Kode antar-kelompok dilarang keras identik (*copy-paste*).
  - Repositori proyek akan diaudit menggunakan *code similarity analyzer*. Plagiarisme otomatis berakibat pada pemberian nilai **E (Gagal)** bagi pihak yang memberi dan menerima salinan.

---

### II. STANDAR KERJA & GIT WORKFLOW

Setiap pengerjaan tugas dan proyek wajib dikelola menggunakan **Git & GitHub** dengan standar profesional:

1. **Struktur Branching:**
   - `main`: Branch produksi yang selalu stabil dan dapat dijalankan (*deployable*).
   - `staging` / `develop`: Branch integrasi fitur.
   - `feat/<nama-fitur>`: Branch pengembangan modul spesifik (misal: `feat/auth-sanctum`, `feat/export-queue`).
2. **Standar Conventional Commits:**
   Mahasiswa diwajibkan menggunakan format commit standar industri:
   - `feat: ...` (fitur baru)
   - `fix: ...` (perbaikan bug)
   - `refactor: ...` (penataan ulang struktur kode tanpa mengubah fungsi)
   - `test: ...` (penambahan automated test)
   - `docs: ...` (perubahan dokumentasi)
3. **Pemerataan Kontribusi Tim:**
   Setiap anggota kelompok **wajib memiliki riwayat commit aktif dan berimbang** di GitHub. Nilai akhir proyek bersifat individual berdasarkan kontribusi riil dan penguasaan kode.

---

### III. PANDUAN PROYEK TENGAH SEMESTER (UTS) — MILESTONE 1
**Target:** Membangun Aplikasi Web Monolith Berbasis Role & Clean Architecture.

#### 1. Ketentuan Umum:
- Dikerjakan secara berkelompok (2-3 mahasiswa).
- Menggunakan framework Laravel (versi 11.x atau 12.x).

#### 2. Batasan Teknis Minimal:
1. **Desain Database & Relasi:**
   - Minimal 6-8 tabel relasional yang saling terhubung.
   - Menggunakan Migration, Factory, dan Database Seeder lengkap dengan relasi bertingkat.
   - Memiliki relasi *Many-to-Many* dengan pivot table ber-atribut atau relasi *Polymorphic*.
2. **Clean Code & Architecture:**
   - **Zero Fat Controller:** Controller hanya bertugas menerima request dan mengembalikan response. Seluruh alur proses bisnis wajib didelegasikan ke **Service Layer**.
   - Validasi data terisolasi menggunakan **Form Request**.
3. **Otorisasi & Keamanan:**
   - Minimal 3 level peran (Roles), misal: *Super Admin, Staff/Manager, Regular User/Customer*.
   - Pembatasan hak akses berbasis **Policies & Gates** atau integrasi package RBAC.
4. **File Storage:**
   - Fitur upload file aman dengan validasi tipe berkas dan penggunaan *Storage Abstraction*.

---

### IV. PANDUAN CAPSTONE PROJECT (UAS) — MILESTONE 2
**Target:** Production-Grade Modern Web Application / API Ecosystem.

#### 1. Ketentuan Teknis Lanjutan:
Melanjutkan dan menyempurnakan proyek UTS dengan integrasi teknologi web mutakhir:

1. **RESTful API / Modern Frontend:**
   - Opsi A: Membangun endpoint RESTful API v1 lengkap dengan serialisasi **Eloquent API Resources** dan autentikasi token **Laravel Sanctum**.
   - Opsi B: Mengonversi frontend menjadi Modern Monolith berbasis **Inertia.js (React / Vue)**.
2. **Asynchronous Architecture:**
   - Minimal 1 proses berat yang didelegasikan ke **Background Queue & Jobs** (misal: pengiriman notifikasi email massal, generate dokumen laporan, kompresi berkas).
   - Penjadwalan tugas berkala menggunakan **Task Scheduling**.
3. **Real-Time Integration:**
   - Minimal 1 fitur interaktif *real-time* berbasis **WebSockets** (Laravel Reverb / Pusher), seperti notifikasi lonceng instan atau live tracking status.
4. **Automated Testing:**
   - Minimal 10-15 test cases otomatis (Unit & Feature Tests) menggunakan **Pest PHP / PHPUnit**.
5. **Deployment & CI/CD:**
   - Aplikasi berhasil di-deploy ke server cloud publik (VPS Linux dengan Nginx / PaaS seperti Railway/Fly.io) dengan domain dan SSL HTTPS aktif.
   - Repositori dilengkapi pipeline pengujian otomatis via **GitHub Actions**.

---

### V. RUBRIK ASESMEN PROYEK (OBE COMPLIANT)

| Dimensi Penilaian | Bobot | Deskripsi Skor Maksimal (A) |
| :--- | :---: | :--- |
| **Arsitektur & Clean Code** | 25% | Pemisahan concern sempurna (Service Layer, Form Request, DTO), kode rapi, bebas *code smells* dan N+1 query. |
| **Fungsionalitas & Kompleksitas** | 25% | Seluruh fitur berjalan tanpa bug; integrasi Queue, Real-time WebSockets, dan RBAC berfungsi sempurna. |
| **Software Quality & Testing** | 20% | Menulis automated test yang mencakup happy path & failure path; code coverage memadai. |
| **DevOps & Production Readiness** | 15% | Aplikasi live di server publik dengan SSL, README profesional, dan pipeline CI/CD hijau. |
| **Wawancara & Code Defense** | 15% | Setiap anggota tim mampu menjawab pertanyaan teknis mendalam tentang kode yang dibuatnya. |
