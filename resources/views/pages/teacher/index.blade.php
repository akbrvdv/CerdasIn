@extends('layouts.teacher')

@section('title', 'Dashboard Guru')

@section('content')
<div>
    {{-- BAGIAN JUDUL --}}
    {{-- Kita siapkan <span id="user-name"> untuk diisi oleh JavaScript --}}
    <h1 class="text-2xl font-bold text-purple-700 mb-4">
        Hai, <span id="user-name">Loading...</span>! 👋
    </h1>
    
    <p class="text-gray-600">Ini adalah halaman dashboard guru. Kamu bisa mengelola kelas dan materi di sini.</p>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-8">
        {{-- Card Kelola Kelas --}}
        <a href="{{ route('teacher.classes.index') }}"
            class="bg-white border rounded-xl p-6 flex flex-col items-center hover:border-purple-300 transition">
            {{-- Menggunakan FontAwesome (pastikan library sudah di-load di layout) --}}
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
    document.addEventListener('DOMContentLoaded', async function() {
        // 1. Cek Apakah Token Ada? (Proteksi Halaman)
        const token = localStorage.getItem('auth_token');
        if (!token) {
            alert('Anda belum login!');
            window.location.href = '/login';
            return;
        }

        // 2. Ambil Nama dari LocalStorage (Cara Cepat)
        // Data ini disimpan saat Anda login di file login.blade.php sebelumnya
        const userInfo = localStorage.getItem('user_info');
        
        if (userInfo) {
            try {
                const user = JSON.parse(userInfo);
                // Update teks di HTML
                document.getElementById('user-name').innerText = user.name;
            } catch (e) {
                console.error("Gagal parsing data user lokal");
            }
        }

        // 3. (Opsional tapi Disarankan) Validasi Token ke API Backend
        // Ini memastikan token belum expired dan data user paling baru
        try {
            // Sesuaikan URL ini dengan backend Anda
            // Endpoint /api/user ada di routes/api.php backend Anda
            const response = await axios.get('https://56c8e939278d.ngrok-free.app/api/user', {
                headers: {
                    'Authorization': `Bearer ${token}` // Wajib kirim token
                }
            });

            // Jika sukses, update nama dengan data terbaru dari server (jika ada perubahan)
            const latestUser = response.data; // atau response.data.user_info tergantung respon API backend
            
            // Backend AuthenticationController::userInfo mengembalikan 'user_info'
            const realName = latestUser.name || (latestUser.user_info ? latestUser.user_info.name : 'Guru');
            
            document.getElementById('user-name').innerText = realName;

        } catch (error) {
            console.error("Gagal memverifikasi user:", error);
            
            // Jika token ditolak (401 Unauthorized), berarti sesi habis
            if (error.response && error.response.status === 401) {
                alert('Sesi Anda telah berakhir. Silakan login kembali.');
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user_info');
                window.location.href = '/login';
            }
        }
    });
</script>
@endsection