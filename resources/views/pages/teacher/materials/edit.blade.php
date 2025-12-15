@extends('layouts.teacher')

@section('title', 'Edit Materi')

@section('content')
    <div class="max-w-5xl bg-white border rounded-xl p-6">
        <h2 class="text-xl font-semibold mb-4 text-purple-700">Edit Materi</h2>

        {{-- Loading Indicator --}}
        <div id="loading-indicator" class="flex flex-col items-center justify-center py-10">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-purple-600 mb-3"></div>
            <p class="text-gray-500">Memuat data materi...</p>
        </div>

        {{-- Form Container (Hidden saat loading) --}}
        <form id="edit-material-form" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Judul Materi</label>
                <input type="text" name="title" id="title"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400"
                    required>
                <p id="error-title" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div class="col-span-2">
                <label for="material" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Materi</label>
                <textarea name="material" id="material" rows="5"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400"
                    placeholder="Tuliskan isi atau ringkasan materi..."></textarea>
                <p id="error-material" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-1">Thumbnail</label>
                
                {{-- Preview Thumbnail Lama --}}
                <div id="current-thumbnail-container" class="mb-2 hidden">
                    <img id="current-thumbnail" src="" alt="Thumbnail Saat Ini"
                        class="w-32 h-32 object-cover rounded-lg border shadow-sm">
                    <p class="text-xs text-gray-500 mt-1">Thumbnail saat ini</p>
                </div>

                <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengganti. Format: JPG, PNG, JPEG.</p>
                <p id="error-thumbnail" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="classroom_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Pilih Kelas
                </label>
                <select name="classroom_id" id="classroom_id"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                    <option value="">-- Memuat Kelas --</option>
                    {{-- Option diisi via JS --}}
                </select>
                <p id="error-classroom_id" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div>
                <label for="file_path" class="block text-sm font-medium text-gray-700 mb-1">File Materi</label>
                
                {{-- Preview File Lama --}}
                <div id="current-file-container" class="mb-2 hidden">
                    <a id="current-file-link" href="#" target="_blank"
                        class="inline-flex items-center text-blue-600 hover:underline bg-blue-50 px-3 py-2 rounded-lg text-sm">
                        <i class="fa-solid fa-file mr-2"></i> 
                        <span>Lihat File Saat Ini</span>
                    </a>
                </div>

                <input type="file" name="file_path" id="file_path"
                    class="w-full border border-gray-300 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-purple-400">
                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengganti. Format: PDF, DOCX, PPTX.</p>
                <p id="error-file_path" class="text-red-500 text-sm mt-1 hidden"></p>
            </div>

            <div class="col-span-2 flex justify-end gap-3 mt-6">
                <a href="{{ route('teacher.materials.index') }}"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>

                <button type="submit" id="btn-submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    <i class="fa-solid fa-check mr-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- SCRIPT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // 1. Config
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // Ambil ID Materi dari URL Browser (teacher/materials/{id}/edit)
            const pathSegments = window.location.pathname.split('/');
            // Filter segmen kosong dan ambil segmen sebelum 'edit'
            const cleanSegments = pathSegments.filter(seg => seg !== '');
            const materialId = cleanSegments[cleanSegments.length - 2]; // Ambil ID sebelum 'edit'

            if (!materialId) {
                alert("ID Materi tidak ditemukan.");
                window.location.href = "{{ route('teacher.materials.index') }}";
                return;
            }

            const headers = { 
                'Authorization': `Bearer ${token}`,
                'ngrok-skip-browser-warning': 'true'
                // Jangan set Content-Type: application/json secara manual saat upload file
            };

            // 2. Fetch Data (Materi & List Kelas)
            try {
                const [materialResRaw, classesResRaw] = await Promise.all([
                    axios.get(`${API_URL}/teacher/materials/${materialId}`, { headers }),
                    axios.get(`${API_URL}/teacher/classrooms`, { headers })
                ]);

                const material = parseResponse(materialResRaw.data).data || parseResponse(materialResRaw.data);
                const classes = parseResponse(classesResRaw.data).data || parseResponse(classesResRaw.data);

                populateForm(material, classes);

                document.getElementById('loading-indicator').classList.add('hidden');
                document.getElementById('edit-material-form').classList.remove('hidden');

            } catch (error) {
                console.error("Error fetching data:", error);
                alert("Gagal memuat data materi.");
                window.location.href = "{{ route('teacher.materials.index') }}";
            }

            // 3. Populate Form Function
            function populateForm(material, classes) {
                // Isi Dropdown Kelas
                const classSelect = document.getElementById('classroom_id');
                classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';
                classes.forEach(c => {
                    const isSelected = c.id == material.classroom_id ? 'selected' : '';
                    classSelect.innerHTML += `<option value="${c.id}" ${isSelected}>${c.name}</option>`;
                });

                // Isi Input Text
                document.getElementById('title').value = material.title;
                document.getElementById('material').value = material.material; // Deskripsi

                // Tampilkan Thumbnail Lama jika ada
                if (material.thumbnail) {
                    const cleanPath = material.thumbnail.replace(/^\//, '');
                    document.getElementById('current-thumbnail').src = `${BASE_URL}/storage/${cleanPath}`;
                    document.getElementById('current-thumbnail-container').classList.remove('hidden');
                }

                // Tampilkan Link File Lama jika ada
                if (material.file_path) {
                    const cleanPath = material.file_path.replace(/^\//, '');
                    document.getElementById('current-file-link').href = `${BASE_URL}/storage/${cleanPath}`;
                    document.getElementById('current-file-container').classList.remove('hidden');
                }
            }

            // 4. Handle Submit (Update)
            const form = document.getElementById('edit-material-form');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Reset Error
                document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
                const btnSubmit = document.getElementById('btn-submit');
                const originalText = btnSubmit.innerHTML;
                btnSubmit.innerText = 'Menyimpan...';
                btnSubmit.disabled = true;

                try {
                    // Gunakan FormData untuk support file upload
                    const formData = new FormData(this);
                    
                    // PENTING: Laravel tidak bisa membaca file di method PUT standard.
                    // Triknya: Kirim POST dengan _method = 'PUT'
                    formData.append('_method', 'PUT');

                    await axios.post(`${API_URL}/teacher/materials/${materialId}`, formData, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Content-Type': 'multipart/form-data', // Wajib untuk upload file
                            'ngrok-skip-browser-warning': 'true'
                        }
                    });

                    alert('Materi berhasil diperbarui!');
                    window.location.href = "{{ route('teacher.materials.index') }}";

                } catch (error) {
                    console.error('Update Error:', error);
                    btnSubmit.innerHTML = originalText;
                    btnSubmit.disabled = false;

                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        for (const key in errors) {
                            const errorEl = document.getElementById(`error-${key}`);
                            if (errorEl) {
                                errorEl.innerText = errors[key][0];
                                errorEl.classList.remove('hidden');
                            }
                        }
                    } else {
                        alert('Gagal memperbarui materi. Silakan coba lagi.');
                    }
                }
            });

            // Helper Parse PHP Warnings
            function parseResponse(rawData) {
                if (typeof rawData === 'string') {
                    const jsonStartIndex = rawData.indexOf('{');
                    const jsonEndIndex = rawData.lastIndexOf('}') + 1;
                    if (jsonStartIndex !== -1) {
                        try { return JSON.parse(rawData.substring(jsonStartIndex, jsonEndIndex)); } 
                        catch (e) { return rawData; }
                    }
                }
                return rawData;
            }
        });
    </script>
@endsection