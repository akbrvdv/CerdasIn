@extends('layouts.teacher')

@section('title', 'Kelas')

@section('content')
    <div>
        {{-- Header --}}
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold text-purple-700">Daftar Kelas</h1>
            <a href="{{ route('teacher.classes.create') }}"
                class="text-center bg-purple-600 text-white py-2 px-4 rounded-md hover:bg-purple-700 transition">
                <i class="fa-solid fa-plus"></i>
                <span class="ml-2 hidden sm:inline">Tambah Kelas</span>
            </a>
        </div>

        {{-- Tabel Container --}}
        <div class="bg-white border rounded-xl p-4 overflow-x-auto">
            
            {{-- Loading State --}}
            <div id="loading-indicator" class="text-center py-8">
                <i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-2xl"></i>
                <p class="text-gray-500 mt-2">Memuat daftar kelas...</p>
            </div>

            {{-- Error State --}}
            <div id="error-message" class="hidden text-center py-8 text-red-500">
                <i class="fa-solid fa-circle-exclamation text-2xl mb-2"></i>
                <p>Gagal memuat data. Silakan coba lagi.</p>
            </div>

            {{-- Empty State --}}
            <div id="empty-message" class="hidden text-center py-10">
                <div class="bg-gray-100 rounded-full h-16 w-16 flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-chalkboard text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-gray-900 font-medium">Belum ada kelas</h3>
                <p class="text-gray-500 text-sm mt-1">Mulai dengan menambahkan kelas baru.</p>
            </div>

            {{-- Tabel Data --}}
            <table id="classes-table" class="hidden w-full text-left border-collapse min-w-[500px]">
                <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
                    <tr>
                        <th class="p-4 border-b">No</th>
                        <th class="p-4 border-b w-full">Nama Kelas</th>
                        <th class="p-4 border-b text-center min-w-[150px]">Aksi</th>
                    </tr>
                </thead>
                <tbody id="classes-table-body" class="divide-y divide-gray-100">
                    {{-- Data diinject via JS --}}
                </tbody>
            </table>
        </div>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // 1. Config
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            // Cek Token
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // 2. Elements
            const loadingEl = document.getElementById('loading-indicator');
            const errorEl = document.getElementById('error-message');
            const emptyEl = document.getElementById('empty-message');
            const tableEl = document.getElementById('classes-table');
            const tbodyEl = document.getElementById('classes-table-body');

            try {
                // 3. Request Data
                const response = await axios.get(`${API_URL}/teacher/classrooms`, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true'
                    }
                });

                // Bersihkan Data
                let rawData = response.data;
                // Handle PHP Warning jika ada
                if (typeof rawData === 'string') {
                    const jsonStart = rawData.indexOf('{');
                    if (jsonStart !== -1) {
                        rawData = JSON.parse(rawData.substring(jsonStart));
                    }
                }

                // Ambil Array Kelas
                // Struktur API Resource biasanya: { data: [...] }
                const classes = Array.isArray(rawData) ? rawData : (rawData.data || []);

                loadingEl.classList.add('hidden');

                if (classes.length === 0) {
                    emptyEl.classList.remove('hidden');
                } else {
                    tableEl.classList.remove('hidden');
                    renderTable(classes);
                }

            } catch (error) {
                console.error('Error fetching classes:', error);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');

                if (error.response && error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                }
            }

            // 4. Render Table
            function renderTable(data) {
                tbodyEl.innerHTML = '';
                
                data.forEach((kelas, index) => {
                    // Buat URL Edit & Delete secara dinamis
                    // Asumsi route web: /teacher/classes/{id}/edit
                    const editUrl = `{{ url('/teacher/classes') }}/${kelas.id}/edit`;
                    
                    const row = `
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 font-medium text-gray-500">${index + 1}</td>
                            <td class="p-4 font-semibold text-gray-800">${kelas.name}</td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Edit --}}
                                    <a href="${editUrl}" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    {{-- Tombol Hapus (Pemicu Modal/JS) --}}
                                    <button onclick="deleteClass(${kelas.id}, '${kelas.name}')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbodyEl.innerHTML += row;
                });
            }

            // 5. Fungsi Hapus (Global Scope agar bisa dipanggil onclick)
            window.deleteClass = async function(id, name) {
                if (!confirm(`Apakah Anda yakin ingin menghapus kelas "${name}"?`)) return;

                try {
                    await axios.delete(`${API_URL}/teacher/classrooms/${id}`, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    });
                    
                    alert('Kelas berhasil dihapus');
                    location.reload(); // Reload halaman untuk refresh tabel

                } catch (error) {
                    console.error('Delete error:', error);
                    alert('Gagal menghapus kelas.');
                }
            };
        });
    </script>
@endsection