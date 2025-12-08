<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CerdasIn') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-sans antialiased text-slate-600">
    <div x-data="{ sidebarOpen: false }" class="flex h-full">
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
    <a href="/teacher/dashboard"
        class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->is('teacher/dashboard') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
        <i class="fa-solid fa-chart-line w-5"></i>
        Dashboard
    </a>

    {{-- Kelola Kelas --}}
    <a href="/teacher/classes"
        class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->is('teacher/classes*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
        <i class="fa-solid fa-layer-group w-5"></i>
        Kelas
    </a>

    {{-- Kelola Materi --}}
    <a href="/teacher/materials"
        class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->is('teacher/materials*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
        <i class="fa-solid fa-book-open w-5"></i>
        Materi
    </a>

    {{-- Kelola Kuis --}}
    <a href="/teacher/quizzes"
        class="flex items-center gap-3 py-2.5 px-4 rounded-lg transition {{ request()->is('teacher/quizzes*') ? 'bg-purple-100 text-purple-700 font-semibold' : 'hover:bg-gray-100' }}">
        <i class="fa-solid fa-puzzle-piece w-5"></i>
        Kuis
    </a>

</nav>
        </aside>

        <div class="flex-1 flex flex-col lg:ml-64">
            <header class="flex items-center justify-between p-4 bg-white border-b h-16">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 lg:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"></path>
                    </svg>
                </button>
                
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>