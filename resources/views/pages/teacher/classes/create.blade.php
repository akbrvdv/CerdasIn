@extends('layouts.teacher')

@section('title', 'Tambah Kelas Baru')

@section('content')
    <div class="max-w-2xl mx-auto">
        
        {{-- Header & Tombol Kembali --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.classes.index') }}" 
               class="text-gray-500 hover:text-purple-600 transition">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h1 class="text-2xl font-bold text-purple-700">Buat Kelas Baru</h1>
        </div>

        {{-- Card Form --}}
        <div class="bg-white border rounded-xl p-6 shadow-sm">
            
            {{-- Alert Error Global --}}
            <div id="alert-error" class="hidden mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200">
                <p id="error-message">Terjadi kesalahan.</p>
            </div>

            <form id="create-class-form">
                
                {{-- Input Nama Kelas --}}
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Kelas</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500 shadow-sm"
                           placeholder="Contoh: X RPL 1, XII IPA 3"
                           required
                           autofocus>
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
                        <span id="btn-text">Simpan Kelas</span>
                        <i id="btn-loader" class="fa-solid fa-circle-notch fa-spin hidden ml-2"></i>
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Config
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const API_URL = ENV_URL.replace(/\/$/, '') + '/api';
            const token = localStorage.getItem('auth_token');

            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            // 2. Handle Submit
            const form = document.getElementById('create-class-form');
            const btnSubmit = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-text');
            const btnLoader = document.getElementById('btn-loader');
            
            // Elements Error
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

                // Ambil Data
                const name = document.getElementById('name').value;

                try {
                    // Kirim ke API Backend
                    // Endpoint: POST /api/teacher/classrooms
                    await axios.post(`${API_URL}/teacher/classrooms`, 
                        { name: name },
                        {
                            headers: { 
                                'Authorization': `Bearer ${token}`,
                                'ngrok-skip-browser-warning': 'true',
                                'Content-Type': 'application/json'
                            }
                        }
                    );

                    // Sukses -> Redirect ke Index
                    alert('Kelas berhasil dibuat!');
                    window.location.href = "{{ route('teacher.classes.index') }}";

                } catch (error) {
                    console.error('Create Class Error:', error);
                    btnSubmit.disabled = false;
                    btnText.innerText = 'Simpan Kelas';
                    btnLoader.classList.add('hidden');

                    // Handle Error Response
                    if (error.response) {
                        // Error Validasi (422)
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            if (errors.name) {
                                errorName.innerText = errors.name[0];
                                errorName.classList.remove('hidden');
                            }
                        } 
                        // Error Auth (401)
                        else if (error.response.status === 401) {
                            localStorage.removeItem('auth_token');
                            window.location.href = "{{ route('login') }}";
                        } 
                        // Error Lainnya
                        else {
                            msgError.innerText = error.response.data.message || "Gagal menyimpan data.";
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