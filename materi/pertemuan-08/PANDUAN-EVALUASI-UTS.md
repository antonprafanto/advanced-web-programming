# PANDUAN EVALUASI UJIAN TENGAH SEMESTER (UTS)
## Midterm Project Defense & Code Review (Milestone 1)
### Mata Kuliah: Pemrograman Web Lanjut (3 SKS)

---

## I. KETENTUAN UMUM EVALUASI UTS
1. **Model Ujian:** Ujian Tengah Semester diselenggarakan dalam bentuk **Midterm Project Defense & Code Review Tatap Muka** (bukan ujian tulis teori).
2. **Durasi Evaluasi:** **15 Menit** per tim proyek (2 - 3 mahasiswa).
3. **Batas Akhir Submission Repositori:** H-1 sebelum sesi jadwal kelas UTS dimulai.
4. **Git Release Tagging:** Tim wajib menandai commit final UTS dengan Git Tag resmi:
   ```bash
   git tag -a v0.5.0-uts -m "Release Milestone 1 UTS: Clean Architecture, RBAC & Storage"
   git push origin v0.5.0-uts
   ```
5. Seluruh anggota tim wajib hadir tepat waktu dan membawa laptop yang telah siap menjalankan aplikasi secara lokal (*local environment* siap demo tanpa kendala instalasi di tempat).

---

## II. CHECKLIST MINIMAL FITUR & ARSITEKTUR (MILESTONE 1)

Aplikasi yang dipresentasikan harus memenuhi 5 pilar arsitektur dasar yang telah dipelajari pada Minggu 1 s/d 7:

### 1. Clean Architecture & Pemisahan Layer (Minggu 2 & 5)
- [ ] **Skinny Controller:** Controller hanya bertindak sebagai orkestrator HTTP ($\le 25$ baris per method). Tidak ada logika SQL atau perhitungan bisnis menumpuk di Controller.
- [ ] **Form Request Validation:** Seluruh validasi input dibungkus dalam Form Request mandiri (`app/Http/Requests/`).
- [ ] **Service Layer:** Logika bisnis transaksi utama diisolasi di `app/Services/`. Service murni independen dari HTTP (tidak memanggil `$request`, `response()`, `redirect()`, atau `session()`).
- [ ] **Data Transfer Object (DTO):** Data masukan kompleks dari form dipetakan ke objek DTO bertipe data tegas (*strongly typed*) dengan PHP 8.x `readonly class`.

### 2. Database Engineering & Keamanan Transaksi (Minggu 3)
- [ ] **Skema Migrasi Terstruktur:** Menggunakan tipe data presisi, *Foreign Key Constraints*, dan *Indexes* pada kolom filter pencarian.
- [ ] **High-Performance Seeder:** Database terisi data realistis (minimal 500 - 1.000 record) menggunakan *Model Factories* dan *Seeder Chunk* yang berjalan cepat (< 5 detik).
- [ ] **ACID & Locking:** Alur perubahan saldo / kuota kritis dibungkus dalam `DB::transaction()` dan menggunakan *Pessimistic Locking* (`lockForUpdate()`).

### 3. Eloquent ORM Optimization (Minggu 4)
- [ ] **Relasi Kompleks:** Mengimplementasikan relasi relasional tingkat lanjut (*Polymorphic*, *Custom Pivot Model*, atau *Has-Many-Through*).
- [ ] **Bebas Masalah N+1:** Seluruh pemanggilan data berelasi menggunakan *Eager Loading* (`with()`). Dosen akan memeriksa via **Laravel Debugbar**; jika ditemukan query berulang $> 20$ untuk 1 halaman, nilai poin ini dipotong.

### 4. Otentikasi, Spatie RBAC & Model Policies (Minggu 6)
- [ ] **Multi-Role Scaffolding:** Sistem memiliki minimal 2 atau 3 level peran aktor yang terkelola via `spatie/laravel-permission` (misal: Admin, Dosen, Mahasiswa).
- [ ] **Resource Ownership Policy:** Logika otorisasi kepemilikan data dibungkus dalam *Model Policy* (`update`/`delete`). Dosen akan menguji: User B mencoba mengedit data milik User A via Postman/Browser dan wajib menghasilkan **HTTP 403 Forbidden**.
- [ ] **Super Admin Bypass:** Role admin tertinggi otomatis memiliki akses penuh via `Gate::before()` hook di `AppServiceProvider`.

### 5. Media & Multi-Disk Storage (Minggu 7)
- [ ] **Pemisahan Publik vs Privat:** Foto profil/avatar disimpan di disk `public` (WebP), sedangkan dokumen identitas/keuangan rahasia disimpan di disk `private` (`storage/app/private`).
- [ ] **Temporary Signed URL:** Dokumen privat hanya dapat diunduh melalui URL bertanda tangan kriptografis dengan batas waktu kedaluwarsa.
- [ ] **Malware & Filename Sanitization:** Nama berkas di-hash dengan UUID, tidak menyimpan nama asli klien langsung ke disk.

---

## III. ALUR PELAKSANAAN DEFENSE (15 MENIT)

```
┌────────────────────────────────────────────────────────────────────────┐
│ 00' - 03' (3 Menit): LIVE DEMO FITUR                                    │
│ • Menunjukkan login multi-role.                                        │
│ • Menunjukkan 1 alur transaksi bisnis utama & upload berkas.           │
├────────────────────────────────────────────────────────────────────────┤
│ 03' - 10' (7 Menit): ARSITEKTUR & CODE WALKTHROUGH                     │
│ • Membuka VS Code: FormRequest -> DTO -> Service -> Model.             │
│ • Membuka Model Policy & AppServiceProvider Gate hook.                 │
│ • Membuka browser: Inspeksi query SQL via Laravel Debugbar.            │
├────────────────────────────────────────────────────────────────────────┤
│ 10' - 15' (5 Menit): ORAL DEFENSE & LIVE CODING CHALLENGE              │
│ • Dosen mengajukan pertanyaan teknis kepada masing-masing anggota.     │
│ • Dosen meminta perubahan kecil langsung (misal: ubah batas waktu      │
│   signed URL atau tambahkan validasi baru).                            │
└────────────────────────────────────────────────────────────────────────┘
```

---

## IV. KEBIJAKAN INTEGRITAS AKADEMIK & AI/LLM

1. **Penggunaan AI Assistant:** Mahasiswa diperbolehkan menggunakan AI (seperti GitHub Copilot, ChatGPT, Gemini) sebagai asisten belajar dan *pair programming*.
2. **Kewajiban Penguasaan Kode:** Setiap anggota tim **WAJIB MAMPU MENJELASKAN SETIAP BARIS KODE** yang diserahkan. Alasan *"kode ini di-generate oleh AI dan saya tidak tahu cara kerjanya"* **TIDAK DITERIMA**.
3. **Pemeriksaan Kontribusi Git (*Git Insights*):**
   - Dosen akan memeriksa grafik kontribusi commit di GitHub (`Contributors Graph` & `git blame`).
   - Anggota tim yang pasif / tidak memiliki riwayat commit substansial (*free-rider*) akan diberikan nilai individu maksimal **40 (E)** meskipun timnya memiliki aplikasi yang baik.
4. **Sanksi Plagiarisme Antar-Tim:** Jika ditemukan dua tim mengumpulkan kode yang identik (hanya mengganti nama variabel/tema), **KEDUA TIM OTOMATIS DIBERIKAN NILAI 0 PADA UTS**.
