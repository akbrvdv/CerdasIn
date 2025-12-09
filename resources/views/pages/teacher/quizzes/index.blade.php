@extends('layouts.teacher')

@section('title', 'Kelola Kuis')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Daftar Kuis</h1>
        <a href="{{ route('teacher.quizzes.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> <span>Buat Kuis Baru</span>
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
                    <th class="px-6 py-4 font-semibold">Judul Kuis</th>
                    <th class="px-6 py-4 font-semibold">Kelas</th>
                    <th class="px-6 py-4 font-semibold">Jumlah Soal</th>
                    <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="table-body">
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Sedang memuat kuis...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- SCRIPT JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        fetchQuizzes();
    });

    // --- KONFIGURASI ---
    const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
    const BASE_URL = ENV_URL.replace(/\/$/, '');
    const API_URL = `${BASE_URL}/api/teacher/quizzes`;

    async function fetchQuizzes() {
        const tbody = document.getElementById('table-body');
        const errorMonitor = document.getElementById('error-monitor');
        const token = localStorage.getItem('auth_token');

        if (!token) {
            window.location.href = "{{ route('login') }}";
            return;
        }

        try {
            // 1. Ambil Data API
            const response = await axios.get(API_URL, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'ngrok-skip-browser-warning': 'true'
                }
            });

            // 2. Parse Data (Anti PHP Warning from Ngrok/Laravel)
            let rawData = response.data;
            if (typeof rawData === 'string' && rawData.includes('{')) {
                try {
                    rawData = JSON.parse(rawData.substring(rawData.indexOf('{')));
                } catch (e) {}
            }

            // Handle data structure { data: [...] } or direct array [...]
            const quizzes = Array.isArray(rawData) ? rawData : (rawData.data || []);

            // 3. Render Tabel
            if (quizzes.length > 0) {
                let html = '';
                quizzes.forEach((quiz, index) => {
                    
                    // Handle null classroom
                    const className = quiz.classroom ? quiz.classroom.name : '<span class="text-red-500 italic">Tanpa Kelas</span>';
                    const questionCount = quiz.questions_count || 0;

                    // URL Routes
                    const editUrl = `/teacher/quizzes/${quiz.id}/edit`;
                    const manageQuestionsUrl = `/teacher/quizzes/${quiz.id}/questions`; 

                    html += `
                        <tr class="hover:bg-gray-50 border-b last:border-0 transition">
                            <td class="px-6 py-4 text-gray-600">${index + 1}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                ${quiz.title}
                                <div class="text-xs text-gray-500 font-normal mt-1 truncate max-w-xs">${quiz.description || '-'}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded font-semibold">
                                    ${className}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <i class="fa-solid fa-list-ol mr-1"></i> ${questionCount} Soal
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    {{-- Tombol Kelola Soal --}}
                                    <a href="${manageQuestionsUrl}" class="p-2 bg-green-50 text-green-600 rounded hover:bg-green-100 transition" title="Kelola Soal">
                                        <i class="fa-solid fa-list-check"></i>
                                    </a>
                                    
                                    {{-- Tombol Edit --}}
                                    <a href="${editUrl}" class="p-2 bg-blue-50 text-blue-600 rounded hover:bg-blue-100 transition" title="Edit Kuis">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    {{-- Tombol Hapus --}}
                                    <button onclick="deleteQuiz(${quiz.id})" class="p-2 bg-red-50 text-red-600 rounded hover:bg-red-100 transition" title="Hapus Kuis">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">Belum ada kuis. Silakan buat kuis baru.</td></tr>`;
            }

        } catch (error) {
            console.error('Fetch Error:', error);
            errorMonitor.classList.remove('hidden');
            let msg = "Gagal memuat data.";
            
            if (error.response && error.response.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = "{{ route('login') }}";
                return;
            }
            
            errorMonitor.innerText = msg;
            tbody.innerHTML = `<tr><td colspan="5" class="px-6 py-10 text-center text-red-500 font-bold">Gagal mengambil data.</td></tr>`;
        }
    }

    // Fungsi Hapus
    window.deleteQuiz = async function(id) {
        if(!confirm('Yakin ingin menghapus kuis ini beserta semua soalnya?')) return;
        
        const token = localStorage.getItem('auth_token');
        try {
            await axios.delete(`${API_URL}/${id}`, {
                headers: { 
                    'Authorization': `Bearer ${token}`,
                    'ngrok-skip-browser-warning': 'true'
                }
            });
            alert('Kuis berhasil dihapus');
            fetchQuizzes(); 
        } catch (e) {
            console.error(e);
            alert('Gagal menghapus kuis.');
        }
    }
</script>
@endsection