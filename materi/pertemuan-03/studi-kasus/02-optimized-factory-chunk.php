<?php

declare(strict_types=1);

/**
 * CONTOH SEEDER CEPAT (OPTIMIZED BATCH INSERTION & ACID TRANSACTION)
 * 
 * Strategi Optimasi:
 * 1. Menyiapkan dataset dalam memory terlebih dahulu.
 * 2. Menggunakan batch insert (1 query INSERT untuk ratusan baris sekaligus).
 * 3. Memanfaatkan chunking (misal 500 baris per batch) untuk menghindari batas memory / query limit MySQL.
 * 4. Membungkus proses ke dalam 1 transaksi ACID tunggal (hanya 1 kali commit disk I/O).
 * 
 * Estimasi waktu eksekusi untuk 5.000 record: < 1 detik!
 */

// SIMULASI DEMO MANDIRI VIA SQLITE IN-MEMORY (Dapat dijalankan langsung via CLI)
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
    CREATE TABLE students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nim TEXT NOT NULL UNIQUE,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        ipk REAL NOT NULL,
        created_at TEXT NOT NULL
    );
");

echo "========================================================\n";
echo " UJI PERFORMA: HIGH-SPEED BATCH SEEDING (5.000 DATA)\n";
echo "========================================================\n\n";

$startTime = microtime(true);
$totalRecords = 5000;
$chunkSize = 500;

$dataset = [];
for ($i = 1; $i <= $totalRecords; $i++) {
    $dataset[] = [
        'nim'        => 'A11.2024.' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
        'name'       => 'Mahasiswa ' . $i,
        'email'      => 'mhs' . $i . '@kampus.ac.id',
        'ipk'        => round(mt_rand(250, 400) / 100, 2),
        'created_at' => date('Y-m-d H:i:s'),
    ];
}

// Eksekusi Batch Chunk di dalam Satu Transaksi Database
$pdo->beginTransaction();

try {
    $chunks = array_chunk($dataset, $chunkSize);
    
    foreach ($chunks as $chunk) {
        // Buat parameterized batch query: INSERT INTO students (...) VALUES (?, ?...), (?, ?...)
        $placeholders = [];
        $values = [];
        
        foreach ($chunk as $row) {
            $placeholders[] = '(?, ?, ?, ?, ?)';
            array_push($values, $row['nim'], $row['name'], $row['email'], $row['ipk'], $row['created_at']);
        }
        
        $sql = "INSERT INTO students (nim, name, email, ipk, created_at) VALUES " . implode(', ', $placeholders);
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
    }

    $pdo->commit();
    $duration = microtime(true) - $startTime;

    $count = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    echo "Status: Berhasil memasukkan {$count} data!\n";
    echo "Waktu Eksekusi: " . round($duration, 3) . " detik.\n";
    echo "Kecepatan Rata-rata: " . round($totalRecords / $duration, 0) . " records/detik.\n\n";

} catch (Throwable $e) {
    $pdo->rollBack();
    echo "Gagal: " . $e->getMessage() . "\n";
}

echo "========================================================\n";
