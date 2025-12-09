@extends('layouts.teacher')

@section('title', 'Tambah Materi')

@section('content')
<div class="max-w-5xl bg-white border rounded-xl p-6 mx-auto">
    <h2 class="text-xl font-semibold mb-4 text-purple-700">Tambah Materi Baru</h2>

    {{-- Error Alert Global --}}
    <div id="alert-error" class="hidden mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
        <p id="error-message">Terjadi kesalahan saat menyimpan data.</p>
    </div>

    <form id="create-material-form" class="space-y-6">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Judul Materi <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400"
                    placeholder="Contoh: Materi Bangun Datar" required>
                <p id="err-title" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Pilih Kelas <span class="text-red-500">*</span>
                </label>
                <select id="classroom_id" name="classroom_id"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400" required>
                    <option value="">-- Memuat Kelas... --</option>
                </select>
                <p id="err-classroom_id" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-1">
                    Thumbnail (Gambar)
                </label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, JPEG. Max: 2MB.</p>
                <p id="err-thumbnail" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="file_path" class="block text-sm font-medium text-gray-700 mb-1">
                    Upload File (PDF/Doc)
                </label>
                <input type="file" id="file_path" name="file_path"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOCX. Max: 10MB.</p>
                <p id="err-file_path" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div class="md:col-span-2">
                <label for="material" class="block text-sm font-medium text-gray-700 mb-1">
                    Isi / Deskripsi Materi <span class="text-red-500">*</span>
                </label>
                {{-- Bisa diganti CKEditor/Summernote jika mau --}}
                <textarea id="material" name="material" rows="6"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400"
                    placeholder="Tuliskan isi materi atau deskripsi singkat di sini..."></textarea>
                <p id="err-material" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
            <a href="{{ route('teacher.materials.index') }}"
                class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                Batal
            </a>

            <button type="submit" id="btn-submit"
                class="px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium shadow-lg shadow-purple-200 flex items-center">
                <span id="btn-text">Simpan Materi</span>
                <i id="btn-loader" class="fa-solid fa-circle-notch fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </form>
</div>

{{-- JAVASCRIPT LOGIC --}}
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
            'ngrok-skip-browser-warning': 'true'
            // Jangan set Content-Type manual saat upload file, biarkan axios yang handle (multipart/form-data)
        };

        // 2. Fetch Data Kelas (Untuk Dropdown)
        const classSelect = document.getElementById('classroom_id');
        try {
            const response = await axios.get(`${API_URL}/teacher/classrooms`, { headers });
            
            // Handle Data Structure
            let rawData = response.data;
            if(typeof rawData === 'string' && rawData.includes('{')) {
                 rawData = JSON.parse(rawData.substring(rawData.indexOf('{')));
            }
            const classes = Array.isArray(rawData) ? rawData : (rawData.data || []);

            // Populate Dropdown
            classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
            if(classes.length > 0) {
                classes.forEach(cls => {
                    classSelect.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                });
            } else {
                classSelect.innerHTML = '<option value="">Belum ada kelas (Buat dulu)</option>';
            }

        } catch (error) {
            console.error("Error fetching classes:", error);
            classSelect.innerHTML = '<option value="">Gagal memuat kelas</option>';
        }

        // 3. Handle Submit
        const form = document.getElementById('create-material-form');
        const btnSubmit = document.getElementById('btn-submit');
        const btnText = document.getElementById('btn-text');
        const btnLoader = document.getElementById('btn-loader');
        const alertError = document.getElementById('alert-error');
        const errorMsg = document.getElementById('error-message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Reset Error UI
            alertError.classList.add('hidden');
            document.querySelectorAll('[id^="err-"]').forEach(el => el.classList.add('hidden'));
            
            // Loading State
            btnSubmit.disabled = true;
            btnText.innerText = 'Menyimpan...';
            btnLoader.classList.remove('hidden');

            try {
                // Gunakan FormData untuk upload file
                const formData = new FormData(form);

                // Kirim ke API
                await axios.post(`${API_URL}/teacher/materials`, formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true',
                        'Content-Type': 'multipart/form-data' // Penting untuk file upload
                    }
                });

                // Sukses
                alert('Materi berhasil ditambahkan!');
                window.location.href = "{{ route('teacher.materials.index') }}";

            } catch (error) {
                console.error('Submit Error:', error);
                btnSubmit.disabled = false;
                btnText.innerText = 'Simpan Materi';
                btnLoader.classList.add('hidden');

                if (error.response) {
                    if (error.response.status === 422) {
                        // Error Validasi
                        const errors = error.response.data.errors;
                        for (const [key, value] of Object.entries(errors)) {
                            const errEl = document.getElementById(`err-${key}`);
                            if (errEl) {
                                errEl.innerText = value[0];
                                errEl.classList.remove('hidden');
                            }
                        }
                        errorMsg.innerText = "Mohon periksa inputan Anda.";
                    } else {
                        // Error Server
                        errorMsg.innerText = error.response.data.message || "Gagal menyimpan materi.";
                    }
                } else {
                    errorMsg.innerText = "Tidak dapat terhubung ke server.";
                }
                
                alertError.classList.remove('hidden');
                // Scroll ke atas agar error terlihat
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
</script>
@endsection