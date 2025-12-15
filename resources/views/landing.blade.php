<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CerdasIn — Belajar Lebih Pintar</title>
    {{-- Pastikan Vite meload CSS/JS Anda --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Font Awesome (Opsional, untuk ikon jika dibutuhkan nanti) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50 text-gray-800 scroll-smooth">

    <header class="flex justify-between items-center px-6 py-4 bg-white shadow-sm fixed top-0 left-0 right-0 z-10">
        <h1 class="text-2xl font-bold text-purple-600 flex items-center gap-2">
            <i class="fa-solid fa-graduation-cap"></i> CerdasIn
        </h1>
        <nav class="hidden md:flex space-x-6 text-sm font-medium">
            <a href="#fitur" class="hover:text-purple-600 transition">Fitur</a>
            <a href="#tentang" class="hover:text-purple-600 transition">Tentang</a>
            <a href="#kontak" class="hover:text-purple-600 transition">Kontak</a>
        </nav>

        {{-- ID ditambahkan untuk dimanipulasi JS --}}
        <a id="btn-login-nav" href="{{ route('login') }}"
            class="hidden md:block bg-purple-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-purple-700 transition">
            Masuk
        </a>
    </header>

    <section class="min-h-screen flex flex-col justify-center items-center text-center px-6 bg-gradient-to-b from-white to-purple-50 pt-20">
        <h2 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
            Belajar Jadi Lebih <span class="text-purple-600">Cerdas</span> Bersama CerdasIn
        </h2>
        <p class="text-gray-600 max-w-xl mb-8">
            Aplikasi pembelajaran interaktif untuk membantu siswa belajar dengan cara yang menyenangkan,
            efektif, dan terarah sesuai prinsip <strong>SDGs 4: Pendidikan Berkualitas</strong>.
        </p>
        <div class="flex space-x-4">
            {{-- ID ditambahkan untuk dimanipulasi JS --}}
            <a id="btn-register-hero" href="{{ route('register') }}"
                class="bg-purple-600 text-white px-6 py-3 rounded-full font-semibold hover:bg-purple-700 transition shadow-lg hover:-translate-y-1">
                Daftar Sekarang
            </a>
        </div>
    </section>

    <section id="fitur" class="py-16 px-6 bg-gradient-to-b from-purple-50 to-white">
        <div class="text-center mb-10">
            <h3 class="text-3xl font-bold text-purple-700">Fitur Unggulan</h3>
            <p class="text-gray-500 mt-2">Apa saja yang bisa kamu lakukan?</p>
        </div>
        
        <div class="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
            <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition border border-purple-100 text-center">
                <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-puzzle-piece"></i>
                </div>
                <h4 class="font-semibold text-lg mb-2 text-purple-600">Kuis Interaktif</h4>
                <p class="text-gray-600 text-sm">Belajar jadi seru dengan soal interaktif dan penilaian otomatis langsung keluar.</p>
            </div>
            <div class="bg-white p-8 rounded-xl shadow-sm hover:shadow-md transition border border-purple-100 text-center">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                    <i class="fa-solid fa-book-open"></i>
                </div>
                <h4 class="font-semibold text-lg mb-2 text-purple-600">Materi Edukatif</h4>
                <p class="text-gray-600 text-sm">Akses materi pelajaran lengkap dan latihan soal sesuai tingkat belajar siswa.</p>
            </div>
        </div>
    </section>

    <section id="tentang" class="py-20 bg-purple-50 px-6 text-center">
        <h3 class="text-3xl font-bold mb-6 text-purple-700">Tentang CerdasIn</h3>
        <p class="max-w-3xl mx-auto text-gray-600 leading-relaxed text-lg">
            CerdasIn adalah aplikasi pembelajaran berbasis web yang dirancang untuk membantu siswa belajar secara
            efektif dan menyenangkan. Kami percaya bahwa setiap anak berhak mendapatkan akses pendidikan berkualitas,
            sejalan dengan tujuan <strong>SDGs 4 – Quality Education</strong>.
        </p>
    </section>

    <section id="kontak" class="py-20 bg-purple-600 text-white text-center px-6">
        <h3 class="text-3xl font-bold mb-4">Hubungi Kami</h3>
        <p class="mb-8 max-w-md mx-auto opacity-90">Punya saran atau ingin berkolaborasi? Kami senang mendengarnya!</p>
        <a href="mailto:ardhanahidayat61@gmail.com"
            class="bg-white text-purple-700 px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition shadow-lg">
            <i class="fa-solid fa-envelope mr-2"></i> Kirim Email
        </a>
    </section>

    <footer class="py-6 bg-white text-center text-sm text-gray-500 border-t">
        <p>© {{ date('Y') }} <span class="font-semibold text-purple-600">CerdasIn</span> - Kelompok 2 - Pemrograman Web II.</p>
    </footer>

    {{-- SCRIPT LOGIC AUTH --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Cek LocalStorage
            const token = localStorage.getItem('auth_token');
            const userData = localStorage.getItem('user_data');

            // Jika user sudah login
            if (token && userData) {
                try {
                    const user = JSON.parse(userData);
                    let dashboardUrl = "{{ route('login') }}"; // Default fallback

                    // Tentukan URL berdasarkan Role
                    if (user.role === 'teacher') {
                        dashboardUrl = "{{ route('teacher.dashboard') }}";
                    } else if (user.role === 'student') {
                        dashboardUrl = "{{ route('student.dashboard') }}";
                    }

                    // 1. Ubah Tombol di Navbar (Masuk -> Dashboard)
                    const navBtn = document.getElementById('btn-login-nav');
                    if (navBtn) {
                        navBtn.textContent = `Dashboard ${user.name.split(' ')[0]}`; // Pakai nama depan
                        navBtn.href = dashboardUrl;
                    }

                    // 2. Ubah Tombol di Hero (Daftar -> Ke Dashboard)
                    const heroBtn = document.getElementById('btn-register-hero');
                    if (heroBtn) {
                        heroBtn.textContent = "Lanjut Belajar 🚀";
                        heroBtn.href = dashboardUrl;
                    }

                } catch (e) {
                    console.error("Error parsing user data", e);
                }
            }
        });
    </script>
</body>

</html>