<?php

/**
 * CONTOH KASUS BENCANA PERFORMA: N+1 QUERY PROBLEM
 * 
 * Skenario Masalah:
 * Developer mengambil daftar 50 postingan blog, lalu di tampilan Blade me-looping:
 * $post->author->name dan $post->category->name.
 * 
 * Mengapa terjadi 101 Query SQL?
 * - Query 1: SELECT * FROM posts LIMIT 50;
 * - Query 2..51: SELECT * FROM users WHERE id = ?; (50 kali!)
 * - Query 52..101: SELECT * FROM categories WHERE id = ?; (50 kali!)
 * 
 * Dampak di Server:
 * 1. Database Connection Pool cepat habis saat diakses banyak pengguna.
 * 2. Response time melonjak dari 15ms menjadi 800ms - 2000ms.
 * 3. RAM dan CPU database server terbebani I/O yang tidak perlu.
 */

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostControllerDisaster extends Controller
{
    public function index()
    {
        // ANTI-PATTERN: Mengambil data tanpa eager loading relasi
        $posts = Post::latest()->take(50)->get();

        // Di View Blade:
        // @foreach ($posts as $post)
        //     {{ $post->title }}
        //     {{ $post->author->name }}   <-- Pemicu N query
        //     {{ $post->category->name }} <-- Pemicu N query lagi!
        //     {{ $post->comments->count() }} <-- Pemicu memuat seluruh row komentar ke RAM!
        // @endforeach

        return view('posts.index', compact('posts'));
    }
}
