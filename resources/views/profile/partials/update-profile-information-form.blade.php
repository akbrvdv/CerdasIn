<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Perbarui detail profil, alamat email, dan foto profil akun Anda.") }}
        </p>
    </header>

    {{-- 
        FORM UPDATE
        - Action dihapus agar tidak error route.
        - ID ditambahkan untuk JavaScript.
        - Enctype ada untuk handle file upload di JS (FormData).
    --}}
    <form id="form-profile-update" class="mt-6 space-y-6" enctype="multipart/form-data">
        
        {{-- 1. Input Nama --}}
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" />
            {{-- Menggunakan $user->name untuk nilai awal --}}
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <p id="error-name" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>

        {{-- 2. Input Email --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <p id="error-email" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>

        {{-- 3. Input Foto Profil --}}
        <div>
            <x-input-label for="photo_profile" :value="__('Foto Profil')" />
            
            {{-- Preview Foto Saat Ini --}}
            <div id="current-photo-container" class="my-2 {{ $user->photo_profile ? '' : 'hidden' }}">
                <p class="text-xs text-gray-500 mb-1">Foto saat ini:</p>
                {{-- Pastikan logic asset storage sudah benar di Laravel (php artisan storage:link) --}}
                <img id="img-preview" src="{{ $user->photo_profile ? asset('storage/' . $user->photo_profile) : '#' }}" 
                     alt="Foto Profil" 
                     class="w-20 h-20 rounded-full object-cover border border-gray-300 shadow-sm">
            </div>

            {{-- Input File --}}
            <input id="photo_profile" name="photo_profile" type="file" 
                class="mt-1 block w-full text-sm text-gray-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-md file:border-0
                file:text-sm file:font-semibold
                file:bg-purple-50 file:text-purple-700
                hover:file:bg-purple-100" 
                accept="image/png, image/jpeg, image/jpg"
                onchange="previewImage(event)" />
            
            <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, JPEG. (Maks. 2MB)</p>
            <p id="error-photo_profile" class="mt-2 text-sm text-red-600 hidden"></p>
        </div>

        {{-- Tombol Simpan --}}
        <div class="flex items-center gap-4">
            <x-primary-button id="btn-save-profile">{{ __('Simpan') }}</x-primary-button>

            {{-- Pesan Sukses / Loading --}}
            <p id="status-message" class="text-sm text-gray-600 hidden"></p>
        </div>
    </form>

    {{-- JAVASCRIPT LOGIC --}}
    <script>
        // 1. Fungsi Preview Image sebelum upload
        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.getElementById('img-preview');
                    const container = document.getElementById('current-photo-container');
                    
                    img.src = e.target.result;
                    container.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 2. Handle Submit Form via Axios
        document.getElementById('form-profile-update').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Setup
            const btn = document.getElementById('btn-save-profile');
            const statusMsg = document.getElementById('status-message');
            const token = localStorage.getItem('auth_token');
            const ENV_URL = "{{ env('API_BASE_URL') }}";
            const API_URL = ENV_URL.replace(/\/$/, '') + '/api';

            // Reset Error Messages
            document.querySelectorAll('[id^="error-"]').forEach(el => el.classList.add('hidden'));
            
            // Loading State
            btn.disabled = true;
            btn.innerText = "Menyimpan...";
            statusMsg.innerText = "";
            statusMsg.classList.add('hidden');

            // Siapkan Data (FormData wajib untuk file upload)
            const formData = new FormData(this);
            // Laravel API biasanya butuh method POST dengan _method=PATCH/PUT untuk update file
            formData.append('_method', 'POST'); 

            try {
                // Kirim ke Endpoint API (Pastikan endpoint ini ada di route/api.php)
                // Contoh: Route::post('/profile-update', ...)
                const response = await axios.post(`${API_URL}/profile-update`, formData, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data', // Penting untuk file
                        'ngrok-skip-browser-warning': 'true'
                    }
                });

                // Sukses
                statusMsg.innerText = "Profil berhasil diperbarui.";
                statusMsg.classList.remove('hidden', 'text-red-600');
                statusMsg.classList.add('text-green-600');
                
                // Update data user di LocalStorage jika perlu
                if(response.data.data) {
                    localStorage.setItem('user_data', JSON.stringify(response.data.data));
                }

            } catch (error) {
                console.error("Update failed:", error);
                
                statusMsg.innerText = "Gagal menyimpan.";
                statusMsg.classList.remove('hidden', 'text-green-600');
                statusMsg.classList.add('text-red-600');

                // Tampilkan Validasi Error dari Laravel
                if (error.response && error.response.data.errors) {
                    const errors = error.response.data.errors;
                    Object.keys(errors).forEach(key => {
                        const errorEl = document.getElementById(`error-${key}`);
                        if (errorEl) {
                            errorEl.innerText = errors[key][0];
                            errorEl.classList.remove('hidden');
                        }
                    });
                }
            } finally {
                btn.disabled = false;
                btn.innerText = "{{ __('Simpan') }}";
            }
        });
    </script>
</section>