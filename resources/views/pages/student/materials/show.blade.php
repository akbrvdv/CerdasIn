@extends('layouts.student')

@section('title', 'Detail Materi')

@section('content')
    <div class="max-w-6xl mx-auto">
        
        {{-- HEADER: Tombol Kembali --}}
        <div class="mb-6">
            <a href="{{ route('student.materials.index') }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-purple-700 transition-colors">
                <i class="fa-solid fa-arrow-left text-sm"></i>
                <span>Kembali</span>
            </a>
        </div>

        {{-- LOADING STATE --}}
        <div id="loading-indicator" class="bg-white rounded-xl border p-8 flex flex-col items-center justify-center min-h-[400px]">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mb-3"></div>
            <p class="text-gray-500">Sedang memuat materi...</p>
        </div>

        {{-- ERROR STATE --}}
        <div id="error-container" class="hidden bg-red-50 border border-red-200 text-red-700 p-6 rounded-xl text-center">
            <p class="font-bold mb-2">Terjadi Kesalahan</p>
            <p id="error-message">Gagal memuat materi.</p>
            <a href="{{ route('student.materials.index') }}" class="mt-4 inline-block text-red-600 hover:underline">Kembali ke Daftar</a>
        </div>

        {{-- CONTENT CONTAINER (Hidden by default) --}}
        <div id="content-container" class="hidden bg-white rounded-xl border overflow-hidden">
            
            {{-- Gambar Header --}}
            <img id="material-image" src="" alt="Thumbnail" class="w-full h-64 md:h-96 object-cover bg-gray-100">

            <div class="p-6 md:p-8">

                {{-- Label Kelas --}}
                <div class="mb-4">
                    <span id="classroom-badge" class="text-xs font-semibold uppercase text-purple-600 bg-purple-100 px-3 py-1 rounded-full">
                        Loading...
                    </span>
                </div>

                {{-- Judul --}}
                <h1 id="material-title" class="text-3xl font-bold text-gray-900 mb-3">
                    ...
                </h1>

                {{-- Tanggal --}}
                <p class="text-sm text-gray-500 mb-6 flex items-center">
                    <i class="fa-regular fa-calendar-alt mr-1.5"></i>
                    Dibuat pada: <span id="material-date">...</span>
                </p>

                {{-- Isi Materi (HTML) --}}
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Deskripsi/Isi Materi:</h3>
                <div id="material-content" class="prose prose-lg max-w-none text-gray-700 mb-8">
                    </div>

                <hr class="my-8">

                {{-- Bagian Lampiran --}}
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Lampiran</h3>
                
                {{-- Container Lampiran (Ada File) --}}
                <div id="attachment-section" class="hidden">
                    <a id="attachment-link-text" href="#" target="_blank"
                        class="inline-flex items-center gap-2 mb-4 text-purple-700 font-medium hover:underline">
                        <i class="fa-solid fa-paperclip text-xs"></i>
                        <span id="attachment-filename">file.pdf</span>
                    </a>

                    <div class="flex flex-col sm:flex-row gap-3 max-w-xs">
                        <a id="btn-view" href="#" target="_blank"
                            class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-purple-600 text-white hover:bg-purple-700 transition-all duration-200">
                            <i class="fa-solid fa-eye text-xs"></i>
                            <span>Lihat</span>
                        </a>

                        <a id="btn-download" href="#" download
                            class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-purple-50 text-purple-700 border border-purple-200 hover:bg-purple-100 transition-all duration-200">
                            <i class="fa-solid fa-download text-xs"></i>
                            <span>Unduh</span>
                        </a>
                    </div>
                </div>

                {{-- Container Lampiran (Tidak Ada File) --}}
                <div id="no-attachment-section" class="hidden">
                    <p class="text-gray-500 italic">Tidak ada lampiran.</p>
                </div>

            </div>
        </div>
    </div>

    {{-- JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // 1. Setup Config & Token
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // 2. Ambil ID Materi dari URL
            // URL browser: /student/materials/{id}
            const pathSegments = window.location.pathname.split('/');
            const materialId = pathSegments[pathSegments.length - 1]; // Ambil segmen terakhir

            if (!materialId) {
                showError("ID Materi tidak ditemukan.");
                return;
            }

            try {
                // 3. Request ke API Detail Materi
                const response = await axios.get(`${API_URL}/student/materials/${materialId}`, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true' // Jaga-jaga jika pakai ngrok
                    }
                });

                const data = response.data.data || response.data; // Handle wrapper

                renderMaterial(data, BASE_URL);

            } catch (error) {
                console.error('Error loading material:', error);
                
                if (error.response && error.response.status === 404) {
                    showError("Materi tidak ditemukan atau telah dihapus.");
                } else if (error.response && error.response.status === 401) {
                    localStorage.removeItem('auth_token');
                    window.location.href = "{{ route('login') }}";
                } else {
                    showError("Gagal mengambil data materi. Silakan coba lagi.");
                }
            }
        });

        function renderMaterial(material, baseUrl) {
            // Sembunyikan Loading
            document.getElementById('loading-indicator').classList.add('hidden');
            document.getElementById('content-container').classList.remove('hidden');

            // 1. Gambar Header
            const imgEl = document.getElementById('material-image');
            if (material.thumbnail) {
                const cleanPath = material.thumbnail.replace(/^\//, '');
                imgEl.src = `${baseUrl}/storage/${cleanPath}`;
            } else {
                imgEl.src = `${baseUrl}/storage/thumbnails/default.png`; // Fallback image
            }
            imgEl.onerror = function() { this.src = 'https://placehold.co/800x400?text=No+Image'; };

            // 2. Teks Utama
            document.getElementById('material-title').innerText = material.title;
            document.getElementById('material-content').innerHTML = material.material; // Render HTML content

            // 3. Nama Kelas (Jika API mengirim relasi classroom)
            const badgeEl = document.getElementById('classroom-badge');
            if (material.classroom && material.classroom.name) {
                badgeEl.innerText = material.classroom.name;
            } else {
                // Jika relasi tidak ada di API response, sembunyikan atau beri default
                badgeEl.classList.add('hidden');
            }

            // 4. Tanggal
            const dateObj = new Date(material.created_at);
            const dateStr = dateObj.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            document.getElementById('material-date').innerText = dateStr;

            // 5. Logic File Lampiran
            const attachmentSection = document.getElementById('attachment-section');
            const noAttachmentSection = document.getElementById('no-attachment-section');

            if (material.file_path) {
                attachmentSection.classList.remove('hidden');
                
                const cleanFilePath = material.file_path.replace(/^\//, '');
                const fileUrl = `${baseUrl}/storage/${cleanFilePath}`;
                const fileName = cleanFilePath.split('/').pop(); // Ambil nama file saja

                // Update Links
                document.getElementById('attachment-filename').innerText = fileName;
                
                const linkText = document.getElementById('attachment-link-text');
                linkText.href = fileUrl;

                const btnView = document.getElementById('btn-view');
                btnView.href = fileUrl;

                const btnDownload = document.getElementById('btn-download');
                btnDownload.href = fileUrl;
            } else {
                noAttachmentSection.classList.remove('hidden');
            }
        }

        function showError(msg) {
            document.getElementById('loading-indicator').classList.add('hidden');
            document.getElementById('error-container').classList.remove('hidden');
            document.getElementById('error-message').innerText = msg;
        }
    </script>
@endsection