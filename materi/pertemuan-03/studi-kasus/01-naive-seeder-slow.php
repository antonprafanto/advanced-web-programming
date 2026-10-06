<?php

/**
 * CONTOH SEEDER LAMBAT (NAIVE / ANTI-PATTERN)
 * 
 * Mengapa cara ini sangat lambat?
 * 1. Menjalankan 5.000 query INSERT secara individual (5.000 I/O disk & socket network round-trips).
 * 2. Memicu event Eloquent (creating, created, saving, saved) 5.000 kali.
 * 3. Membangun 5.000 objek Model utuh ke dalam RAM.
 * 4. Tanpa Database Transaction (setiap baris di-commit secara terpisah oleh RDBMS).
 * 
 * Estimasi waktu eksekusi untuk 5.000 record: 45 detik s/d 2 menit!
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class NaiveSlowStudentSeeder extends Seeder
{
    public function run(): void
    {
        $startTime = microtime(true);

        for ($i = 1; $i <= 5000; $i++) {
            // Anti-pattern: Model::create() berulang kali di dalam loop
            Student::create([
                'nim'        => 'A11.' . rand(2020, 2024) . '.' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'name'       => fake()->name(),
                'email'      => fake()->unique()->safeEmail(),
                'status'     => 'aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $duration = microtime(true) - $startTime;
        echo "Waktu eksekusi seeder lambat: " . round($duration, 2) . " detik.\n";
    }
}
