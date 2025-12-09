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

            {{-- Card --}}
            <div class="rounded-2xl border bg-white p-8 shadow-sm">
                
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('Terima kasih telah mendaftar! Sebelum memulai, bisakah Anda memverifikasi alamat email Anda dengan mengklik tautan yang baru saja kami kirimkan ke email Anda? Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkannya lagi.') }}
                </div>

                {{-- Status Message (Success) --}}
                <div id="status-message" class="mb-4 hidden font-medium text-sm text-green-600"></div>
                
                {{-- Error Message --}}
                <div id="error-message" class="mb-4 hidden font-medium text-sm text-red-600"></div>

                <div class="mt-6 flex items-center justify-between">
                    {{-- Tombol Kirim Ulang --}}
                    <x-primary-button id="btn-resend">
                        {{ __('Kirim Ulang Email Verifikasi') }}
                    </x-primary-button>

                    {{-- Tombol Logout --}}
                    <button id="btn-logout" type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Keluar') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Setup Config
            const ENV_URL = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
            const API_BASE_URL = ENV_URL.replace(/\/$/, '') + '/api';
            const token = localStorage.getItem('auth_token');

            // Cek Login
            if (!token) {
                window.location.href = "{{ route('login') }}";
                return;
            }

            const btnResend = document.getElementById('btn-resend');
            const btnLogout = document.getElementById('btn-logout');
            const statusMsg = document.getElementById('status-message');
            const errorMsg = document.getElementById('error-message');

            // --- FUNGSI KIRIM ULANG EMAIL ---
            btnResend.addEventListener('click', async function() {
                // Reset UI
                statusMsg.classList.add('hidden');
                errorMsg.classList.add('hidden');
                
                // Loading State
                const originalText = btnResend.innerText;
                btnResend.innerText = 'Mengirim...';
                btnResend.disabled = true;

                try {
                    // Panggil API Backend
                    // Pastikan route POST /api/email/verification-notification ada
                    await axios.post(`${API_BASE_URL}/email/verification-notification`, {}, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    });

                    // Sukses
                    statusMsg.innerText = "Tautan verifikasi baru telah dikirim ke alamat email yang Anda berikan saat pendaftaran.";
                    statusMsg.classList.remove('hidden');

                } catch (error) {
                    console.error('Resend Error:', error);
                    let message = "Gagal mengirim ulang email.";
                    
                    if (error.response && error.response.status === 429) {
                        message = "Terlalu banyak permintaan. Silakan tunggu beberapa saat.";
                    } else if (error.response) {
                        message = error.response.data.message || message;
                    }

                    errorMsg.innerText = message;
                    errorMsg.classList.remove('hidden');
                } finally {
                    btnResend.innerText = originalText;
                    btnResend.disabled = false;
                }
            });

            // --- FUNGSI LOGOUT ---
            btnLogout.addEventListener('click', async function() {
                btnLogout.innerText = 'Keluar...';
                btnLogout.disabled = true;

                try {
                    await axios.post(`${API_BASE_URL}/logout`, {}, {
                        headers: { 'Authorization': `Bearer ${token}` }
                    });
                } catch (e) {
                    console.warn("Logout API failed, clearing local data anyway.");
                } finally {
                    localStorage.removeItem('auth_token');
                    localStorage.removeItem('user_data');
                    window.location.href = "{{ route('login') }}";
                }
            });
        });
    </script>
</x-guest-layout>