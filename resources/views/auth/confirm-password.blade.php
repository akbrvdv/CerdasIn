<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Ini adalah area aman aplikasi. Harap konfirmasi kata sandi Anda sebelum melanjutkan.') }}
    </div>

    {{-- Area Pesan Error Global --}}
    <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

    <form id="confirm-password-form">
        {{-- Token CSRF tidak wajib untuk API stateless, tapi baik untuk kompatibilitas form --}}
        @csrf

        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password"
                            placeholder="Masukkan kata sandi Anda" />

            {{-- Error Field khusus JS --}}
            <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button id="btn-submit">
                {{ __('Konfirmasi') }}
            </x-primary-button>
        </div>
    </form>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Konfigurasi URL API
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const BASE_URL = ENV_URL.replace(/\/$/, '');
            const API_URL = `${BASE_URL}/api`;
            const token = localStorage.getItem('auth_token');

            // Cek jika token tidak ada, redirect ke login
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            const form = document.getElementById('confirm-password-form');
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorPass = document.getElementById('error-password');

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Reset Error
                statusMsg.classList.add('hidden');
                errorPass.classList.add('hidden');
                
                const password = document.getElementById('password').value;

                // Loading State
                const originalText = btnSubmit.innerText;
                btnSubmit.innerText = 'Memproses...';
                btnSubmit.disabled = true;

                try {
                    // Panggil API Confirm Password
                    // Pastikan Anda sudah membuat route POST /api/confirm-password di backend
                    const response = await axios.post(`${API_URL}/confirm-password`, 
                        { password: password },
                        { headers: { 'Authorization': `Bearer ${token}` } }
                    );

                    // SUKSES
                    statusMsg.innerText = "Kata sandi terkonfirmasi!";
                    statusMsg.classList.remove('hidden', 'bg-red-500');
                    statusMsg.classList.add('bg-green-500');

                    // Redirect ke halaman yang dituju (Intended URL)
                    // Karena ini SPA-ish, kita bisa redirect ke dashboard atau back
                    setTimeout(() => {
                        // Cek apakah ada intended URL di query param atau default ke dashboard
                        const urlParams = new URLSearchParams(window.location.search);
                        const redirectUrl = urlParams.get('redirect_to') || "{{ route('dashboard') }}";
                        window.location.href = redirectUrl;
                    }, 1000);

                } catch (error) {
                    console.error('Confirmation error:', error);
                    
                    let errorMessage = "Terjadi kesalahan. Silakan coba lagi.";
                    
                    if (error.response) {
                        // Error dari Backend
                        errorMessage = error.response.data.message || "Kata sandi salah.";
                        
                        // Validasi Spesifik
                        if (error.response.data.errors && error.response.data.errors.password) {
                            errorPass.innerText = error.response.data.errors.password[0];
                            errorPass.classList.remove('hidden');
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