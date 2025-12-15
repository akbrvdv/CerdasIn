<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CerdasIn') }}</title>

    {{-- Fonts & Icons --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-600">
    <div x-data="{ sidebarOpen: false }" class="flex h-full">
        
        {{-- SIDEBAR --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white border-r transform transition-transform duration-300 ease-in-out lg:translate-x-0">
            <div class="flex justify-center items-center px-6 py-4 border-b border-gray-100">
                <h1 class="text-2xl font-bold flex items-center space-x-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600"></i>
                    <span>CerdasIn</span>
                </h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-600 focus:outline-none text-lg ml-auto">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="mt-4 px-4 space-y-2 text-sm font-medium flex-1">
                {{-- Dashboard --}}
                <a href="{{ route('teacher.dashboard') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('teacher.dashboard') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-chart-line w-5"></i>
                    Dashboard
                </a>

                {{-- Kelola Kelas --}}
                <a href="{{ route('teacher.classes.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('teacher.classes.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-layer-group w-5"></i>
                    Kelas
                </a>

                {{-- Kelola Materi --}}
                <a href="{{ route('teacher.materials.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('teacher.materials.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-book-open w-5"></i>
                    Materi
                </a>

                {{-- Kelola Kuis --}}
                <a href="{{ route('teacher.quizzes.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('teacher.quizzes.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-puzzle-piece w-5"></i>
                    Kuis
                </a>
            </nav>
        </aside>

        {{-- MAIN CONTENT WRAPPER --}}
        <div class="flex-1 flex flex-col lg:ml-64">
            
            {{-- HEADER / NAVBAR --}}
            <header class="flex items-center justify-between p-4 bg-white border-b h-16">
                {{-- Hamburger Menu (Mobile) --}}
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>

                {{-- User Dropdown --}}
                <div class="flex items-center ml-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                                {{-- Avatar & Nama Diisi via Javascript --}}
                                <img id="nav-user-avatar" src="" class="w-9 h-9 rounded-full border border-purple-200 object-cover" alt="User Avatar">
                                <div class="hidden sm:block font-semibold" id="nav-user-name">Loading...</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            {{-- Profile Links --}}
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Edit Profil') }}
                            </x-dropdown-link>
                            
                            {{-- Logout --}}
                            <button id="btn-logout" class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                {{ __('Log Out') }}
                            </button>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            {{-- CONTENT AREA --}}
            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- GLOBAL SCRIPT: User Data & Logout Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Konfigurasi API Base URL sesuai yang Anda berikan
            const API_BASE_URL = "https://f3cac68007ee.ngrok-free.app/api";
            
            // Ambil data dari LocalStorage
            const userDataString = localStorage.getItem('user_data');
            const token = localStorage.getItem('auth_token');

            // 1. Cek Token Login
            if (!token) {
                 window.location.href = "{{ route('login') }}";
                 return;
            }

            // 2. Update Tampilan Nama & Avatar
            if (userDataString) {
                try {
                    const user = JSON.parse(userDataString);
                    
                    // Update Nama
                    const nameElement = document.getElementById('nav-user-name');
                    if (nameElement) nameElement.innerText = user.name;

                    // Update Avatar
                    const avatarElement = document.getElementById('nav-user-avatar');
                    if (avatarElement) {
                        // Jika user punya foto profil, gunakan path storage. Jika tidak, pakai UI Avatars.
                        // Kita asumsikan path dari database sudah relatif (misal: 'avatars/foto.jpg')
                        // Karena Anda akses via ngrok, path '/storage/...' akan otomatis mengarah ke domain ngrok.
                        const avatarUrl = user.photo_profile 
                            ? `/storage/${user.photo_profile}` 
                            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random&color=fff`;
                        
                        avatarElement.src = avatarUrl;
                    }
                } catch (e) {
                    console.error("Gagal memparsing data user", e);
                }
            }

            // 3. Handle Logout
            const btnLogout = document.getElementById('btn-logout');
            if (btnLogout) {
                btnLogout.addEventListener('click', async function() {
                    try {
                        // Panggil API Logout untuk menghapus token di server
                        await fetch(`${API_BASE_URL}/logout`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${token}`,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (error) {
                        console.error('Logout API error:', error);
                    } finally {
                        // Hapus data di client dan redirect ke login apapun yang terjadi
                        localStorage.removeItem('user_data');
                        localStorage.removeItem('auth_token');
                        window.location.href = "{{ route('login') }}";
                    }
                });
            }
        });
    </script>
</body>
</html>