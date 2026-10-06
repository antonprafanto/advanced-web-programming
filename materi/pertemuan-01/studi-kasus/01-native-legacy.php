<?php
/**
 * CONTOH KODE LEGACY / NATIVE SPAGHETTI (TIDAK UNTUK DITIRU)
 * 
 * Karakteristik kode bermasalah:
 * 1. Logika database, alur kontrol, dan presentasi HTML tercampur aduk.
 * 2. Celah Keamanan Fatal: SQL Injection pada $_GET['id'] tanpa sanitasi/prepared statements.
 * 3. Menggunakan fungsi prosedural mysqli_* yang rentan error tak tertangani.
 * 4. Tidak ada pemisahan tanggung jawab (Separation of Concerns).
 */

// 1. Koneksi langsung di file yang sama
$conn = mysqli_connect("localhost", "root", "", "db_akademik");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// 2. Ambil parameter URL langsung tanpa sanitasi (BAHAYA: Rentan SQL Injection!)
$id = isset($_GET['id']) ? $_GET['id'] : 1;

// 3. String concatenation query langsung
$query = "SELECT * FROM mahasiswa WHERE id = " . $id;
$result = mysqli_query($conn, $query);

// 4. Pengambilan data
$row = mysqli_fetch_assoc($result);

// 5. Presentasi HTML bercampur logika dan switch-case manual
?>
<!DOCTYPE html>
<html>
<head>
    <title>Profil Mahasiswa (Versi Native)</title>
</head>
<body>
    <h1>Detail Mahasiswa</h1>
    <?php if ($row): ?>
        <p>NIM: <?php echo $row['nim']; ?></p>
        <p>Nama: <?php echo $row['nama']; ?></p>
        <p>Status: 
            <?php 
                // Switch case konvensional bertele-tele
                switch($row['status']) {
                    case 'A':
                        echo "<span style='color:green;'>Aktif</span>";
                        break;
                    case 'C':
                        echo "<span style='color:orange;'>Cuti</span>";
                        break;
                    default:
                        echo "<span style='color:red;'>Non-Aktif</span>";
                        break;
                }
            ?>
        </p>
    <?php else: ?>
        <p>Data tidak ditemukan!</p>
    <?php endif; ?>
</body>
</html>
