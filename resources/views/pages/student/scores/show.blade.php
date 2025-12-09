@extends('layouts.student')

@section('title', 'Hasil Kuis')

@section('content')
    <div class="max-w-6xl mx-auto">
        
        {{-- Loading --}}
        <div id="loading-indicator" class="bg-white rounded-xl border p-10 flex flex-col items-center justify-center min-h-[400px]">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mb-3"></div>
            <p class="text-gray-500">Menghitung nilai...</p>
        </div>

        {{-- Error --}}
        <div id="error-container" class="hidden bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl text-center">
            <p class="font-bold">Gagal Memuat Hasil</p>
            <a href="{{ route('student.quizzes.index') }}" class="mt-2 inline-block underline">Kembali</a>
        </div>

        {{-- Content --}}
        <div id="score-content" class="hidden bg-white rounded-xl border p-6 md:p-10 text-center">
            <div class="mb-6">
                <i class="fa-solid fa-trophy text-6xl text-yellow-400 animate-bounce"></i>
                <h1 class="text-3xl font-bold text-gray-800 mt-4">Kuis Selesai!</h1>
                <p class="text-lg text-gray-600 mt-1">
                    <span id="quiz-title" class="font-semibold text-purple-700">...</span>
                </p>
            </div>

            <div class="mb-8">
                <p class="text-base text-gray-500">Skor Kamu:</p>
                <h2 class="text-6xl font-bold text-purple-600 my-2">
                    <span id="score-value">0</span>
                    <span class="text-2xl text-gray-500">/ 100</span>
                </h2>

                <div class="flex justify-center gap-8 mt-6">
                    <div class="text-center">
                        <span id="correct-count" class="block text-2xl font-bold text-green-600">0</span>
                        <span class="text-sm text-gray-500">Benar</span>
                    </div>
                    <div class="text-center">
                        <span id="total-count" class="block text-2xl font-bold text-gray-700">0</span>
                        <span class="text-sm text-gray-500">Total Soal</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-center gap-4">
                <a href="{{ route('student.quizzes.index') }}" class="px-6 py-2 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">
                    Daftar Kuis
                </a>
                <a href="{{ route('student.dashboard') }}" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const API_URL = ENV_URL.replace(/\/$/, '') + '/api';
            const token = localStorage.getItem('auth_token');

            // Ambil ID Score dari URL
            const pathSegments = window.location.pathname.split('/');
            const scoreId = pathSegments[pathSegments.length - 1];

            if (!scoreId) {
                alert("ID Skor tidak valid");
                return;
            }

            try {
                // Fetch Data Score dari API
                const response = await axios.get(`${API_URL}/student/scores/${scoreId}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'ngrok-skip-browser-warning': 'true' }
                });

                // Bersihkan respons jika tercemar PHP Warning (seperti kasus login)
                let rawData = response.data;
                if (typeof rawData === 'string') {
                    const jsonStart = rawData.indexOf('{');
                    const jsonEnd = rawData.lastIndexOf('}') + 1;
                    if(jsonStart !== -1) rawData = JSON.parse(rawData.substring(jsonStart, jsonEnd));
                }

                const scoreData = rawData.data || rawData; // Handle wrapper

                // Render
                document.getElementById('loading-indicator').classList.add('hidden');
                document.getElementById('score-content').classList.remove('hidden');

                document.getElementById('score-value').innerText = scoreData.score;
                document.getElementById('correct-count').innerText = scoreData.correct_count;
                document.getElementById('total-count').innerText = scoreData.total_count;
                
                if (scoreData.quiz) {
                    document.getElementById('quiz-title').innerText = scoreData.quiz.title;
                }

            } catch (error) {
                console.error("Error fetching score:", error);
                document.getElementById('loading-indicator').classList.add('hidden');
                document.getElementById('error-container').classList.remove('hidden');
            }
        });
    </script>
@endsection