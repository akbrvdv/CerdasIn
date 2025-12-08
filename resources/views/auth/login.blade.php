<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-white p-4">
        
        <div class="w-full max-w-md">
            
            {{-- Header Form --}}
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600 text-4xl"></i>
                    <span class="text-3xl font-bold text-gray-800">CerdasIn</span>
                </a>
                <p class="mt-2 text-gray-600">Selamat datang kembali! Silakan masuk ke akun Anda.</p>
            </div>

            {{-- Card Form --}}
            <div class="rounded-2xl border bg-white p-8 shadow-sm">
                
                {{-- Area Pesan Error/Sukses (Diisi via JS) --}}
                <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

                {{-- Form Login --}}
                {{-- Kita hapus action blade route, ganti jadi onsubmit --}}
                <form id="login-form" class="space-y-6">
                    
                    <div>
                        <x-input-label for="email" value="Email" class="font-semibold" />
                        <x-text-input 
                            id="email" 
                            class="mt-2 block w-full" 
                            type="email" 
                            name="email" 
                            required 
                            autofocus 
                            placeholder="contoh@email.com"
                        />
                        {{-- Error field khusus JS --}}
                        <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" value="Kata Sandi" class="font-semibold" />
                            @if (Route::has('password.request'))
                                <a class="text-sm text-purple-600 hover:text-purple-800 hover:underline" href="{{ route('password.request') }}">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>
                        <x-text-input 
                            id="password" 
                            class="mt-2 block w-full" 
                            type="password" 
                            name="password" 
                            required 
                            placeholder="Masukkan kata sandi"
                        />
                         {{-- Error field khusus JS --}}
                         <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500" name="remember">
                        <label for="remember_me" class="ms-2 block text-sm text-gray-700">
                            Ingat saya
                        </label>
                    </div>
                    
                    {{-- Tombol Submit --}}
                    <div>
                        <x-primary-button id="btn-submit" class="w-full justify-center bg-purple-600 py-3 text-sm hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800">
                           Masuk
                        </x-primary-button>
                    </div>
                </form>

                {{-- Tautan Daftar --}}
                <p class="mt-8 text-center text-sm text-gray-600">
                    Belum punya akun? 
                    <a href="{{ route('register') }}" class="font-semibold text-purple-600 hover:text-purple-800 hover:underline">
                        Daftar di sini
                    </a>
                </p>
            </div>

        </div>
    </div>

    {{-- Script untuk Handle Login ke API --}}
    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault(); // Mencegah reload halaman

            // Ambil elemen
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorEmail = document.getElementById('error-email');
            const errorPass = document.getElementById('error-password');

            // Reset tampilan error
            statusMsg.classList.add('hidden');
            errorEmail.classList.add('hidden');
            errorPass.classList.add('hidden');
            
            // Ubah tombol jadi loading
            const originalBtnText = btnSubmit.innerText;
            btnSubmit.innerText = 'Memproses...';
            btnSubmit.disabled = true;

            try {
                // Endpoint API Anda
                const apiUrl = 'https://71870eaf2b39.ngrok-free.app/api/login';

                const response = await axios.post(apiUrl, {
                    email: email,
                    password: password
                });

                // --- SUKSES ---
                const data = response.data;
                
                // Tampilkan pesan sukses
                statusMsg.innerText = "Login Berhasil! Mengalihkan...";
                statusMsg.classList.remove('hidden', 'bg-red-500');
                statusMsg.classList.add('bg-green-500');

                // Simpan Token di LocalStorage
                if (data.token) {
                    localStorage.setItem('auth_token', data.token);
                    
                    // Simpan data user jika perlu
                    if(data.user) {
                        localStorage.setItem('user_data', JSON.stringify(data.user));
                    }
                }

                // Redirect ke Dashboard (sesuaikan route dashboard Anda)
                // Karena ini SPA-like, kita redirect manual via window.location
                setTimeout(() => {
                    // Cek role user untuk redirect yang sesuai (opsional)
                    if (data.user && data.user.role === 'teacher') {
                        window.location.href = '/teacher/dashboard'; 
                    } else if (data.user && data.user.role === 'student') {
                        window.location.href = '/student/dashboard';
                    } else {
                        window.location.href = '/dashboard'; 
                    }
                }, 1000);

            } catch (error) {
                // --- GAGAL ---
                console.error('Login Error:', error);
                
                let errorMessage = "Terjadi kesalahan pada server.";
                
                if (error.response) {
                    // Jika ada respons error dari backend (misal 401 atau 422)
                    errorMessage = error.response.data.message || "Email atau password salah.";
                    
                    // Jika ada validasi error spesifik
                    if (error.response.data.errors) {
                         if(error.response.data.errors.email) {
                             errorEmail.innerText = error.response.data.errors.email[0];
                             errorEmail.classList.remove('hidden');
                         }
                         if(error.response.data.errors.password) {
                             errorPass.innerText = error.response.data.errors.password[0];
                             errorPass.classList.remove('hidden');
                         }
                    }
                } else if (error.request) {
                    errorMessage = "Tidak dapat menghubungi server API.";
                }

                // Tampilkan pesan error utama
                statusMsg.innerText = errorMessage;
                statusMsg.classList.remove('hidden', 'bg-green-500');
                statusMsg.classList.add('bg-red-500');

            } finally {
                // Kembalikan tombol seperti semula
                btnSubmit.innerText = originalBtnText;
                btnSubmit.disabled = false;
            }
        });
    </script>
</x-guest-layout>