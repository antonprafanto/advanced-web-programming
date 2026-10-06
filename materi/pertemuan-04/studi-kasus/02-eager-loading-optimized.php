<?php

declare(strict_types=1);

/**
 * BENCHMARK MANDIRI: N+1 QUERY VS EAGER LOADING (SQLITE IN-MEMORY)
 * 
 * Skrip ini mensimulasikan dan membuktikan secara nyata di terminal:
 * - Skenario A: Lazy Loading (N+1 Query)
 * - Skenario B: Eager Loading Teroptimasi (WHERE IN Query)
 */

$pdo = new PDO('sqlite::memory:');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat Skema Sederhana
$pdo->exec("
    CREATE TABLE authors (id INTEGER PRIMARY KEY, name TEXT);
    CREATE TABLE categories (id INTEGER PRIMARY KEY, name TEXT);
    CREATE TABLE posts (
        id INTEGER PRIMARY KEY,
        author_id INTEGER,
        category_id INTEGER,
        title TEXT
    );
");

// Seed 5 Author, 3 Kategori, dan 50 Posts
for ($i = 1; $i <= 5; $i++) {
    $pdo->exec("INSERT INTO authors (id, name) VALUES ($i, 'Penulis $i')");
}
for ($i = 1; $i <= 3; $i++) {
    $pdo->exec("INSERT INTO categories (id, name) VALUES ($i, 'Kategori $i')");
}
for ($i = 1; $i <= 50; $i++) {
    $authorId = rand(1, 5);
    $catId = rand(1, 3);
    $pdo->exec("INSERT INTO posts (id, author_id, category_id, title) VALUES ($i, $authorId, $catId, 'Judul Artikel $i')");
}

echo "========================================================\n";
echo " BENCHMARK NYATA: N+1 PROBLEM VS EAGER LOADING (50 DATA)\n";
echo "========================================================\n\n";

// --- SKENARIO 1: N+1 PROBLEM (LAZY LOADING) ---
$startLazy = microtime(true);
$queryCountLazy = 0;

// Query 1: Ambil 50 posts
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$queryCountLazy++;

// Looping di tampilan (N+1 pemicu)
foreach ($posts as $post) {
    // Kueri tambahan per baris untuk ambil Author
    $authorStmt = $pdo->prepare("SELECT name FROM authors WHERE id = ?");
    $authorStmt->execute([$post['author_id']]);
    $authorName = $authorStmt->fetchColumn();
    $queryCountLazy++;

    // Kueri tambahan per baris untuk ambil Category
    $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $catStmt->execute([$post['category_id']]);
    $catName = $catStmt->fetchColumn();
    $queryCountLazy++;
}
$timeLazy = microtime(true) - $startLazy;

echo "1. HASIL SKENARIO LAZY LOADING (N+1):\n";
echo "   - Jumlah Query SQL : {$queryCountLazy} query!\n";
echo "   - Waktu Eksekusi   : " . round($timeLazy * 1000, 3) . " ms\n\n";

// --- SKENARIO 2: EAGER LOADING (OPTIMIZED WHERE IN) ---
$startEager = microtime(true);
$queryCountEager = 0;

// Query 1: Ambil 50 posts
$stmt = $pdo->query("SELECT * FROM posts");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$queryCountEager++;

// Kumpulkan ID unik
$authorIds = array_unique(array_column($posts, 'author_id'));
$categoryIds = array_unique(array_column($posts, 'category_id'));

// Query 2: Ambil seluruh Author sekaligus via WHERE IN
$authorPlaceholders = implode(',', array_fill(0, count($authorIds), '?'));
$authorStmt = $pdo->prepare("SELECT id, name FROM authors WHERE id IN ($authorPlaceholders)");
$authorStmt->execute(array_values($authorIds));
$authors = $authorStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$queryCountEager++;

// Query 3: Ambil seluruh Category sekaligus via WHERE IN
$catPlaceholders = implode(',', array_fill(0, count($categoryIds), '?'));
$catStmt = $pdo->prepare("SELECT id, name FROM categories WHERE id IN ($catPlaceholders)");
$catStmt->execute(array_values($categoryIds));
$categories = $catStmt->fetchAll(PDO::FETCH_KEY_PAIR);
$queryCountEager++;

// Petakan data di memory (O(1) dictionary lookup)
foreach ($posts as $post) {
    $authorName = $authors[$post['author_id']] ?? 'Anonim';
    $catName = $categories[$post['category_id']] ?? 'Umum';
}
$timeEager = microtime(true) - $startEager;

echo "2. HASIL SKENARIO EAGER LOADING (OPTIMAL):\n";
echo "   - Jumlah Query SQL : {$queryCountEager} query saja!\n";
echo "   - Waktu Eksekusi   : " . round($timeEager * 1000, 3) . " ms\n\n";

$efficiency = round(($queryCountLazy - $queryCountEager) / $queryCountLazy * 100, 1);
echo "KESIMPULAN: Eager Loading mereduksi {$efficiency}% jumlah kueri ke basis data!\n";
echo "========================================================\n";
