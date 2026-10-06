# Berkas Studi Kasus: Refactoring Native PHP ke Modern PHP 8.x

Direktori ini memuat dua berkas komparasi yang digunakan untuk demonstrasi *live coding* dosen dan praktikum mahasiswa pada Pertemuan 01:

1. **`01-native-legacy.php`**:
   - Kode *legacy* bergaya prosedural usang.
   - Menggunakan `mysqli_connect` langsung, menggabungkan SQL query dengan variabel `$_GET['id']` tanpa sanitasi (rawan SQL Injection), dan mencampur logika database di dalam tampilan HTML.
2. **`02-modern-php8-refactored.php`**:
   - Versi modern berbasis standar PHP 8.x.
   - Menggunakan `declare(strict_types=1)`, Immutable DTO (`readonly class` dengan *Constructor Property Promotion*), Enums, Match Expression, dan PDO Prepared Statements.
   - **Self-contained**: Menggunakan SQLite in-memory sehingga dapat langsung dijalankan dan diuji di terminal tanpa konfigurasi server MySQL:
     ```bash
     php 02-modern-php8-refactored.php
     ```
