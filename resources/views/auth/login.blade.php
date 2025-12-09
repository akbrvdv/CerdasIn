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
                
                {{-- Area Pesan Error/Sukses --}}
                <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

                {{-- Form Login --}}
                <form id="login-form" class="space-y-6">
                    
                    {{-- Email --}}
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
                        <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Password --}}
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
                        <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Remember Me --}}
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

    {{-- Script Handle Login dengan Debugging Lengkap --}}
    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log("--- 1. Form Login Disubmit ---");

            // 1. Ambil Data Input
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            console.log("Data yang akan dikirim:", { email: email, password: '***' });

            // 2. Setup UI Elements
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorEmail = document.getElementById('error-email');
            const errorPass = document.getElementById('error-password');

            // Reset Error UI
            statusMsg.classList.add('hidden');
            errorEmail.classList.add('hidden');
            errorPass.classList.add('hidden');
            
            // Set Loading State
            const originalBtnText = btnSubmit.innerText;
            btnSubmit.innerText = 'Memproses...';
            btnSubmit.disabled = true;

            try {
                // --- KONFIGURASI URL ---
                const envUrl = "{{ env('API_BASE_URL', 'http://127.0.0.1:8001') }}";
                const baseUrl = envUrl.replace(/\/$/, ''); 
                const apiUrl = `${baseUrl}/api/login`;
                
                console.log(`--- 2. Mengirim Request ke: ${apiUrl} ---`);

                // --- KIRIM REQUEST ---
                const response = await axios.post(apiUrl, {
                    email: email,
                    password: password
                });

                console.log("--- 3. Respons Diterima dari Server ---");
                console.log("Raw Response:", response); // Lihat object response Axios lengkap

                // Ambil body JSON
                const apiResponse = response.data;
                console.log("Isi Body JSON (apiResponse):", apiResponse);

                // --- SUKSES ---
                statusMsg.innerText = "Login Berhasil! Mengalihkan...";
                statusMsg.classList.remove('hidden', 'bg-red-500');
                statusMsg.classList.add('bg-green-500');

                // 1. Cek & Simpan Token
                if (apiResponse.token) {
                    localStorage.setItem('auth_token', apiResponse.token);
                    console.log("✅ Token BERHASIL disimpan ke localStorage:", apiResponse.token.substring(0, 10) + "...");
                } else {
                    console.error("❌ Token TIDAK ditemukan di apiResponse.token!");
                    console.log("Struktur JSON saat ini:", apiResponse);
                }

                // 2. Cek & Simpan Data User
                if (apiResponse.user) {
                    localStorage.setItem('user_data', JSON.stringify(apiResponse.user));
                    console.log("✅ Data User BERHASIL disimpan:", apiResponse.user);
                } else {
                    console.error("❌ Data User TIDAK ditemukan di apiResponse.user!");
                }

                // 3. Redirect Logic
                console.log("--- 4. Bersiap Redirect (Delay 1 detik) ---");
                setTimeout(() => {
                    if (apiResponse.user && apiResponse.user.role) {
                        const role = apiResponse.user.role;
                        console.log(`Role terdeteksi: ${role}`);

                        if (role === 'teacher') {
                            console.log("Redirecting to /teacher/dashboard");
                            window.location.href = '/teacher/dashboard'; 
                        } else if (role === 'student') {
                            console.log("Redirecting to /student/dashboard");
                            window.location.href = '/student/dashboard';
                        } else {
                            console.log("Role tidak dikenali, redirecting to /dashboard");
                            
                        }
                    } else {
                        console.warn("User/Role tidak ada, redirecting default to /dashboard");
                        
                    }
                }, 1000);

            } catch (error) {
                // --- ERROR HANDLING ---
                console.error("--- ERROR TERJADI ---", error);
                
                let errorMessage = "Terjadi kesalahan pada server.";
                
                if (error.response) {
                    // Pesan Error dari API
                    console.log("Status Code:", error.response.status);
                    console.log("Data Error dari Server:", error.response.data);

                    errorMessage = error.response.data.message || "Login gagal.";
                    
                    // Error Per Field
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
                    console.error("Tidak ada respons dari server (Network Error?)");
                    errorMessage = "Tidak dapat menghubungi server API. Pastikan backend aktif.";
                }

                statusMsg.innerText = errorMessage;
                statusMsg.classList.remove('hidden', 'bg-green-500');
                statusMsg.classList.add('bg-red-500');

            } finally {
                console.log("--- Selesai (Tombol diaktifkan kembali) ---");
                // Reset Tombol
                btnSubmit.innerText = originalBtnText;
                btnSubmit.disabled = false;
            }
        });
    </script>
</x-guest-layout>