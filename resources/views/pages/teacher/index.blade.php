@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
<div>
    {{-- BAGIAN JUDUL --}}
    <h1 class="text-2xl font-bold text-purple-700 mb-4">
        {{-- Default text "Loading..." akan diganti instan oleh JS --}}
        Hai, <span id="user-name">Loading...</span>! 👋
    </h1>
    
    <p class="text-gray-600">Ini adalah halaman dashboard guru. Kamu bisa mengelola kelas dan materi di sini.</p>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        {{-- Card Kelola Kelas --}}
        <a href="{{ route('teacher.classes.index') }}"
            class="bg-white border rounded-xl p-6 flex flex-col items-center hover:border-purple-300 transition">
            <i class="fa-solid fa-users text-purple-600 text-3xl mb-2"></i>
            <h3 class="font-semibold">Kelola Kelas</h3>
        </a>

        {{-- Card Kelola Materi --}}
        <a href="{{ route('teacher.materials.index') }}"
            class="bg-white border rounded-xl p-6 flex flex-col items-center hover:border-purple-300 transition">
            <i class="fa-solid fa-book-open text-purple-600 text-3xl mb-2"></i>
            <h3 class="font-semibold">Kelola Materi</h3>
        </a>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Anda belum login!');
            window.location.href = '/login';
            return;
        }

        const userDataString = localStorage.getItem('user_data');
        
        if (userDataString) {
            try {
                const user = JSON.parse(userDataString);

                if (user.name) {
                    document.getElementById('user-name').innerText = user.name;
                } else {
                    document.getElementById('user-name').innerText = "Guru";
                }
                
            } catch (e) {
                console.error("Gagal memparsing data user dari LocalStorage", e);
                document.getElementById('user-name').innerText = "Guru";
            }
        } else {
            console.warn("Data user tidak ditemukan di LocalStorage.");
            document.getElementById('user-name').innerText = "Guru";
        }
    });
</script>
@endsection