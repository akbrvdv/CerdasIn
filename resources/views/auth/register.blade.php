<x-guest-layout>
    <div class="flex min-h-screen flex-col items-center justify-center bg-white p-4">
        
        <div class="w-full max-w-md">
            
            {{-- Header Form --}}
            <div class="mb-8 text-center">
                <a href="/" class="inline-flex items-center gap-2">
                    <i class="fa-solid fa-graduation-cap text-purple-600 text-4xl"></i>
                    <span class="text-3xl font-bold text-gray-800">CerdasIn</span>
                </a>
                <p class="mt-2 text-gray-600">Buat akun baru untuk memulai petualangan belajar Anda.</p>
            </div>

            {{-- Card Form --}}
            <div class="rounded-2xl border bg-white p-8 shadow-sm">
                
                {{-- Area Pesan Error/Sukses (Diisi via JS, sama seperti Login) --}}
                <div id="status-message" class="mb-4 hidden rounded-md p-3 text-sm text-white"></div>

                {{-- Form Register --}}
                <form id="register-form" class="space-y-6">
                    
                    {{-- Nama Lengkap --}}
                    <div>
                        <x-input-label for="name" value="Nama Lengkap" class="font-semibold" />
                        <x-text-input 
                            id="name" 
                            class="mt-2 block w-full" 
                            type="text" 
                            name="name" 
                            required 
                            autofocus 
                            placeholder="Masukkan nama lengkap Anda"
                        />
                        <p id="error-name" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Email --}}
                    <div>
                        <x-input-label for="email" value="Email" class="font-semibold" />
                        <x-text-input 
                            id="email" 
                            class="mt-2 block w-full" 
                            type="email" 
                            name="email" 
                            required 
                            placeholder="contoh@email.com"
                        />
                        <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Password --}}
                    <div>
                        <x-input-label for="password" value="Kata Sandi" class="font-semibold" />
                        <x-text-input 
                            id="password" 
                            class="mt-2 block w-full" 
                            type="password" 
                            name="password" 
                            required 
                            placeholder="Buat kata sandi yang kuat"
                        />
                        <p id="error-password" class="mt-2 text-sm text-red-600 hidden"></p>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <x-input-label for="password_confirmation" value="Konfirmasi Kata Sandi" class="font-semibold" />
                        <x-text-input 
                            id="password_confirmation" 
                            class="mt-2 block w-full" 
                            type="password"
                            name="password_confirmation" 
                            required 
                            placeholder="Ulangi kata sandi Anda"
                        />
                    </div>
                    
                    {{-- Tombol Submit --}}
                    <div>
                        <x-primary-button id="btn-submit" class="w-full justify-center bg-purple-600 py-3 text-sm hover:bg-purple-700 focus:bg-purple-700 active:bg-purple-800">
                           Daftar
                        </x-primary-button>
                    </div>
                </form>

                {{-- Tautan Login --}}
                <p class="mt-8 text-center text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="font-semibold text-purple-600 hover:text-purple-800 hover:underline">
                        Masuk di sini
                    </a>
                </p>
            </div>

        </div>
    </div>

    {{-- Script untuk Handle Register ke API (Menggunakan Axios sesuai patokan Login) --}}
    <script>
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault(); // Mencegah reload halaman

            // Ambil elemen input
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const password_confirmation = document.getElementById('password_confirmation').value;
            
            // Ambil elemen UI
            const btnSubmit = document.getElementById('btn-submit');
            const statusMsg = document.getElementById('status-message');
            const errorName = document.getElementById('error-name');
            const errorEmail = document.getElementById('error-email');
            const errorPass = document.getElementById('error-password');

            // Reset tampilan error
            statusMsg.classList.add('hidden');
            errorName.classList.add('hidden');
            errorEmail.classList.add('hidden');
            errorPass.classList.add('hidden');
            
            // Validasi sederhana di frontend (password match)
            if (password !== password_confirmation) {
                statusMsg.innerText = "Konfirmasi kata sandi tidak cocok.";
                statusMsg.classList.remove('hidden', 'bg-green-500');
                statusMsg.classList.add('bg-red-500');
                return;
            }

            // Ubah tombol jadi loading
            const originalBtnText = btnSubmit.innerText;
            btnSubmit.innerText = 'Memproses...';
            btnSubmit.disabled = true;

            try {
                // Endpoint API Register
                const apiUrl = 'https://56c8e939278d.ngrok-free.app/api/register';

                // Mengirim request POST via Axios
                const response = await axios.post(apiUrl, {
                    name: name,
                    email: email,
                    password: password,
                    password_confirmation: password_confirmation,
                    role: 'student' // Sesuai instruksi, role diset otomatis
                });

                // --- SUKSES ---
                const data = response.data;
                
                // Tampilkan pesan sukses
                statusMsg.innerText = "Registrasi Berhasil! Anda sedang dialihkan...";
                statusMsg.classList.remove('hidden', 'bg-red-500');
                statusMsg.classList.add('bg-green-500');

                // Karena backend register Anda juga mengembalikan token,
                // Kita bisa langsung login user tersebut tanpa harus ke halaman login dulu.
                if (data.token) {
                    localStorage.setItem('auth_token', data.token);
                    
                    if(data.user) {
                        localStorage.setItem('user_data', JSON.stringify(data.user));
                    }
                }

                // Redirect ke Dashboard Student
                setTimeout(() => {
                    window.location.href = '/student/dashboard';
                }, 1000);

            } catch (error) {
                // --- GAGAL ---
                console.error('Register Error:', error);
                
                let errorMessage = "Terjadi kesalahan pada server.";
                
                if (error.response) {
                    // Pesan dari backend
                    errorMessage = error.response.data.message || "Registrasi gagal.";
                    
                    // Menampilkan error per field jika ada (validasi Laravel)
                    if (error.response.data.errors) {
                         if(error.response.data.errors.name) {
                             errorName.innerText = error.response.data.errors.name[0];
                             errorName.classList.remove('hidden');
                         }
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

                // Tampilkan pesan error utama di atas
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