@extends('layouts.teacher')

@section('title', 'Buat Kuis Baru')

@section('content')
<div class="max-w-4xl mx-auto bg-white border rounded-xl p-8 shadow-sm">
    
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('teacher.quizzes.index') }}" class="text-gray-500 hover:text-purple-600 transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Buat Kuis Baru</h1>
    </div>

    {{-- Error Alert --}}
    <div id="alert-error" class="hidden mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
        <p id="error-message">Terjadi kesalahan.</p>
    </div>

    <form id="create-quiz-form" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Judul --}}
            <div class="md:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Kuis <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" 
                    class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Contoh: Ulangan Harian Matematika Bab 1" required>
                <p id="err-title" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            {{-- Kelas --}}
            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas <span class="text-red-500">*</span></label>
                <select id="classroom_id" name="classroom_id" 
                    class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500" required>
                    <option value="">-- Memuat Kelas... --</option>
                </select>
                <p id="err-classroom_id" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            {{-- Durasi --}}
            <div>
                <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Durasi (Menit) <span class="text-red-500">*</span></label>
                <input type="number" id="duration" name="duration" min="1"
                    class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Contoh: 60" required>
                <p id="err-duration" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Petunjuk</label>
                <textarea id="description" name="description" rows="4" 
                    class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500"
                    placeholder="Tuliskan petunjuk pengerjaan kuis..."></textarea>
                <p id="err-description" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('teacher.quizzes.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                Batal
            </a>
            <button type="submit" id="btn-submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium flex items-center shadow-lg shadow-purple-200">
                <span id="btn-text">Simpan & Lanjut Buat Soal</span>
                <i id="btn-loader" class="fa-solid fa-circle-notch fa-spin ml-2 hidden"></i>
            </button>
        </div>

    </form>
</div>

{{-- JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        // 1. Config
        const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
        const API_URL = ENV_URL.replace(/\/$/, '') + '/api';
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

        // 2. Fetch Data Kelas (Untuk Dropdown)
        const classSelect = document.getElementById('classroom_id');
        try {
            const response = await axios.get(`${API_URL}/teacher/classrooms`, { headers });
            
            // Bersihkan Data
            let rawData = response.data;
            if (typeof rawData === 'string' && rawData.includes('{')) {
                 rawData = JSON.parse(rawData.substring(rawData.indexOf('{')));
            }
            const classes = Array.isArray(rawData) ? rawData : (rawData.data || []);

            classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            
            if(classes.length > 0) {
                classes.forEach(cls => {
                    classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                });
            } else {
                classSelect.innerHTML = '<option value="">Belum ada kelas (Buat dulu)</option>';
            }

        } catch (error) {
            console.error("Error loading classes:", error);
            classSelect.innerHTML = '<option value="">Gagal memuat kelas</option>';
        }

        // 3. Handle Submit
        const form = document.getElementById('create-quiz-form');
        const btnSubmit = document.getElementById('btn-submit');
        const btnText = document.getElementById('btn-text');
        const btnLoader = document.getElementById('btn-loader');
        const alertError = document.getElementById('alert-error');
        const errorMsg = document.getElementById('error-message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Reset UI
            alertError.classList.add('hidden');
            document.querySelectorAll('[id^="err-"]').forEach(el => el.classList.add('hidden'));
            
            btnSubmit.disabled = true;
            btnText.innerText = 'Menyimpan...';
            btnLoader.classList.remove('hidden');

            // Ambil Data
            const formData = {
                title: document.getElementById('title').value,
                classroom_id: document.getElementById('classroom_id').value,
                duration: document.getElementById('duration').value,
                description: document.getElementById('description').value,
            };

            try {
                // POST ke API
                const response = await axios.post(`${API_URL}/teacher/quizzes`, formData, { headers });

                // Sukses
                alert('Kuis berhasil dibuat! Silakan tambahkan soal.');
                
                // Ambil ID Kuis yang baru dibuat untuk redirect ke halaman soal
                // Backend biasanya return: { success: true, data: { id: 1, ... } }
                const responseData = response.data.data || response.data;
                const newQuizId = responseData.id;

                if (newQuizId) {
                    // Redirect ke halaman kelola soal
                    window.location.href = `/teacher/quizzes/${newQuizId}/questions`;
                } else {
                    // Fallback jika ID tidak ada di response
                    window.location.href = "{{ route('teacher.quizzes.index') }}";
                }

            } catch (error) {
                console.error('Create Quiz Error:', error);
                btnSubmit.disabled = false;
                btnText.innerText = 'Simpan & Lanjut Buat Soal';
                btnLoader.classList.add('hidden');

                if (error.response) {
                    if (error.response.status === 422) {
                        // Validasi
                        const errors = error.response.data.errors;
                        for (const [key, value] of Object.entries(errors)) {
                            const errEl = document.getElementById(`err-${key}`);
                            if (errEl) {
                                errEl.innerText = value[0];
                                errEl.classList.remove('hidden');
                            }
                        }
                        errorMsg.innerText = "Mohon lengkapi data yang wajib diisi.";
                    } else {
                        // Error Server
                        errorMsg.innerText = error.response.data.message || "Gagal membuat kuis.";
                    }
                } else {
                    errorMsg.innerText = "Tidak dapat terhubung ke server.";
                }
                alertError.classList.remove('hidden');
            }
        });
    });
</script>
@endsection