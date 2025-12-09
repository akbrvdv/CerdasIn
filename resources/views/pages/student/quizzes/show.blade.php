@extends('layouts.student')

@section('title', 'Mengerjakan Kuis')

@section('content')
    <div class="max-w-6xl mx-auto">
        
        {{-- HEADER --}}
        <div class="mb-6">
            <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-purple-700 transition-colors">
                <i class="fa-solid fa-arrow-left text-sm"></i>
                <span>Kembali ke Daftar</span>
            </a>
        </div>

        {{-- LOADING STATE --}}
        <div id="loading-indicator" class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-xl border p-8">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mb-3"></div>
            <p class="text-gray-500">Memuat soal kuis...</p>
        </div>

        {{-- ERROR STATE --}}
        <div id="error-container" class="hidden bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl text-center">
            <p class="font-bold mb-2">Gagal Memuat Kuis</p>
            <p id="error-message">Terjadi kesalahan.</p>
            <a href="{{ route('student.quizzes.index') }}" class="mt-4 inline-block text-red-600 hover:underline">Kembali</a>
        </div>

        {{-- CONTENT CONTAINER --}}
        <div id="quiz-content" class="hidden">
            
            <div class="mb-8">
                <h1 id="quiz-title" class="text-2xl sm:text-3xl font-bold text-purple-700">Loading...</h1>
                <p id="quiz-description" class="text-gray-600 mt-1">...</p>
                <p class="text-sm text-purple-700 font-semibold mt-2">
                    <i class="fa-solid fa-list-ol mr-1.5"></i>
                    Total Soal: <span id="question-count">0</span>
                </p>
            </div>

            <form id="quiz-form">
                <div id="questions-list" class="space-y-6">
                    {{-- Soal akan dirender di sini --}}
                </div>

                <div class="mt-8">
                    <button type="submit" id="btn-submit"
                            class="w-full inline-flex items-center justify-center gap-2 bg-purple-600 text-white px-6 py-3 rounded-lg text-base font-medium hover:bg-purple-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Selesai & Kirim Jawaban</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const API_URL = ENV_URL.replace(/\/$/, '') + '/api';
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Ambil ID Kuis dari URL
            const pathSegments = window.location.pathname.split('/');
            const quizId = pathSegments[pathSegments.length - 1];

            // 1. Fetch Soal
            try {
                const response = await axios.get(`${API_URL}/student/quizzes/${quizId}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'ngrok-skip-browser-warning': 'true' }
                });
                
                // Ambil data dari wrapper JSON
                const quizData = response.data.data || response.data;
                renderQuiz(quizData);

            } catch (error) {
                console.error('Error fetching quiz:', error);
                document.getElementById('loading-indicator').classList.add('hidden');
                document.getElementById('error-container').classList.remove('hidden');
            }

            // 2. Handle Submit
            document.getElementById('quiz-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if(!confirm("Yakin ingin mengirim jawaban? Anda tidak bisa mengubahnya lagi.")) return;

                const btn = document.getElementById('btn-submit');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Mengirim...';
                btn.disabled = true;

                try {
                    // Kumpulkan Jawaban dari Form
                    const formData = new FormData(this);
                    const answers = {};
                    
                    // Format data agar sesuai validasi backend: 'answers' => ['question_id' => 'option']
                    for (let [key, value] of formData.entries()) {
                        // Regex untuk mengambil ID soal dari name="answers[123]"
                        const match = key.match(/answers\[(\d+)\]/);
                        if (match) {
                            answers[match[1]] = value;
                        }
                    }

                    // POST ke API Submit
                    const submitResponse = await axios.post(`${API_URL}/student/quizzes/${quizId}/submit`, 
                        { answers: answers },
                        { headers: { 'Authorization': `Bearer ${token}` } }
                    );

                    // Ambil ID Score dari respons API untuk redirect
                    const responseData = submitResponse.data.data || submitResponse.data;
                    const scoreId = responseData.id || responseData.score?.id;

                    if (scoreId) {
                        // Redirect ke halaman detail skor Frontend
                        window.location.href = `/student/scores/${scoreId}`;
                    } else {
                        // Fallback jika ID tidak ditemukan
                        window.location.href = "{{ route('student.quizzes.index') }}";
                    }

                } catch (error) {
                    console.error('Submit error:', error);
                    alert("Gagal mengirim jawaban. Silakan coba lagi.");
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
        });

        function renderQuiz(quiz) {
            document.getElementById('loading-indicator').classList.add('hidden');
            document.getElementById('quiz-content').classList.remove('hidden');

            document.getElementById('quiz-title').innerText = quiz.title;
            document.getElementById('quiz-description').innerText = quiz.description || '-';
            
            const questions = quiz.questions || [];
            document.getElementById('question-count').innerText = questions.length;

            const container = document.getElementById('questions-list');
            container.innerHTML = '';

            questions.forEach((q, index) => {
                const questionHtml = `
                    <div class="bg-white rounded-xl border overflow-hidden">
                        <div class="p-5 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold">Soal No. ${index + 1}</h2>
                        </div>
                        <div class="p-6">
                            <p class="text-base mb-5 font-medium">${q.question}</p>
                            <div class="space-y-3">
                                ${renderOption(q.id, 'a', q.option_a)}
                                ${renderOption(q.id, 'b', q.option_b)}
                                ${renderOption(q.id, 'c', q.option_c)}
                                ${renderOption(q.id, 'd', q.option_d)}
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += questionHtml;
            });
        }

        function renderOption(qId, key, text) {
            if(!text) return '';
            return `
                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-purple-50 transition-colors">
                    <input type="radio" name="answers[${qId}]" value="${key}" class="h-4 w-4 text-purple-600 focus:ring-purple-500">
                    <span class="ml-3 text-gray-700">${text}</span>
                </label>
            `;
        }
    </script>
@endsection