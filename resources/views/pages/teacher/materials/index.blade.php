@extends('layouts.teacher')

@section('title', 'Daftar Materi')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Materi</h1>
        <a href="/teacher/materials/create" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> <span>Tambah Materi</span>
        </a>
    </div>

    {{-- KOTAK MONITOR ERROR --}}
    <div id="error-monitor" class="hidden p-4 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded"></div>

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
        <table class="min-w-full text-left">
            <thead class="bg-purple-50 text-purple-900 border-b">
                <tr>
                    <th class="px-6 py-4 font-semibold">No</th>
                    <th class="px-6 py-4 font-semibold">Thumbnail</th>
                    <th class="px-6 py-4 font-semibold">Judul & Kelas</th>
                    <th class="px-6 py-4 font-semibold">File</th>
                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Sedang memuat materi...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchMaterials();
    });

    // --- KONFIGURASI URL API BARU ANDA ---
    const BASE_URL = 'https://71870eaf2b39.ngrok-free.app'; // Domain Ngrok Baru
    const API_URL = `${BASE_URL}/api/teacher/materials`;    // Endpoint API
    const STORAGE_URL = `${BASE_URL}/storage/`;             // Endpoint untuk akses gambar/file

    async function fetchMaterials() {
        const tbody = document.getElementById('table-body');
        const errorMonitor = document.getElementById('error-monitor');
        const token = localStorage.getItem('auth_token');

        if (!token) {
            window.location.href = '/login';
            return;
        }

        try {
            // 1. Ambil Data dari API
            const response = await axios.get(API_URL, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            // 2. Akses Array Data (Response -> Data -> Data)
            // Backend Anda membungkus data di dalam properti 'data'
            const materials = response.data.data;

            // 3. Render Tabel
            if (materials && materials.length > 0) {
                let html = '';
                materials.forEach((item, index) => {
                    
                    // a. Logic Thumbnail
                    // Jika ada thumbnail, gabungkan dengan STORAGE_URL. Jika tidak, pakai placeholder.
                    let thumbHtml = `<div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs border">No Img</div>`;
                    if (item.thumbnail) {
                        thumbHtml = `<img src="${STORAGE_URL}${item.thumbnail}" alt="Img" class="w-16 h-16 object-cover rounded border border-gray-200">`;
                    }

                    // b. Logic File Download
                    // Jika ada file_path, buat link download ke STORAGE_URL
                    let fileHtml = `<span class="text-gray-400 italic text-sm">Tidak ada file</span>`;
                    if (item.file_path) {
                        fileHtml = `
                            <a href="${STORAGE_URL}${item.file_path}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                <i class="fa-solid fa-file-arrow-down"></i> Unduh File
                            </a>
                        `;
                    }

                    // c. Nama Kelas (Relasi)
                    // Cek apakah classroom null (misal kelas terhapus)
                    const className = item.classroom ? item.classroom.name : '<span class="text-red-500 italic">Tanpa Kelas</span>';

                    html += `
                        <tr class="hover:bg-gray-50 border-b last:border-0 transition">
                            <td class="px-6 py-4 text-gray-600">${index + 1}</td>
                            <td class="px-6 py-4">${thumbHtml}</td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-bold text-base mb-1">${item.title}</div>
                                <div class="inline-block bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded">
                                    ${className}
                                </div>
                            </td>
                            <td class="px-6 py-4">${fileHtml}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="/teacher/materials/${item.id}/edit" class="p-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button onclick="deleteMaterial(${item.id})" class="p-2 bg-red-50 text-red-600 rounded hover:bg-red-100 transition" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada materi. Klik tombol <b>+ Tambah Materi</b>.</td></tr>`;
            }

        } catch (error) {
            console.error(error);
            errorMonitor.classList.remove('hidden');
            let msg = "Gagal memuat data.";
            
            if (error.response) {
                msg += ` Server merespon: ${error.response.status} (${error.response.statusText})`;
                if (error.response.status === 401) msg = "Sesi habis. Silakan Login ulang.";
            } else if (error.request) {
                msg += " Tidak dapat terhubung ke Backend (Cek Ngrok).";
            } else {
                msg += ` ${error.message}`;
            }
            
            errorMonitor.innerText = msg;
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-500 font-bold">Gagal mengambil data.</td></tr>`;
        }
    }

    // Fungsi Hapus
    async function deleteMaterial(id) {
        if(!confirm('Yakin ingin menghapus materi ini?')) return;
        
        const token = localStorage.getItem('auth_token');
        try {
            await axios.delete(`${API_URL}/${id}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            fetchMaterials(); // Refresh otomatis
        } catch (e) {
            alert('Gagal menghapus materi.');
        }
    }
</script>
@endsection