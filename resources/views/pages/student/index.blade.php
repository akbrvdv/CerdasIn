@extends('layouts.student')

@section('title', 'Dashboard Siswa')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- 1. NOTIFIKASI / ALERT --}}
        <div id="alert-container" class="hidden px-4 py-3 rounded-lg border relative mb-5 transition-all duration-300" role="alert">
            <span id="alert-message" class="block sm:inline font-medium"></span>
        </div>

        {{-- 2. HEADER: Sapaan User --}}
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-purple-700 flex items-center gap-2">
                    Hai, <span id="user-name" class="animate-pulse bg-gray-200 rounded px-2 text-transparent">User</span>! 👋
                </h1>
                <p class="text-gray-600 mt-1">
                    Selamat datang di dashboard siswa.
                    <span id="instruction-text" class="hidden">Silakan pilih kelas untuk memulai.</span>
                </p>
            </div>
        </div>

        {{-- 3. CARD: INFO KELAS / PILIH KELAS --}}
        <div class="bg-white rounded-xl border p-6 min-h-[150px] shadow-sm relative">
            
            {{-- Loading Spinner --}}
            <div id="loading-indicator" class="absolute inset-0 flex flex-col items-center justify-center bg-white z-10 rounded-xl">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mb-2"></div>
                <p class="text-sm text-gray-500">Memuat data...</p>
            </div>

            {{-- STATE A: Siswa Sudah Punya Kelas --}}
            <div id="current-class-section" class="hidden">
                <div class="bg-purple-50 border border-purple-200 text-purple-900 p-5 rounded-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <p class="text-sm text-purple-600 font-medium mb-1">Kelas Anda saat ini:</p>
                        <h2 class="text-2xl font-bold text-purple-800" id="current-class-name">-</h2>
                    </div>
                    <button onclick="toggleChangeClassMode()" class="px-4 py-2 text-sm font-medium text-purple-600 bg-white border border-purple-200 rounded-lg hover:bg-purple-50 transition shadow-sm">
                        <i class="fa-solid fa-pencil mr-1"></i> Ganti Kelas
                    </button>
                </div>
            </div>

            {{-- STATE B: Form Pilih Kelas --}}
            <div id="select-class-section" class="hidden mt-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Pilih Kelas</h2>
                    <button id="btn-cancel-change" onclick="toggleChangeClassMode()" class="hidden text-sm text-gray-500 hover:text-gray-700 underline">
                        Batal
                    </button>
                </div>
                
                {{-- Container Tombol Kelas --}}
                <div id="classes-list-container" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                    {{-- Diisi oleh JavaScript --}}
                </div>
            </div>
        </div>

        {{-- 4. MENU CEPAT --}}
        <div>
            <h2 class="text-lg font-semibold mb-3 text-gray-800">Menu Cepat</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                <a id="menu-materi" href="#" class="group flex flex-col items-center justify-center p-6 rounded-xl border bg-gray-50 opacity-60 cursor-not-allowed transition-all duration-300">
                    <div class="h-12 w-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 text-gray-400 group-hover:text-purple-600 group-hover:shadow-md transition-all"> 
                        <i class="fa-solid fa-book-open text-xl"></i> 
                    </div>
                    <h3 class="text-lg font-semibold text-gray-500 group-hover:text-purple-700">Materi</h3>
                    <p class="text-sm text-gray-400 mt-1 text-center">Modul & Bahan Ajar</p>
                </a>

                <a id="menu-kuis" href="#" class="group flex flex-col items-center justify-center p-6 rounded-xl border bg-gray-50 opacity-60 cursor-not-allowed transition-all duration-300">
                    <div class="h-12 w-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 text-gray-400 group-hover:text-purple-600 group-hover:shadow-md transition-all"> 
                        <i class="fa-solid fa-puzzle-piece text-xl"></i> 
                    </div>
                    <h3 class="text-lg font-semibold text-gray-500 group-hover:text-purple-700">Kuis</h3>
                    <p class="text-sm text-gray-400 mt-1 text-center">Latihan & Ujian</p>
                </a>

                <a id="menu-nilai" href="#" class="group flex flex-col items-center justify-center p-6 rounded-xl border bg-gray-50 opacity-60 cursor-not-allowed transition-all duration-300">
                    <div class="h-12 w-12 bg-white rounded-full flex items-center justify-center shadow-sm mb-3 text-gray-400 group-hover:text-purple-600 group-hover:shadow-md transition-all"> 
                        <i class="fa-solid fa-chart-column text-xl"></i> 
                    </div>
                    <h3 class="text-lg font-semibold text-gray-500 group-hover:text-purple-700">Nilai</h3>
                    <p class="text-sm text-gray-400 mt-1 text-center">Hasil Belajar</p>
                </a>

            </div>
        </div>
    </div>

    {{-- 5. JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Konfigurasi
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Init
            await initializeDashboard();
        });

        // --- FUNGSI UTAMA ---
        async function initializeDashboard() {
            try {
                // Request Data
                const responseRaw = await axios.get(`${API_BASE_URL}/student/classrooms`, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true',
                        'Content-Type': 'application/json'
                    }
                });

                // [PENTING] Bersihkan Data dari PHP Warning
                const responseClean = parseResponse(responseRaw.data);
                const userData = responseClean.data || responseClean; 

                // Render User
                const nameEl = document.getElementById('user-name');
                if (userData.name) {
                    nameEl.innerText = userData.name;
                    nameEl.classList.remove('animate-pulse', 'bg-gray-200', 'text-transparent');
                }

                // Logic Tampilan
                if (userData.selected_class_id && userData.selected_class) {
                    renderActiveClass(userData.selected_class);
                } else {
                    document.getElementById('instruction-text').classList.remove('hidden');
                    document.getElementById('select-class-section').classList.remove('hidden');
                    await fetchAndRenderClassList(null); 
                }

            } catch (error) {
                console.error('Error init dashboard:', error);
                handleError(error);
            } finally {
                document.getElementById('loading-indicator').classList.add('hidden');
            }
        }

        // --- AMBIL LIST KELAS ---
        async function fetchAndRenderClassList(currentClassId) {
            const container = document.getElementById('classes-list-container');
            container.innerHTML = '<div class="col-span-full text-center text-gray-500 py-4">Memuat opsi kelas...</div>';

            try {
                const responseRaw = await axios.get(`${API_BASE_URL}/student/list-classes`, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true',
                        'Content-Type': 'application/json'
                    }
                });

                // [PENTING] Bersihkan Data
                const responseClean = parseResponse(responseRaw.data);
                const classes = responseClean.data || responseClean;

                container.innerHTML = ''; 

                // Validasi Array
                if (!Array.isArray(classes) || classes.length === 0) {
                    container.innerHTML = '<div class="col-span-full text-center text-gray-500">Belum ada kelas tersedia.</div>';
                    return;
                }

                classes.forEach(cls => {
                    const btn = document.createElement('button');
                    const isSelected = cls.id == currentClassId;

                    btn.className = `p-3 rounded-lg border text-left transition-all duration-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-purple-500 
                        ${isSelected 
                            ? 'bg-purple-600 border-purple-600 text-white cursor-default ring-2 ring-purple-300' 
                            : 'bg-white border-gray-200 text-gray-700 hover:border-purple-300 hover:text-purple-700'}`;
                    
                    btn.innerHTML = `
                        <div class="font-semibold text-sm truncate">${cls.name}</div>
                        <div class="text-xs ${isSelected ? 'text-purple-100' : 'text-gray-400'} mt-1">
                            ${isSelected ? 'Terpilih' : 'Pilih Kelas'}
                        </div>
                    `;

                    if (!isSelected) {
                        btn.onclick = () => handleSelectClass(cls.id, cls.name);
                    }

                    container.appendChild(btn);
                });

            } catch (error) {
                console.error('Error fetching classes:', error);
                container.innerHTML = '<div class="col-span-full text-center text-red-500">Gagal mengambil data kelas.</div>';
            }
        }

        // --- AKSI PILIH KELAS ---
        async function handleSelectClass(classId, className) {
            if (!confirm(`Apakah Anda yakin ingin bergabung dengan kelas "${className}"?`)) return;

            const container = document.getElementById('classes-list-container');
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';

            try {
                await axios.post(`${API_BASE_URL}/student/select-class`, 
                    { class_id: classId },
                    { 
                        headers: { 
                            'Authorization': `Bearer ${token}`,
                            'ngrok-skip-browser-warning': 'true'
                        } 
                    }
                );

                showAlert(`Berhasil bergabung ke kelas ${className}!`, 'success');
                renderActiveClass({ id: classId, name: className });
                
                document.getElementById('select-class-section').classList.add('hidden');
                document.getElementById('btn-cancel-change').classList.add('hidden');

            } catch (error) {
                console.error('Select class error:', error);
                showAlert('Gagal memilih kelas. Silakan coba lagi.', 'error');
            } finally {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
        }

        // --- UTILS ---
        function renderActiveClass(classData) {
            document.getElementById('current-class-section').classList.remove('hidden');
            document.getElementById('current-class-name').innerText = classData.name;
            document.getElementById('instruction-text').classList.add('hidden');

            unlockQuickMenu('menu-materi', "{{ route('student.materials.index') }}");
            unlockQuickMenu('menu-kuis', "{{ route('student.quizzes.index') }}");
            unlockQuickMenu('menu-nilai', "{{ route('student.scores.index') }}");
        }

        function unlockQuickMenu(elementId, url) {
            const el = document.getElementById(elementId);
            el.href = url;
            el.classList.remove('opacity-60', 'cursor-not-allowed', 'bg-gray-50');
            el.classList.add('bg-white', 'hover:border-purple-400', 'shadow-sm', 'cursor-pointer');
            el.querySelector('.bg-white').classList.replace('text-gray-400', 'text-purple-600');
        }

        function toggleChangeClassMode() {
            const section = document.getElementById('select-class-section');
            const cancelBtn = document.getElementById('btn-cancel-change');
            
            if (section.classList.contains('hidden')) {
                section.classList.remove('hidden');
                cancelBtn.classList.remove('hidden');
                fetchAndRenderClassList(null); 
            } else {
                section.classList.add('hidden');
                cancelBtn.classList.add('hidden');
            }
        }

        function showAlert(message, type) {
            const box = document.getElementById('alert-container');
            const msgEl = document.getElementById('alert-message');
            msgEl.innerText = message;
            
            box.className = "px-4 py-3 rounded-lg border relative mb-5 transition-all duration-300 " + 
                            (type === 'error' ? 'bg-red-50 border-red-400 text-red-700' : 'bg-green-50 border-green-400 text-green-700');
            
            box.classList.remove('hidden');
            setTimeout(() => box.classList.add('hidden'), 4000);
        }

        function handleError(error) {
            if (error.response && error.response.status === 401) {
                localStorage.removeItem('auth_token');
                window.location.href = "{{ route('login') }}";
            } else {
                showAlert('Gagal terhubung ke server.', 'error');
            }
        }

        // --- Helper: Membersihkan Response dari PHP Warnings (CRITICAL) ---
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