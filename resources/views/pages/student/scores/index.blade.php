@extends('layouts.student')

@section('title', 'Riwayat Nilai')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- 1. HEADER --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-purple-700 flex items-center gap-2">
                    Riwayat Nilai
                </h1>
                <p class="text-gray-600 mt-1">
                    Berikut adalah hasil pengerjaan kuis Anda di kelas 
                    <span id="class-name" class="font-semibold text-purple-500 animate-pulse bg-gray-200 px-2 rounded">
                        Loading...
                    </span>.
                </p>
            </div>
        </div>

        {{-- 2. LOADING STATE --}}
        <div id="loading-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for($i=0; $i<3; $i++)
            <div class="bg-white rounded-xl border overflow-hidden flex flex-col shadow-sm animate-pulse">
                <div class="p-6 space-y-4">
                    <div class="flex justify-between">
                        <div class="h-4 bg-gray-200 rounded w-1/3"></div>
                        <div class="h-4 bg-gray-200 rounded w-10"></div>
                    </div>
                    <div class="h-8 bg-gray-200 rounded w-full"></div>
                    <div class="h-20 bg-gray-200 rounded w-full"></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- 3. ERROR STATE --}}
        <div id="error-container" class="hidden max-w-6xl mx-auto p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 text-center">
            <p id="error-message">Gagal memuat data nilai.</p>
        </div>

        {{-- 4. SCORES GRID (Diisi JS) --}}
        <div id="scores-container" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Item score akan dirender di sini --}}
        </div>

        {{-- 5. EMPTY STATE --}}
        <div id="empty-container" class="hidden lg:col-span-3 md:col-span-2 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-700 p-6 rounded-lg">
            <p class="font-bold text-lg">Belum Ada Nilai</p>
            <p>Anda belum mengerjakan kuis apapun di kelas ini.</p>
            <a href="{{ route('student.quizzes.index') }}" class="mt-2 inline-block font-semibold underline hover:text-yellow-800">
                Lihat Daftar Kuis
            </a>
        </div>

    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Config
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            const headers = { 
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true',
                'Content-Type': 'application/json'
            };

            try {
                // Fetch Data Paralel (User Info & Scores List)
                const [userResRaw, scoresResRaw] = await Promise.all([
                    axios.get(`${API_URL}/student/classrooms`, { headers }),
                    axios.get(`${API_URL}/student/scores`, { headers }) // Endpoint baru
                ]);

                // Bersihkan Data
                const userData = parseResponse(userResRaw.data);
                const scoresData = parseResponse(scoresResRaw.data);

                const user = userData.data || userData;
                const scores = scoresData.data || scoresData;

                // --- RENDER NAMA KELAS ---
                const classNameEl = document.getElementById('class-name');
                if (user.selected_class) {
                    classNameEl.innerText = user.selected_class.name;
                    classNameEl.classList.remove('animate-pulse', 'bg-gray-200', 'px-2', 'rounded');
                } else {
                    classNameEl.innerText = "(Belum Pilih Kelas)";
                }

                // --- RENDER LIST NILAI ---
                const container = document.getElementById('scores-container');
                const loading = document.getElementById('loading-container');
                const empty = document.getElementById('empty-container');

                loading.classList.add('hidden');

                if (scores && scores.length > 0) {
                    container.classList.remove('hidden');

                    scores.forEach(score => {
                        // Link Detail
                        const detailUrl = `/student/scores/${score.id}`;
                        
                        // Tanggal
                        const dateObj = new Date(score.created_at);
                        const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });

                        // Warna Score
                        let scoreColor = 'text-purple-600';
                        if(score.score < 50) scoreColor = 'text-red-500';
                        else if(score.score >= 80) scoreColor = 'text-green-600';

                        const html = `
                            <div class="bg-white rounded-xl border overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 shadow-sm hover:shadow-md">
                                <div class="p-6 flex-grow">
                                    
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="bg-purple-100 text-purple-700 text-xs font-bold px-2 py-1 rounded uppercase tracking-wide">
                                            Kuis
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            ${dateStr}
                                        </div>
                                    </div>

                                    <h2 class="text-lg font-bold text-gray-800 line-clamp-2 mb-1" title="${score.quiz?.title}">
                                        ${score.quiz?.title || 'Judul Kuis Tidak Tersedia'}
                                    </h2>

                                    <div class="mt-4 flex items-end gap-2">
                                        <span class="text-4xl font-bold ${scoreColor}">
                                            ${score.score}
                                        </span>
                                        <span class="text-sm text-gray-400 mb-1">/ 100</span>
                                    </div>

                                    <div class="mt-2 text-sm text-gray-500">
                                        Benar: <span class="font-medium text-gray-700">${score.correct_count}</span> dari ${score.total_count} soal
                                    </div>
                                </div>

                                <div class="p-4 border-t bg-gray-50">
                                    <a href="${detailUrl}" class="flex items-center justify-center w-full gap-2 text-purple-700 font-medium hover:text-purple-900 transition-colors">
                                        <span>Lihat Detail</span>
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        `;
                        container.innerHTML += html;
                    });

                } else {
                    empty.classList.remove('hidden');
                }

            } catch (error) {
                console.error('Error fetching scores:', error);
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

        // Helper Parse PHP Warnings
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