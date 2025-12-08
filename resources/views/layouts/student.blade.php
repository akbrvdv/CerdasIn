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
            
            {{-- Logo --}}
            <div class="flex justify-center items-center px-6 py-4 border-b border-gray-100">
                <h1 class="text-2xl font-bold flex items-center space-x-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600"></i>
                    <span>CerdasIn</span>
                </h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-600 focus:outline-none text-lg ml-auto">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Navigasi Sidebar --}}
            <nav class="mt-4 px-4 space-y-2 text-sm font-medium flex-1">
                {{-- PERBAIKAN: Nama Route diperbaiki jadi 'student.dashboard' --}}
                <a href="{{ route('student.dashboard') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('student.dashboard') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-chart-line w-5"></i>
                    Dashboard
                </a>
                <a href="{{ route('student.materials.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('student.materials.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-book-open w-5"></i>
                    Materi
                </a>
                <a href="{{ route('student.quizzes.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('student.quizzes.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-puzzle-piece w-5"></i>
                    Kuis
                </a>
                <a href="{{ route('student.scores.index') }}"
                    class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->routeIs('student.scores.*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
                    <i class="fa-solid fa-chart-column w-5"></i>
                    Nilai
                </a>
            </nav>
        </aside>

        {{-- MAIN CONTENT WRAPPER --}}
        <div class="flex-1 flex flex-col lg:ml-64">
            
            {{-- HEADER / NAVBAR --}}
            <header class="flex items-center justify-between p-4 bg-white border-b h-16">
                {{-- Hamburger Menu (Mobile) --}}
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>

                {{-- User Dropdown --}}
                <div class="flex items-center ml-auto">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                                {{-- Avatar & Nama Diisi JS --}}
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
                            {{-- Profile Links (Pastikan route ini ada di web.php) --}}
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Edit Profil') }}
                            </x-dropdown-link>
                            
                            {{-- Logout (Trigger JS) --}}
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

    {{-- GLOBAL SCRIPT: User Data & Logout --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Ambil Data User dari LocalStorage
            const userDataString = localStorage.getItem('user_data');
            const token = localStorage.getItem('auth_token');
            const API_BASE_URL = "{{ env('API_BASE_URL', 'http://localhost:8000') }}".replace(/\/$/, '') + '/api';

            // Jika tidak ada data login, redirect (opsional, bisa juga di-handle per halaman)
            if (!token) {
                 window.location.href = "{{ route('login') }}";
                 return;
            }

            // Update UI Nama & Avatar
            if (userDataString) {
                const user = JSON.parse(userDataString);
                
                // Set Nama
                document.getElementById('nav-user-name').innerText = user.name;
                
                // Set Avatar (UI Avatars Generator)
                const