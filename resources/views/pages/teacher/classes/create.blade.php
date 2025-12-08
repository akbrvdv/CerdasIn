@extends('layouts.teacher')

@section('title', 'Tambah Kelas')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-purple-700">Tambah Kelas Baru</h1>
            <a href="/teacher/classes" class="text-gray-600 hover:text-gray-900">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white border rounded-xl p-6 max-w-lg">
            
            {{-- Pesan Error --}}
            <div id="error-message" class="hidden mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <span class="block sm:inline" id="error-text"></span>
            </div>

            <form id="create-class-form">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-semibold mb-2">Nama Kelas</label>
                    <input type="text" id="name" name="name" 
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200"
                        placeholder="Contoh: XII IPA 1" required>
                    <p id="error-name" class="text-red-500 text-sm mt-1 hidden"></p>
                </div>

                <div class="flex justify-end gap-2">
                    <a href="/teacher/classes" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </a>
                    <button type="submit" id="btn-submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.getElementById('create-class-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            // 1. Ambil Data
            const name = document.getElementById('name').value;
            const btnSubmit = document.getElementById('btn-submit');
            const errorMsg = document.getElementById('error-message');
            const errorName = document.getElementById('error-name');

            // 2. Reset State (Hapus pesan error lama)
            errorMsg.classList.add('hidden');
            errorName.classList.add('hidden');
            btnSubmit.innerText = 'Menyimpan...';
            btnSubmit.disabled = true;

            const token = localStorage.getItem('auth_token');
            if (!token) {
                alert('Sesi habis, silakan login ulang.');
                window.location.href = '/login';
                return;
            }

            try {
                // 3. Kirim Data ke API Backend
                // Endpoint sesuai dengan route resource di api.php
                const response = await axios.post('https://56c8e939278d.ngrok-free.app/api/teacher/classrooms', {
                    name: name
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                // 4. Jika Sukses
                alert('Kelas berhasil ditambahkan!');
                window.location.href = '/teacher/classes'; // Kembali ke tabel untuk melihat hasilnya

            } catch (error) {
                // 5. Jika Gagal
                console.error('Error:', error);
                btnSubmit.innerText = 'Simpan';
                btnSubmit.disabled = false;

                if (error.response) {
                    // Validasi Laravel (misal: nama kelas sudah ada)
                    if (error.response.status === 422) {
                        const errors = error.response.data.errors;
                        if (errors.name) {
                            errorName.innerText = errors.name[0];
                            errorName.classList.remove('hidden');
                        }
                    } else {
                        // Error Server Lainnya
                        document.getElementById('error-text').innerText = error.response.data.message || 'Terjadi kesalahan sistem.';
                        errorMsg.classList.remove('hidden');
                    }
                }
            }
        });
    </script>
@endsection