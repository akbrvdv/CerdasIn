@extends('layouts.teacher')

@section('title', 'Daftar Materi')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Materi</h1>
        <a href="{{ route('teacher.materials.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> <span>Tambah Materi</span>
        </a>
    </div>

    {{-- KOTAK MONITOR ERROR --}}
    <div id="error-monitor" class="hidden p-4 mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded"></div>

    {{-- TABEL DATA --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden overflow-x-auto">
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

    // --- KONFIGURASI ---
    const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
    const BASE_URL = ENV_URL.replace(/\/$/, '');
    const API_URL = `${BASE_URL}/api/teacher/materials`;
    const STORAGE_URL = `${BASE_URL}/storage/`;

    async function fetchMaterials() {
        const tbody = document.getElementById('table-body');
        const errorMonitor = document.getElementById('error-monitor');
        const token = localStorage.getItem('auth_token');

        if (!token) {
            window.location.href = "{{ route('login') }}";
            return;
        }

        try {
            console.log(`Fetching materials from: ${API_URL}`);

            // 1. Ambil Data dari API
            const response = await axios.get(API_URL, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'ngrok-skip-browser-warning': 'true' // Penting untuk Ngrok Free
                }
            });

            // 2. Bersihkan & Parse Data
            let rawData = response.data;
            
            // Jika respons tercemar HTML Warning (kasus umum Ngrok/Laravel error)
            if (typeof rawData === 'string') {
                const jsonStart = rawData.indexOf('{');
                if (jsonStart !== -1) {
                    try {
                        rawData = JSON.parse(rawData.substring(jsonStart));
                    } catch (e) {
                        console.error("Gagal parsing JSON manual", e);
                    }
                }
            }

            console.log("Data Materi:", rawData); // Debugging

            // Tentukan lokasi array (bisa di root atau di properti .data)
            const materials = Array.isArray(rawData) ? rawData : (rawData.data || []);

            // 3. Render Tabel
            if (materials.length > 0) {
                let html = '';
                materials.forEach((item, index) => {
                    
                    // a. Logic Thumbnail
                    let thumbHtml = `<div class="w-16 h-16 bg-gray-100 rounded flex items-center justify-center text-gray-400 text-xs border">No Img</div>`;
                    if (item.thumbnail) {
                        const cleanPath = item.thumbnail.replace(/^\//, '');
                        thumbHtml = `<img src="${STORAGE_URL}${cleanPath}" alt="Img" class="w-16 h-16 object-cover rounded border border-gray-200" onerror="this.src='https://placehold.co/100?text=Error'">`;
                    }

                    // b. Logic File Download
                    let fileHtml = `<span class="text-gray-400 italic text-sm">Tidak ada file</span>`;
                    if (item.file_path) {
                        const cleanFilePath = item.file_path.replace(/^\//, '');
                        fileHtml = `
                            <a href="${STORAGE_URL}${cleanFilePath}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                <i class="fa-solid fa-file-arrow-down"></i> Unduh File
                            </a>
                        `;
                    }

                    // c. Nama Kelas
                    const className = item.classroom ? item.classroom.name : '<span class="text-red-500 italic">Tanpa Kelas</span>';

                    // d. URL Edit (Frontend Route)
                    // Asumsi route: /teacher/materials/{id}/edit
                    const editUrl = `/teacher/materials/${item.id}/edit`;

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
                                    <a href="${editUrl}" class="p-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition" title="Edit">
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
            console.error('Fetch Error:', error);
            errorMonitor.classList.remove('hidden');
            
            let msg = "Gagal memuat data.";
            if (error.response) {
                if (error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                    return;
                }
                msg = error.response.data.message || msg;
            }
            
            errorMonitor.innerText = msg;
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-500 font-bold">Gagal mengambil data. Cek Console.</td></tr>`;
        }
    }

    // Fungsi Hapus
    window.deleteMaterial = async function(id) {
        if(!confirm('Yakin ingin menghapus materi ini?')) return;
        
        const token = localStorage.getItem('auth_token');
        try {
            await axios.delete(`${API_URL}/${id}`, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'ngrok-skip-browser-warning': 'true'
                }
            });
            alert('Materi berhasil dihapus');
            fetchMaterials(); // Refresh otomatis
        } catch (e) {
            console.error(e);
            alert('Gagal menghapus materi.');
        }
    }
</script>
@endsection