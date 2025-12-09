@extends('layouts.teacher')

@section('title', 'Kelas')

@section('content')
    <div>
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-purple-700">Daftar Kelas</h1>
            <a href="{{ route('teacher.classes.create') }}"
                class="text-center bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition">
                <i class="fa-solid fa-plus"></i>
            </a>
        </div>

        <div class="bg-white border rounded-xl p-4">
            {{-- Tabel Kosong dengan ID untuk dimanipulasi JS --}}
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr>
                        <th class="p-3">No</th>
                        <th class="p-3">Nama Kelas</th>
                        <th class="p-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="classes-table-body">
                    {{-- Data akan dimasukkan di sini oleh JavaScript --}}
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
            
            {{-- Pesan jika kosong --}}
            <p id="empty-message" class="hidden text-gray-500 text-center py-6">Belum ada kelas yang ditambahkan.</p>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiUrl = 'https://71870eaf2b39.ngrok-free.app/api/teacher/classrooms';
            const tableBody = document.getElementById('classes-table-body');
            const emptyMessage = document.getElementById('empty-message');

            // Fungsi untuk mengambil data
            fetch(apiUrl)
                .then(response => {
                    if (!response.ok) throw new Error('Gagal mengambil data');
                    return response.json();
                })
                .then(data => {
                    // Bersihkan loading
                    tableBody.innerHTML = '';

                    // Cek jika data kosong
                    // Sesuaikan 'data' jika API membungkusnya dalam { data: [...] }
                    const classes = Array.isArray(data) ? data : data.data; 

                    if (classes.length === 0) {
                        tableBody.parentElement.classList.add('hidden'); // Sembunyikan tabel
                        emptyMessage.classList.remove('hidden'); // Tampilkan pesan kosong
                        return;
                    }

                    // Loop data dan buat baris HTML
                    classes.forEach((kelas, index) => {
                        // Pastikan URL route edit/delete sesuai aplikasi Anda
                        // Kita inject ID dari API ke dalam URL Laravel
                        // const editUrl = {{ url('/teacher/classes') }}/${kelas.id}/edit;
                        // const deleteUrl = {{ url('/teacher/classes') }}/${kelas.id};
                        // const csrfToken = {{ csrf_token() }};

                        const row = `
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-medium">${index + 1}</td>
                                <td class="p-3 font-medium">${kelas.name}</td>
                                <td class="p-3 text-center">
                                    <a href="${editUrl}" 
                                       class="inline-block bg-transparent border rounded-md p-2 hover:border-blue-400 hover:text-blue-400 transition mr-1">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form action="${deleteUrl}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus kelas ini?');">
                                        
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit"
                                            class="bg-transparent border rounded-md p-2 hover:border-red-400 hover:text-red-400 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        `;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                });
        });
    </script>
@endsection