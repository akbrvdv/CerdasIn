<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-white p-4">
        
        <div class="w-full max-w-md">
            
            {{-- Header --}}
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600 text-4xl"></i>
                    <span class="text-3xl font-bold text-gray-800">CerdasIn</span>
                </a>
            </div>

            {{-- Card Form --}}
            <div class="rounded-2xl border bg-white p-8 shadow-sm">
                <div class="mb-6 text-left">
                    <h2 class="text-xl font-bold text-gray-800">Atur Ulang Kata Sandi</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Masukkan alamat email Anda dan kata sandi baru di bawah ini.
                    </p>
                </div>

                {{-- Status Message --}}
                <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

                <form id="reset-password-form" class="space-y-6">
                    {{-- Token (Hidden) --}}
                    <input type="hidden" id="token" name="token">

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email" class="font-semibold" />
                        <x-text-input 
                            id="email" 
                            class="mt-2 block w-full bg-gray-100" 
                            type="email" 
                            name="email" 
                            required 
                            readonly 
                            placeholder="email@anda.com"
                        />
                        <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>
                    
                    {{-- Password Baru --}}
                    <div>
                        <x-input-label for="password" value="Kata Sandi Baru" class="font-semibold" />
                        <x-text-input 
                            id="password" 
                            class="mt-2 block w-full" 
                            type="password" 
                            name="password" 
                            required 
                            autofocus
                            autocomplete="new-password"
                            placeholder="Masukkan kata sandi baru"
                        />
                        <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi Baru" class="font-semibold" />
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

                    {{-- Tombol Submit --}}
                    <div class="mt-6 flex items-center">
                        <x-primary-button id="btn-submit" class="w-full justify-center bg-purple-600 py-3 text-sm hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800">
                            Atur Ulang Kata Sandi
                        </x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Link Kembali --}}
            <p class="mt-8 text-center text-sm text-gray-600">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 font-semibold text-purple-600 hover:text-purple-800 hover:underline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Kembali ke Login
                </a>
            </p>

        </div>
    </div>

    {{-- Script JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Setup Config
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const API_BASE_URL = ENV_URL.replace(/\/$/, '') + '/api';

            // 2. Ambil Token & Email dari URL
            // URL biasanya: http://localhost:8000/reset-password/{token}?email=user@example.com
            const urlPath = window.location.pathname;
            const tokenFromUrl = urlPath.split('/').pop(); // Ambil segmen terakhir
            const urlParams = new URLSearchParams(window.location.search);
            const emailFromUrl = urlParams.get('email');

            // Isi Input Otomatis
            document.getElementById('token').value = tokenFromUrl;
            if (emailFromUrl) {
                document.getElementById('email').value = emailFromUrl;
            }

            // 3. Handle Submit
            const form = document.getElementById('reset-password-form');
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorEmail = document.getElementById('error-email');
            const errorPass = document.getElementById('error-password');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Reset Error UI
                statusMsg.classList.add('hidden');
                errorEmail.classList.add('hidden');
                errorPass.classList.add('hidden');

                const token = document.getElementById('token').value;
                const email = document.getElementById('email').value;
                const password = document.getElementById('password').value;
                const password_confirmation = document.getElementById('password_confirmation').value;

                // Validasi Client Side
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
                    // Panggil API Backend (Endpoint standard Laravel biasanya /reset-password)
                    // Pastikan di routes/api.php backend sudah ada route untuk reset password
                    const response = await axios.post(`${API_BASE_URL}/reset-password`, {
                        token: token,
                        email: email,
                        password: password,
                        password_confirmation: password_confirmation
                    });

                    // Sukses
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