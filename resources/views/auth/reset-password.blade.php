<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-white p-4">
        
        <div class="w-full max-w-md">
            
            {{-- Header Branding --}}
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600 text-4xl"></i>
                    <span class="text-3xl font-bold text-gray-800">CerdasIn</span>
                </a>
            </div>

            {{-- Card Form --}}
            <div class="rounded-2xl border bg-white p-8 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Buat Kata Sandi Baru</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Silakan masukkan kata sandi baru Anda di bawah ini.
                    </p>
                </div>

                {{-- Status Message --}}
                <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

                <form id="reset-password-form" class="space-y-6">
                    {{-- Token (Hidden) --}}
                    <input type="hidden" id="token" name="token">

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input 
                            id="email" 
                            class="mt-2 block w-full bg-gray-100 cursor-not-allowed" 
                            type="email" 
                            name="email" 
                            required 
                            readonly 
                        />
                        <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" :value="__('Kata Sandi Baru')" />
                        <x-text-input 
                            id="password" 
                            class="mt-2 block w-full" 
                            type="password" 
                            name="password" 
                            required 
                            autofocus
                            autocomplete="new-password" 
                            placeholder="Minimal 8 karakter"
                        />
                        <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                        <x-text-input 
                            id="password_confirmation" 
                            class="mt-2 block w-full"
                            type="password"
                            name="password_confirmation" 
                            required 
                            autocomplete="new-password" 
                            placeholder="Ulangi kata sandi baru"
                        />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button id="btn-submit" class="w-full justify-center bg-purple-600 hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800">
                            {{ __('Simpan Kata Sandi') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Setup Config
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const API_BASE_URL = ENV_URL.replace(/\/$/, '') + '/api';

            // 2. Ambil Token & Email dari URL Browser
            // URL Frontend biasanya: http://localhost:8000/reset-password/{token}?email=user@example.com
            const urlPath = window.location.pathname;
            const tokenFromUrl = urlPath.split('/').pop(); // Ambil segmen terakhir (token)
            const urlParams = new URLSearchParams(window.location.search);
            const emailFromUrl = urlParams.get('email');

            // Isi Input Otomatis
            if (tokenFromUrl) document.getElementById('token').value = tokenFromUrl;
            if (emailFromUrl) document.getElementById('email').value = emailFromUrl;

            // 3. Handle Submit
            const form = document.getElementById('reset-password-form');
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorEmail = document.getElementById('error-email');
            const errorPass = document.getElementById('error-password');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Reset UI
                statusMsg.classList.add('hidden');
                errorEmail.classList.add('hidden');
                errorPass.classList.add('hidden');

                // Ambil Data
                const token = document.getElementById('token').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const password_confirmation = document.getElementById('password_confirmation').value;

                // Validasi Client
                if (password !== password_confirmation) {
                    statusMsg.innerText = "Konfirmasi kata sandi tidak cocok.";
                    statusMsg.classList.remove('hidden', 'bg-green-500');
                    statusMsg.classList.add('bg-red-500');
                    return;
                }

                // Loading State
                const originalText = btnSubmit.innerText;
                btnSubmit.innerText = 'Memproses...';
                btnSubmit.disabled = true;

                try {
                    // Panggil API Backend
                    // Pastikan route POST /api/reset-password sudah ada di backend
                    const response = await axios.post(`${API_BASE_URL}/reset-password`, {
                        token: token,
                        email: email,
                        password: password,
                        password_confirmation: password_confirmation
                    });

                    // SUKSES
                    statusMsg.innerText = "Kata sandi berhasil diubah! Mengalihkan ke login...";
                    statusMsg.classList.remove('hidden', 'bg-red-500');
                    statusMsg.classList.add('bg-green-500');

                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 2000);

                } catch (error) {
                    console.error('Reset Password Error:', error);
                    
                    let errorMessage = "Gagal mengatur ulang kata sandi.";

                    if (error.response) {
                        errorMessage = error.response.data.message || errorMessage;
                        
                        // Handle Validasi Server
                        if (error.response.data.errors) {
                            if (error.response.data.errors.email) {
                                errorEmail.innerText = error.response.data.errors.email[0];
                                errorEmail.classList.remove('hidden');
                            }
                            if (error.response.data.errors.password) {
                                errorPass.innerText = error.response.data.errors.password[0];
                                errorPass.classList.remove('hidden');
                            }
                        }
                    }

                    statusMsg.innerText = errorMessage;
                    statusMsg.classList.remove('hidden', 'bg-green-500');
                    statusMsg.classList.add('bg-red-500');
                } finally {
                    btnSubmit.innerText = originalText;
                    btnSubmit.disabled = false;
                }
            });
        });
    </script>
</x-guest-layout>