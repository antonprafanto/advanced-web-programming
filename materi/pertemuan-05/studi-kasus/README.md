# Berkas Studi Kasus: Monolithic Fat Controller vs Layered Architecture (DTO & Service)

Direktori ini memuat perbandingan arsitektural proses pendaftaran/checkout:

1. **`01-monolithic-fat-controller.php`**:
   - Contoh nyata controller monolitik sepanjang 100+ baris.
   - Melakukan validasi inline, otorisasi manual, query pengecekan duplikasi, manipulasi kupon diskon, transaksi database, panggilan API pembayaran, dan pengiriman email.
   - Kode ini tidak dapat di-reuse di API mobile atau background queue tanpa copy-paste.

2. **`02-layered-service-dto.php`**:
   - Refactoring berstandar arsitektur enterprise:
     - **Controller:** Ramping (15 baris), hanya menerima HTTP dan mengembalikan JSON.
     - **DTO:** Data bertipe ketat dan immutable (`readonly class`).
     - **Service Layer:** Mengisolasi logika bisnis dan transaksi ACID (`DB::transaction`).
     - **Interface:** Abstraksi payment gateway yang siap diganti atau di-mock saat testing.
