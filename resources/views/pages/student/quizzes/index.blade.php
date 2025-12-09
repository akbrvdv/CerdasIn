@extends('layouts.student')

@section('title', 'Daftar Kuis')

@section('content')
    <div class="space-y-6">
        
        {{-- HEADER --}}
        <div class="max-w-6xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-purple-700">
                    Ayo Asah Kemampuanmu dengan Mengerjakan Kuis!
                </h1>
                <p class="text-gray-600 mt-1">
                    Berikut adalah daftar kuis untuk 
                    <span id="class-name" class="font-semibold text-purple-500 animate-pulse bg-gray-200 px-2 rounded">
                        Loading...
                    </span>.
                </p>
            </div>
        </div>

        {{-- LOADING STATE --}}
        <div id="loading-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i=0; $i<3; $i++)
            <div class="bg-white rounded-xl border overflow-hidden flex flex-col shadow-sm animate-pulse">
                <div class="w-full h-40 bg-gray-200"></div>
                <div class="p-6 space-y-3">
                    <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- ERROR STATE --}}
        <div id="error-container" class="hidden max-w-6xl mx-auto p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <p id="error-message">Gagal memuat daftar kuis.</p>
        </div>

        {{-- QUIZ LIST CONTAINER --}}
        <div id="quiz-container" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Item kuis akan dirender di sini oleh JS --}}
        </div>

        {{-- EMPTY STATE --}}
        <div id="empty-container" class="hidden lg:col-span-3 md:col-span-2 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-700 p-6 rounded-lg">
            <p class="font-bold text-lg">Belum Ada Kuis</p>
            <p>Saat ini belum ada kuis yang ditambahkan untuk kelas ini.</p>
        </div>

    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // 1. Setup Config
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Headers
            const headers = { 
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true',
                'Content-Type': 'application/json'
            };

            try {
                // 2. Fetch Data Paralel
                const [userResRaw, quizzesResRaw] = await Promise.all([
                    axios.get(`${API_URL}/student/classrooms`, { headers }),
                    axios.get(`${API_URL}/student/quizzes`, { headers })
                ]);

                // 3. Bersihkan Data
                const userDataWrapper = parseResponse(userResRaw.data);
                const quizzesDataWrapper = parseResponse(quizzesResRaw.data);

                const userData = userDataWrapper.data || userDataWrapper;
                const quizzes = quizzesDataWrapper.data || quizzesDataWrapper;

                // --- RENDER NAMA KELAS ---
                const classNameEl = document.getElementById('class-name');
                if (userData.selected_class) {
                    classNameEl.innerText = userData.selected_class.name;
                    classNameEl.classList.remove('animate-pulse', 'bg-gray-200', 'px-2', 'rounded');
                } else {
                    classNameEl.innerText = "(Belum Pilih Kelas)";
                }

                // --- RENDER DAFTAR KUIS ---
                const container = document.getElementById('quiz-container');
                const loading = document.getElementById('loading-container');
                const empty = document.getElementById('empty-container');

                loading.classList.add('hidden');

                if (quizzes && quizzes.length > 0) {
                    container.classList.remove('hidden');

                    quizzes.forEach(quiz => {
                        let actionSection = '';

                        // Cek apakah sudah dikerjakan (ada my_score)
                        if (quiz.my_score) {
                            const scoreUrl = `/student/scores/${quiz.my_score.id}`;
                            actionSection = `
                                <div class="text-center">
                                    <p class="text-sm text-gray-500 mb-1">Skor Kamu:</p>
                                    <p class="text-2xl font-bold text-purple-600">
                                        ${quiz.my_score.score} <span class="text-base text-gray-400">/ 100</span>
                                    </p>
                                    <a href="${scoreUrl}" class="mt-3 inline-flex items-center justify-center w-full gap-2 bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span>Lihat Hasil</span>
                                    </a>
                                </div>
                            `;
                        } else {
                            const startUrl = `/student/quizzes/${quiz.id}`;
                            actionSection = `
                                <a href="${startUrl}" class="inline-flex items-center justify-center w-full gap-2 bg-purple-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-purple-700 transition-colors">
                                    <i class="fa-solid fa-play text-xs"></i>
                                    <span>Mulai Kuis</span>
                                </a>
                            `;
                        }

                        const questionCount = quiz.questions_count || 0;

                        const html = `
                            <div class="bg-white rounded-xl border overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 shadow-sm hover:shadow-md">
                                <div class="relative w-full h-40 bg-purple-600 flex items-center justify-center">
                                    <i class="fa-solid fa-puzzle-piece text-white text-6xl opacity-70"></i>
                                </div>
                                <div class="p-6 flex-grow">
                                    <h2 class="text-xl font-bold text-gray-800 line-clamp-2 mb-2" title="${quiz.title}">
                                        ${quiz.title}
                                    </h2>
                                    <p class="text-sm text-gray-600 line-clamp-3 mb-4">
                                        ${quiz.description || 'Tidak ada deskripsi.'}
                                    </p>
                                    <div class="text-sm font-semibold text-purple-700">
                                        <i class="fa-solid fa-list-ol mr-1.5"></i>
                                        ${questionCount} Soal
                                    </div>
                                </div>
                                <div class="p-4 border-t bg-gray-50">
                                    ${actionSection}
                                </div>
                            </div>
                        `;
                        container.innerHTML += html;
                    });

                } else {
                    empty.classList.remove('hidden');
                }

            } catch (error) {
                console.error('Error fetching quizzes:', error);
                document.getElementById('loading-container').classList.add('hidden');

                // Handle Error 400 (Belum Pilih Kelas)
                if (error.response && error.response.status === 400) {
                    let msg = "Silakan pilih kelas terlebih dahulu.";
                    try {
                        const errData = parseResponse(error.response.data);
                        if(errData.message) msg = errData.message;
                    } catch(e) {}

                    alert(msg);
                    window.location.href = "{{ route('student.dashboard') }}";
                    return;
                }

                if (error.response && error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                document.getElementById('error-container').classList.remove('hidden');
            }
        });

        // Helper Parse (Membersihkan Warning PHP)
        function parseResponse(rawData) {
            if (typeof rawData === 'string') {
                const jsonStartIndex = rawData.indexOf('{');
                const jsonEndIndex = rawData.lastIndexOf('}') + 1;
                
                if (jsonStartIndex !== -1) {
                    const jsonString = rawData.substring(jsonStartIndex, jsonEndIndex);
                    try {
                        return JSON.parse(jsonString);
                    } catch (e) {
                        return rawData;
                    }
                }
            }
            return rawData;
        }
    </script>
@endsection