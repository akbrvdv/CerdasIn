@extends('layouts.teacher')

@section('title', 'Edit Kuis')

@section('content')
<div class="max-w-lg bg-white border rounded-xl p-6 mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('teacher.quizzes.index') }}" class="text-gray-500 hover:text-purple-600 transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h2 class="text-xl font-semibold text-purple-700">Edit Kuis</h2>
    </div>

    {{-- Error Alert --}}
    <div id="alert-error" class="hidden mb-6 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
        <p id="error-message">Terjadi kesalahan.</p>
    </div>

    {{-- Loading State --}}
    <div id="page-loader" class="text-center py-10">
        <i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-3xl"></i>
        <p class="text-gray-500 mt-2">Mengambil data kuis...</p>
    </div>

    <form id="edit-quiz-form" class="hidden space-y-6">
        
        {{-- Judul --}}
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Kuis <span class="text-red-500">*</span></label>
            <input type="text" id="title" name="title" 
                class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500"
                required>
            <p id="err-title" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        {{-- Deskripsi --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea id="description" name="description" rows="4" 
                class="w-full border-gray-300 rounded-lg p-2.5 focus:ring-purple-500 focus:border-purple-500"></textarea>
            <p id="err-description" class="text-red-500 text-sm mt-1 hidden"></p>
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
                required>
            <p id="err-duration" class="text-red-500 text-sm mt-1 hidden"></p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('teacher.quizzes.index') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                Batal
            </a>
            <button type="submit" id="btn-submit" class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium flex items-center shadow-lg shadow-purple-200">
                <span id="btn-text">Simpan Perubahan</span>
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

        // Ambil ID dari URL (teacher/quizzes/{id}/edit)
        const pathSegments = window.location.pathname.split('/');
        const editIndex = pathSegments.indexOf('edit');
        const quizId = pathSegments[editIndex - 1];

        if (!quizId) {
            alert('ID Kuis tidak ditemukan.');
            window.location.href = "{{ route('teacher.quizzes.index') }}";
            return;
        }

        const headers = { 
            'Authorization': `Bearer ${token}`,
            'ngrok-skip-browser-warning': 'true',
            'Content-Type': 'application/json'
        };

        // 2. Fetch Data Awal (Kelas & Detail Kuis)
        try {
            const [classRes, quizRes] = await Promise.all([
                axios.get(`${API_URL}/teacher/classrooms`, { headers }),
                axios.get(`${API_URL}/teacher/quizzes/${quizId}`, { headers })
            ]);

            // Parse Data
            const classes = parseResponse(classRes.data);
            const quizData = parseResponse(quizRes.data);
            const quiz = quizData.data || quizData; // Handle wrapper

            // Isi Dropdown Kelas
            const classSelect = document.getElementById('classroom_id');
            classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            
            const classList = Array.isArray(classes) ? classes : (classes.data || []);
            classList.forEach(cls => {
                classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
            });

            // Isi Form dengan Data Lama
            document.getElementById('title').value = quiz.title;
            document.getElementById('description').value = quiz.description || '';
            document.getElementById('duration').value = quiz.duration;
            document.getElementById('classroom_id').value = quiz.classroom_id;

            // Tampilkan Form
            document.getElementById('page-loader').classList.add('hidden');
            document.getElementById('edit-quiz-form').classList.remove('hidden');

        } catch (error) {
            console.error("Fetch Error:", error);
            alert("Gagal memuat data kuis.");
            window.location.href = "{{ route('teacher.quizzes.index') }}";
        }

        // 3. Handle Update (PUT)
        const form = document.getElementById('edit-quiz-form');
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

            const formData = {
                title: document.getElementById('title').value,
                description: document.getElementById('description').value,
                classroom_id: document.getElementById('classroom_id').value,
                duration: document.getElementById('duration').value,
            };

            try {
                await axios.put(`${API_URL}/teacher/quizzes/${quizId}`, formData, { headers });

                alert('Kuis berhasil diperbarui!');
                window.location.href = "{{ route('teacher.quizzes.index') }}";

            } catch (error) {
                console.error('Update Error:', error);
                btnSubmit.disabled = false;
                btnText.innerText = 'Simpan Perubahan';
                btnLoader.classList.add('hidden');

                if (error.response) {
                    if (error.response.status === 422) {
                        const errors = error.response.data.errors;
                        for (const [key, value] of Object.entries(errors)) {
                            const errEl = document.getElementById(`err-${key}`);
                            if (errEl) {
                                errEl.innerText = value[0];
                                errEl.classList.remove('hidden');
                            }
                        }
                        errorMsg.innerText = "Periksa inputan Anda.";
                    } else {
                        errorMsg.innerText = error.response.data.message || "Gagal memperbarui kuis.";
                    }
                } else {
                    errorMsg.innerText = "Tidak dapat terhubung ke server.";
                }
                alertError.classList.remove('hidden');
            }
        });

        // Helper JSON Parse
        function parseResponse(rawData) {
            if (typeof rawData === 'string') {
                const jsonStartIndex = rawData.indexOf('{');
                if (jsonStartIndex !== -1) {
                    try {
                        return JSON.parse(rawData.substring(jsonStartIndex));
                    } catch (e) { return rawData; }
                }
            }
            return rawData;
        }
    });
</script>
@endsection