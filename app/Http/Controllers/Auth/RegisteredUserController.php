<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Classroom;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        // 2. Ambil semua data kelas
        $classrooms = Classroom::all(); 

        // 3. Kirim data 'classrooms' ke view menggunakan compact
        return view('auth.register', compact('classrooms'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
        {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
                'classroom_id' => ['required', 'exists:classrooms,id'], // <--- 4. Validasi input kelas
                // 'role' dihapus dari validasi jika Anda ingin memaksanya jadi student di bawah
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
                'classroom_id' => $request->classroom_id, // <--- 5. Simpan ID kelas ke database
            ]);
    event(new Registered($user));
            Auth::login($user);

            return redirect(route('teacher.dashboard', absolute: false));
        }
}

