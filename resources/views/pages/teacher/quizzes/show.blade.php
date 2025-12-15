@extends('layouts.teacher')

@section('title', 'Detail Kuis')

@section('content')
    <div class="p-6">
        
        {{-- 1. HEADER & DETAIL KUIS --}}
        <div class="bg-white rounded-2xl p-6 border mb-6 shadow-sm">
            {{-- Tombol Kembali --}}
            <div>
                <a href="{{ route('teacher.quizzes.index') }}"
                    class="inline-flex items-center text-slate-400 hover:text-slate-600 mb-4 font-medium transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="flex flex-col md:flex-row justify-between gap-4">
                {{-- Info Kuis (Judul & Deskripsi) --}}
                <div>
                    <h1 id="quiz-title" class="text-2xl md:text-3xl font-bold text-gray-800 animate-pulse bg-gray-200 text-transparent rounded w-fit mb-2">Loading...</h1>
                    <p id="quiz-description" class="text-gray-600 mt-2">...</p>
                </div>

                {{-- Action Buttons (Edit/Delete Quiz) --}}
                <div class="flex items-center gap-2">
                    <a id="btn-edit-quiz" href="#"
                        class="bg-transparent border rounded-md p-2 hover:border-blue-400 hover:text-blue-400 transition"
                        title="Edit Kuis">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                    
                    <button onclick="deleteQuiz()"
                        class="bg-transparent border rounded-md p-2 hover:border-red-400 hover:text-red-400 transition"
                        title="Hapus Kuis">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- 2. DAFTAR PERTANYAAN --}}
        <div class="bg-white rounded-2xl p-6 border shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <h2 class="text-xl md:text-2xl font-semibold">Daftar Pertanyaan</h2>

                <a id="btn-create-question" href="#"
                    class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-lg transition flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Tambah Pertanyaan
                </a>
            </div>

            {{-- Loading State --}}
            <div id="loading-indicator" class="text-center py-10">
                <i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-2xl mb-2"></i>
                <p class="text-gray-500">Memuat pertanyaan...</p>
            </div>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden text-center py-10 text-gray-500">
                Belum ada pertanyaan untuk kuis ini.
            </div>

            {{-- Table Container --}}
            <div id="questions-table-container" class="hidden overflow-x-auto rounded-lg border border-gray-100">
                <table class="min-w-full text-sm text-gray-700">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left w-16">No</th>
                            <th class="py-3 px-6 text-left">Pertanyaan</th>
                            <th class="py-3 px-6 text-center w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="questions-table-body" class="divide-y divide-gray-100">
                        {{-- Data Questions akan dirender di sini --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetchQuizDetails();
        });

        // --- KONFIGURASI ---
        const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
        const BASE_URL = ENV_URL.replace(/\/$/, '');
        
        // Ambil ID Kuis dari URL Browser (Asumsi URL: /teacher/quizzes/{id})
        // Jika URL Anda berbeda (misal /teacher/quizzes/{id}/questions), sesuaikan logic ini
        const pathSegments = window.location.pathname.split('/');
        
        // Cari segmen setelah 'quizzes'
        const quizzesIndex = pathSegments.indexOf('quizzes');
        const quizId = (quizzesIndex !== -1 && pathSegments[quizzesIndex + 1]) ? pathSegments[quizzesIndex + 1] : null;

        const API_URL = `${BASE_URL}/api/teacher/quizzes/${quizId}`; // Endpoint API Detail Kuis

        async function fetchQuizDetails() {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            if (!quizId) {
                alert("ID Kuis tidak ditemukan di URL.");
                return;
            }

            try {
                // 1. Fetch Data
                // Pastikan API Detail Kuis mengembalikan relasi 'questions'
                // Jika tidak, Anda mungkin perlu memanggil endpoint terpisah untuk questions
                const response = await axios.get(API_URL, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true'
                    }
                });

                // 2. Parse Data (Membersihkan PHP Warning jika ada)
                let rawData = response.data;
                if (typeof rawData === 'string' && rawData.includes('{')) {
                    try { 
                        rawData = JSON.parse(rawData.substring(rawData.indexOf('{'))); 
                    } catch (e) {}
                }

                const quizData = rawData.data || rawData; // Handle wrapper

                renderPage(quizData);

            } catch (error) {
                console.error("Error fetching quiz:", error);
                document.getElementById('loading-indicator').classList.add('hidden');
                
                if (error.response && error.response.status === 404) {
                    alert("Kuis tidak ditemukan.");
                    window.location.href = "{{ route('teacher.quizzes.index') }}";
                } else if (error.response && error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                } else {
                    alert("Gagal memuat data kuis.");
                }
            }
        }

        function renderPage(quiz) {
            // Sembunyikan Loading
            document.getElementById('loading-indicator').classList.add('hidden');

            // 1. Render Header Info
            const titleEl = document.getElementById('quiz-title');
            titleEl.innerText = quiz.title;
            titleEl.classList.remove('animate-pulse', 'bg-gray-200', 'text-transparent', 'w-fit');
            
            document.getElementById('quiz-description').innerText = quiz.description || 'Tidak ada deskripsi.';

            // 2. Update Link Buttons (Edit Quiz & Create Question)
            // URL Web: /teacher/quizzes/{id}/edit
            document.getElementById('btn-edit-quiz').href = `/teacher/quizzes/${quiz.id}/edit`;
            // URL Web: /teacher/quizzes/{id}/questions/create
            document.getElementById('btn-create-question').href = `/teacher/quizzes/${quiz.id}/questions/create`;

            // 3. Render Daftar Pertanyaan
            const questions = quiz.questions || [];
            
            if (questions.length === 0) {
                document.getElementById('empty-state').classList.remove('hidden');
            } else {
                document.getElementById('questions-table-container').classList.remove('hidden');
                const tbody = document.getElementById('questions-table-body');
                tbody.innerHTML = '';

                questions.forEach((q, index) => {
                    // URL Web: /teacher/quizzes/{quiz_id}/questions/{question_id}/edit
                    const editUrl = `/teacher/quizzes/${quiz.id}/questions/${q.id}/edit`;

                    const row = `
                        <tr class="hover:bg-purple-50 transition">
                            <td class="py-4 px-6 text-gray-500">${index + 1}</td>
                            <td class="py-4 px-6">
                                <p class="text-gray-800 font-medium line-clamp-2">${q.question}</p>
                            </td>
                            <td class="py-4 px-6 text-center space-x-2">
                                <a href="${editUrl}" 
                                   class="inline-flex items-center justify-center border rounded-md p-2 hover:border-blue-400 hover:text-blue-400 transition" 
                                   title="Edit Soal">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <button onclick="deleteQuestion(${q.id})" 
                                        class="inline-flex items-center justify-center border rounded-md p-2 hover:border-red-400 hover:text-red-400 transition" 
                                        title="Hapus Soal">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        }

        // --- FUNGSI HAPUS KUIS ---
        window.deleteQuiz = async function() {
            if(!confirm('Yakin ingin menghapus kuis INI beserta semua soalnya?')) return;
            const token = localStorage.getItem('auth_token');
            
            try {
                // DELETE /api/teacher/quizzes/{id}
                await axios.delete(API_URL, { 
                    headers: { 'Authorization': `Bearer ${token}`, 'ngrok-skip-browser-warning': 'true' }
                });
                alert('Kuis berhasil dihapus.');
                window.location.href = "{{ route('teacher.quizzes.index') }}";
            } catch (e) {
                console.error(e);
                alert('Gagal menghapus kuis.');
            }
        }

        // --- FUNGSI HAPUS PERTANYAAN ---
        window.deleteQuestion = async function(questionId) {
            if(!confirm('Yakin ingin menghapus pertanyaan ini?')) return;
            const token = localStorage.getItem('auth_token');

            try {
                // Endpoint DELETE Question
                // Sesuaikan dengan API Resource (Nested): /api/teacher/quizzes/{quiz_id}/questions/{question_id}
                const deleteUrl = `${API_URL}/questions/${questionId}`;

                await axios.delete(deleteUrl, {
                    headers: { 'Authorization': `Bearer ${token}`, 'ngrok-skip-browser-warning': 'true' }
                });
                
                alert('Pertanyaan berhasil dihapus.');
                location.reload(); // Refresh halaman

            } catch (e) {
                console.error("Delete Question Error:", e);
                // Coba fallback jika URL nested berbeda
                alert('Gagal menghapus pertanyaan. Pastikan endpoint API benar.');
            }
        }
    </script>
@endsection