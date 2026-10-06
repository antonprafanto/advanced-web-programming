# SKENARIO PEMBELAJARAN (LESSON PLAN / RPP)
## PERTEMUAN 05: Enterprise Architecture: Service Layer, DTO & Dependency Injection
### Alokasi Waktu: 3 SKS x 50 Menit = 150 Menit

---

### I. DISTRIBUSI ALOKASI WAKTU

```
[000 - 015'] Pembukaan & Review Evaluasi Tugas 04 (Polymorphic & N+1 Problem)
[015 - 055'] Sesi Teori: Dekonstruksi Fat Controller, Service Layer, DTO, & IoC Container
[055 - 065'] Istirahat Singkat / Diskusi Interaktif
[065 - 095'] Live Demo Dosen: Refactoring Controller 120 Baris Menjadi Arsitektur Berlapis
[095 - 135'] Hands-on Lab Mahasiswa: Implementasi DTO, Service Layer, & Interface Binding
[135 - 150'] Evaluasi Hasil Praktikum, Penjelasan Tugas 05, & Penutupan
```

---

### II. DETAIL AKTIVITAS PEMBELAJARAN

#### Sesi 1: Pembukaan & Review Tugas 04 (Menit 00 – 15)
- **Aktivitas Dosen:**
  - Menyapa mahasiswa dan memeriksa presensi.
  - Membahas evaluasi Tugas 04: betapa drastisnya dampak N+1 query problem dan pentingnya `Model::preventLazyLoading()`.
  - **Pertanyaan Pemantik:** *"Jika bos kalian meminta agar fitur pendaftaran kursus web bisa dijalankan juga lewat Bot Telegram atau CLI background tanpa browser, bisakah controller kalian langsung dipakai? Mengapa jawabannya TIDAK?"*

#### Sesi 2: Pemaparan Teori Interaktif (Menit 15 – 55)
- **Aktivitas Dosen:**
  - Membuka panduan materi di [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Mengulas anti-pattern *Fat Controller*: mengapa menaruh semua logika di controller membuat aplikasi rapuh dan tidak bisa di-reuse.
  - Menjelaskan aturan emas **Service Layer (HTTP-Agnostic)**: Service tidak boleh menerima objek `$request` atau memanggil helper `redirect()` / `response()`.
  - Mengenalkan **Data Transfer Object (DTO)**: keunggulan *strongly-typed immutable object* dibanding array asosiatif rapuh.
  - Menjelaskan konsep **Action Classes** untuk proses berfokus tunggal.
  - Menjelaskan prinsip **Dependency Inversion**: membuat Interface untuk layanan eksternal (Payment Gateway) dan mendaftarkan binding-nya di Laravel Service Container.

#### Sesi 3: Live Coding Demonstrasi oleh Dosen (Menit 65 – 95)
- **Aktivitas Dosen:**
  - Menampilkan [01-monolithic-fat-controller.php](studi-kasus/01-monolithic-fat-controller.php) yang memuat 120 baris kode berantakan.
  - Memandu langkah refactoring bertahap:
    1. Membuat kelas DTO immutable `EnrollmentData`.
    2. Membuat interface `PaymentGatewayInterface` dan implementasi dummy-nya.
    3. Mendaftarkan binding di `AppServiceProvider.php`.
    4. Memindahkan seluruh alur kalkulasi dan transaksi database ke `CourseEnrollmentService`.
    5. Merampingkan controller menjadi hanya 15 baris seperti pada [02-layered-service-dto.php](studi-kasus/02-layered-service-dto.php).
- **Aktivitas Mahasiswa:**
  - Mengamati transisi alur data dan mencatat poin-poin isolasi logika bisnis.

#### Sesi 4: Hands-on Lab Praktikum Mandiri Mahasiswa (Menit 95 – 135)
- **Aktivitas Mahasiswa:**
  - Mahasiswa membuka [MODUL-PRAKTIKUM.md](MODUL-PRAKTIKUM.md).
  - Membuat interface pembayaran dan binding di `AppServiceProvider`.
  - Membuat DTO `EnrollmentData` dengan constructor promotion.
  - Membangun `CourseEnrollmentService` lengkap dengan validasi duplikasi dan `DB::transaction`.
  - Membuat controller invokable dan menguji eksekusi via browser/Postman.
- **Aktivitas Dosen & Asisten Lab (Asdos):**
  - Membantu mahasiswa yang masih tergoda memanggil `request()` atau `session()` di dalam Service class.

#### Sesi 5: Evaluasi, Penjelasan Tugas, & Penutupan (Menit 135 – 150)
- **Aktivitas Dosen:**
  - Memberikan umpan balik terhadap kerapian arsitektur kode mahasiswa.
  - Memaparkan instruksi [TUGAS-05.md](TUGAS-05.md).
  - Memberi gambaran awal materi pertemuan 6: *Advanced Authentication, Authorization Policies & RBAC (Spatie)*.
