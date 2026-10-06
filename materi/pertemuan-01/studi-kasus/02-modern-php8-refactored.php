<?php

declare(strict_types=1);

/**
 * CONTOH KODE REFACTORING MODERN (PHP 8.x)
 * 
 * Perbaikan yang diterapkan:
 * 1. Strict Typing (declare(strict_types=1)) untuk integritas tipe data.
 * 2. Immutable Data Transfer Object (DTO) dengan Constructor Property Promotion & Readonly.
 * 3. Pemisahan Logika Akses Data (Repository) dan Presentasi.
 * 4. Prepared Statements via PDO untuk mencegah SQL Injection 100%.
 * 5. Match Expression untuk pemetaan status yang ringkas dan aman.
 * 6. Self-contained: Menggunakan SQLite in-memory agar dapat langsung diuji di CLI.
 */

// --- 1. ENUM & DTO (Data Transfer Object) ---

enum StudentStatus: string {
    case Active   = 'A';
    case OnLeave  = 'C';
    case Inactive = 'N';

    public function label(): string {
        return match($this) {
            self::Active   => 'Aktif',
            self::OnLeave  => 'Cuti Akademik',
            self::Inactive => 'Non-Aktif / Lulus',
        };
    }

    public function badgeColor(): string {
        return match($this) {
            self::Active   => 'green',
            self::OnLeave  => 'orange',
            self::Inactive => 'red',
        };
    }
}

readonly class StudentDto {
    public function __construct(
        public int $id,
        public string $nim,
        public string $name,
        public StudentStatus $status,
        public ?string $email = null
    ) {}
}

// --- 2. CUSTOM EXCEPTION ---

class StudentNotFoundException extends Exception {}

// --- 3. REPOSITORY (Pemisahan Tanggung Jawab Basis Data) ---

class StudentRepository {
    public function __construct(private PDO $db) {}

    public function findById(int $id): StudentDto {
        // Prepared Statement: Menjamin parameter terisolasi dari SQL parser
        $stmt = $this->db->prepare("SELECT id, nim, nama, status, email FROM students WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new StudentNotFoundException("Mahasiswa dengan ID {$id} tidak ditemukan.");
        }

        return new StudentDto(
            id: (int) $data['id'],
            nim: $data['nim'],
            name: $data['nama'],
            status: StudentStatus::tryFrom($data['status']) ?? StudentStatus::Inactive,
            email: $data['email'] ?? null
        );
    }
}

// --- 4. SIMULASI EKSEKUSI DI TERMINAL / BROWSER ---

// Inisialisasi DB Dummy SQLite in-memory untuk demonstrasi mandiri
$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat skema & seed 1 data
$pdo->exec("
    CREATE TABLE students (
        id INTEGER PRIMARY KEY,
        nim TEXT NOT NULL,
        nama TEXT NOT NULL,
        status TEXT NOT NULL,
        email TEXT
    );
    INSERT INTO students (id, nim, nama, status, email) 
    VALUES (1, 'A11.2023.14999', 'Budi Santoso', 'A', 'budi@mhs.ac.id');
");

$repository = new StudentRepository($pdo);

echo "========================================================\n";
echo " DEMONSTRASI REFACTORING MODERN PHP 8.x\n";
echo "========================================================\n\n";

try {
    $searchId = 1;
    $student = $repository->findById($searchId);

    echo "Status Pencarian: Berhasil Ditemukan!\n";
    echo "ID      : {$student->id}\n";
    echo "NIM     : {$student->nim}\n";
    echo "Nama    : {$student->name}\n";
    echo "Status  : {$student->status->label()} (Warna Badge: {$student->status->badgeColor()})\n";
    echo "Email   : " . ($student->email ?? 'Belum Diisi') . "\n\n";

    echo "Mencoba mencari ID yang tidak ada (ID: 99)...\n";
    $repository->findById(99);

} catch (StudentNotFoundException $e) {
    echo "Handled Error: " . $e->getMessage() . "\n";
}

echo "\n========================================================\n";
echo " Selesai: Kode bersih, aman dari SQL Injection, dan bertipe kuat!\n";
echo "========================================================\n";
