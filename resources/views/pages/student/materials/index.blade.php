@extends('layouts.student')

@section('title', 'Materi Belajar')

@section('content')
    <div class="space-y-6">
        
        {{-- HEADER --}}
        <div class="max-w-6xl mx-auto">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-purple-700">
                    Mau Belajar Apa Hari Ini?
                </h1>
                <p class="text-gray-600 mt-1">
                    Berikut adalah daftar materi untuk 
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
                <div class="w-full h-48 bg-gray-200"></div>
                <div class="p-4 space-y-3">
                    <div class="h-6 bg-gray-200 rounded w-3/4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                </div>
                <div class="p-4 mt-auto">
                    <div class="h-10 bg-gray-200 rounded"></div>
                </div>
            </div>
            @endfor
        </div>

        {{-- ERROR STATE --}}
        <div id="error-container" class="hidden max-w-6xl mx-auto p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
            <p id="error-message">Gagal memuat data materi.</p>
        </div>

        {{-- MATERIALS GRID (Diisi JS) --}}
        <div id="materials-container" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Item materi akan dirender di sini --}}
        </div>

        {{-- EMPTY STATE --}}
        <div id="empty-container" class="hidden lg:col-span-3 md:col-span-2 border-l-4 border-yellow-500 bg-yellow-50 text-yellow-700 p-6 rounded-lg">
            <p class="font-bold text-lg">Belum Ada Materi</p>
            <p>Saat ini belum ada materi yang ditambahkan untuk kelas ini.</p>
        </div>

    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // 1. Konfigurasi
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, ''); // Hapus slash akhir jika ada
            const API_URL = `${BASE_URL}/api`;
            
            const token = localStorage.getItem('auth_token');

            // Cek Token
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Headers standar
            const headers = { 
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true',
                'Content-Type': 'application/json'
            };

            try {
                // 2. Request Data (Paralel)
                const [userResRaw, materialsResRaw] = await Promise.all([
                    axios.get(`${API_URL}/student/classrooms`, { headers }),
                    axios.get(`${API_URL}/student/materials`, { headers })
                ]);

                // 3. Bersihkan Data (Handle PHP Warnings jika ada)
                const userData = parseResponse(userResRaw.data);
                const materialsData = parseResponse(materialsResRaw.data);

                // Extract data dari wrapper (jika ada properti .data)
                const user = userData.data || userData;
                const materials = materialsData.data || materialsData;

                // --- RENDER NAMA KELAS ---
                const classNameEl = document.getElementById('class-name');
                if (user.selected_class) {
                    classNameEl.innerText = user.selected_class.name;
                    classNameEl.classList.remove('animate-pulse', 'bg-gray-200', 'px-2', 'rounded');
                } else {
                    classNameEl.innerText = "(Belum Pilih Kelas)";
                }

                // --- RENDER MATERI ---
                const container = document.getElementById('materials-container');
                const loading = document.getElementById('loading-container');
                const empty = document.getElementById('empty-container');

                loading.classList.add('hidden'); // Sembunyikan loading

                if (materials && materials.length > 0) {
                    container.classList.remove('hidden'); // Tampilkan grid
                    
                    materials.forEach(material => {
                        // Setup Thumbnail URL
                        let thumbUrl = `${BASE_URL}/storage/thumbnails/default.png`;
                        if (material.thumbnail) {
                            const cleanPath = material.thumbnail.replace(/^\//, '');
                            thumbUrl = `${BASE_URL}/storage/${cleanPath}`;
                        }

                        // Setup Link Detail
                        const detailUrl = `/student/materials/${material.id}`;
                        
                        // Format Tanggal
                        const dateObj = new Date(material.created_at);
                        const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });

                        // Setup Tombol File
                        let fileButtons = '';
                        if (material.file_path) {
                            const cleanFilePath = material.file_path.replace(/^\//, '');
                            const fileUrl = `${BASE_URL}/storage/${cleanFilePath}`;
                            
                            fileButtons = `
                                <div class="flex flex-col sm:flex-row gap-3">
                                    <a href="${detailUrl}" class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition-all">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span>Lihat</span>
                                    </a>
                                    <a href="${fileUrl}" download target="_blank" class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition-all">
                                        <i class="fa-solid fa-download text-xs"></i>
                                        <span>Unduh</span>
                                    </a>
                                </div>
                            `;
                        } else {
                            fileButtons = `
                                <span class="inline-flex items-center justify-center w-full gap-2 bg-gray-100 text-gray-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed border border-gray-200">
                                    <i class="fa-solid fa-ban text-xs"></i>
                                    <span>Hanya Bacaan</span>
                                </span>
                            `;
                        }

                        // Template HTML Item
                        const itemHtml = `
                            <div class="bg-white rounded-xl border overflow-hidden flex flex-col group transition-all duration-300 hover:-translate-y-1 hover:border-purple-300 shadow-sm hover:shadow-md">
                                <a href="${detailUrl}">
                                    <div class="relative w-full h-48 overflow-hidden bg-gray-100">
                                        <img src="${thumbUrl}" alt="${material.title}" 
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            onerror="this.src='https://placehold.co/600x400?text=No+Image'">
                                    </div>

                                    <div class="px-4 pt-4 flex-grow">
                                        <h2 class="text-lg font-bold text-gray-800 line-clamp-2 mb-2 group-hover:text-purple-700 transition-colors" title="${material.title}">
                                            ${material.title}
                                        </h2>
                                    </div>

                                    <div class="p-4 mt-auto">
                                        <p class="text-xs text-gray-500 mb-4 flex items-center">
                                            <i class="fa-regular fa-calendar-alt mr-1.5"></i>
                                            ${dateStr}
                                        </p>
                                        ${fileButtons}
                                    </div>
                                </a>
                            </div>
                        `;

                        container.innerHTML += itemHtml;
                    });

                } else {
                    empty.classList.remove('hidden');
                }

            } catch (error) {
                console.error('Error fetching materials:', error);
                document.getElementById('loading-container').classList.add('hidden');

                // --- HANDLE ERROR 400 (Belum Pilih Kelas) ---
                if (error.response && error.response.status === 400) {
                    // Ambil pesan dari backend
                    let msg = "Silakan pilih kelas terlebih dahulu.";
                    // Coba bersihkan response error juga jika tercemar
                    try {
                        const errData = parseResponse(error.response.data);
                        if(errData.message) msg = errData.message;
                    } catch(e) {}

                    alert(msg);
                    window.location.href = "{{ route('student.dashboard') }}";
                    return;
                }

                // Handle 401
                if (error.response && error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                document.getElementById('error-container').classList.remove('hidden');
            }
        });

        // --- Helper: Membersihkan Response dari PHP Warnings ---
        function parseResponse(rawData) {
            if (typeof rawData === 'string') {
                const jsonStartIndex = rawData.indexOf('{');
                const jsonEndIndex = rawData.lastIndexOf('}') + 1;
                
                if (jsonStartIndex !== -1) {
                    const jsonString = rawData.substring(jsonStartIndex, jsonEndIndex);
                    try {
                        return JSON.parse(jsonString);
                    } catch (e) {
                        console.error("Gagal parsing JSON manual", e);
                        return rawData;
                    }
                }
            }
            return rawData;
        }
    </script>
@endsection