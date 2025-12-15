@extends('layouts.teacher')

@section('title', 'Edit Kelas')

@section('content')
    <div class="max-w-2xl mx-auto">
        
        {{-- Header & Tombol Kembali --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.classes.index') }}" 
               class="text-gray-500 hover:text-purple-600 transition">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-purple-700">Edit Kelas</h1>
        </div>

        {{-- Loading State (Muncul saat mengambil data awal) --}}
        <div id="page-loader" class="text-center py-10 bg-white border rounded-xl">
            <i class="fa-solid fa-circle-notch fa-spin text-purple-600 text-3xl"></i>
            <p class="text-gray-500 mt-3">Mengambil data kelas...</p>
        </div>

        {{-- Card Form (Hidden saat loading) --}}
        <div id="form-container" class="hidden bg-white border rounded-xl p-6 shadow-sm">
            
            {{-- Alert Error Global --}}
            <div id="alert-error" class="hidden mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                <p id="error-message">Terjadi kesalahan.</p>
            </div>

            <form id="edit-class-form">
                
                {{-- Input Nama Kelas --}}
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm"
                           placeholder="Contoh: X RPL 1"
                           required>
                    {{-- Error text khusus field --}}
                    <p id="error-name" class="hidden mt-1 text-sm text-red-600"></p>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('teacher.classes.index') }}" 
                       class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" id="btn-submit"
                            class="px-5 py-2.5 rounded-lg bg-purple-600 text-white font-medium hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 transition shadow-lg shadow-purple-200">
                        <span id="btn-text">Simpan Perubahan</span>
                        <i id="btn-loader" class="fa-solid fa-circle-notch fa-spin hidden ml-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // 1. Config
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // 2. Ambil ID Kelas dari URL Browser
            const pathSegments = window.location.pathname.split('/');
            const editIndex = pathSegments.indexOf('edit');
            // Jika URL: /teacher/classes/5/edit -> ID ada sebelum 'edit'
            const classId = (editIndex !== -1) ? pathSegments[editIndex - 1] : pathSegments[pathSegments.length - 1];

            if (!classId || classId === 'edit') {
                alert('ID Kelas tidak valid.');
                window.location.href = "{{ route('teacher.classes.index') }}";
                return;
            }

            // Elements
            const pageLoader = document.getElementById('page-loader');
            const formContainer = document.getElementById('form-container');
            const nameInput = document.getElementById('name');
            
            // 3. FETCH DATA AWAL (GET)
            try {
                const response = await axios.get(`${API_URL}/teacher/classrooms/${classId}`, {
                    headers: { 
                        'Authorization': `Bearer ${token}`,
                        'ngrok-skip-browser-warning': 'true'
                    }
                });

                // Populate Form (Handle JSON Wrapper 'data' atau Raw Array)
                let rawData = response.data;
                
                // Pembersih JSON (Jika ada warning PHP)
                if (typeof rawData === 'string' && rawData.includes('{')) {
                    try {
                        rawData = JSON.parse(rawData.substring(rawData.indexOf('{')));
                    } catch (e) {}
                }

                const classData = rawData.data || rawData;
                
                if(classData && classData.name) {
                    nameInput.value = classData.name;
                    // Tampilkan Form
                    pageLoader.classList.add('hidden');
                    formContainer.classList.remove('hidden');
                } else {
                    throw new Error("Data kelas kosong/tidak ditemukan.");
                }

            } catch (error) {
                console.error('Fetch Error:', error);
                let msg = 'Gagal mengambil data kelas.';
                if(error.response && error.response.status === 404) msg = 'Kelas tidak ditemukan.';
                
                alert(msg);
                window.location.href = "{{ route('teacher.classes.index') }}";
                return;
            }

            // 4. HANDLE UPDATE (PUT)
            const form = document.getElementById('edit-class-form');
            const btnSubmit = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-text');
            const btnLoader = document.getElementById('btn-loader');
            
            const alertError = document.getElementById('alert-error');
            const msgError = document.getElementById('error-message');
            const errorName = document.getElementById('error-name');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();

                // Reset Error UI
                alertError.classList.add('hidden');
                errorName.classList.add('hidden');
                
                // Loading State
                btnSubmit.disabled = true;
                btnText.innerText = 'Menyimpan...';
                btnLoader.classList.remove('hidden');

                const name = nameInput.value;

                try {
                    // Endpoint: PUT /api/teacher/classrooms/{id}
                    await axios.put(`${API_URL}/teacher/classrooms/${classId}`, 
                        { name: name },
                        {
                            headers: { 
                                'Authorization': `Bearer ${token}`,
                                'ngrok-skip-browser-warning': 'true',
                                'Content-Type': 'application/json'
                            }
                        }
                    );

                    alert('Kelas berhasil diperbarui!');
                    window.location.href = "{{ route('teacher.classes.index') }}";

                } catch (error) {
                    console.error('Update Error:', error);
                    btnSubmit.disabled = false;
                    btnText.innerText = 'Simpan Perubahan';
                    btnLoader.classList.add('hidden');

                    if (error.response) {
                        // Error Validasi
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            if (errors.name) {
                                errorName.innerText = errors.name[0];
                                errorName.classList.remove('hidden');
                            }
                        } 
                        // Error Auth
                        else if (error.response.status === 401) {
                            localStorage.removeItem('auth_token');
                            window.location.href = "{{ route('login') }}";
                        } 
                        // Error Lainnya
                        else {
                            msgError.innerText = error.response.data.message || "Gagal memperbarui data.";
                            alertError.classList.remove('hidden');
                        }
                    } else {
                        msgError.innerText = "Tidak dapat terhubung ke server.";
                        alertError.classList.remove('hidden');
                    }
                }
            });
        });
    </script>
@endsection