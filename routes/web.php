<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; // [PENTING] Wajib import ini

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend View Mapper)
|--------------------------------------------------------------------------
|
| File ini bertugas menghubungkan URL browser dengan Tampilan (Blade View).
| Logika data dan database ditangani oleh API (JavaScript/Axios).
|
*/

// =========================================================================
// 1. HALAMAN PUBLIK (Bisa diakses tanpa login)
// =========================================================================

// Landing Page (Halaman Utama)
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Halaman Login
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Halaman Register
Route::get('/register', function () {
    return view('auth.register');
})->name('register');


// =========================================================================
// 2. HALAMAN GURU (TEACHER)
// =========================================================================
Route::prefix('teacher')->name('teacher.')->group(function () {
    
    // Dashboard Guru
    Route::get('dashboard', fn() => view('pages.teacher.index'))->name('dashboard');

    // A. Kelola Kelas
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.classes.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.classes.create'))->name('create');
        Route::get('/{class}/edit', fn() => view('pages.teacher.classes.edit'))->name('edit');
    });

    // B. Kelola Materi
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.materials.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.materials.create'))->name('create');
        Route::get('/{material}/edit', fn() => view('pages.teacher.materials.edit'))->name('edit');
    });

    // C. Kelola Kuis
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.quizzes.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.quizzes.create'))->name('create');
        Route::get('/{quiz}/edit', fn() => view('pages.teacher.quizzes.edit'))->name('edit');
        
        // D. Kelola Soal (Nested dalam Kuis)
        Route::get('/{quiz}/questions', fn() => view('pages.teacher.questions.index'))->name('questions.index');
        Route::get('/{quiz}/questions/create', fn() => view('pages.teacher.questions.create'))->name('questions.create');
        Route::get('/{quiz}/questions/{question}/edit', fn() => view('pages.teacher.questions.edit'))->name('questions.edit');
    });

});


// =========================================================================
// 3. HALAMAN SISWA (STUDENT)
// =========================================================================
Route::prefix('student')->name('student.')->group(function () {

    // Dashboard Siswa
    Route::get('dashboard', fn() => view('pages.student.index'))->name('dashboard');

    // A. Daftar Kelas (Pemilihan Kelas)
    Route::get('classes', fn() => view('pages.student.classes.index'))->name('classes.index');

    // B. Materi (Lihat & Download)
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', fn() => view('pages.student.materials.index'))->name('index');
        Route::get('/{material}', fn() => view('pages.student.materials.show'))->name('show');
    });

    // C. Kuis (Pengerjaan)
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', fn() => view('pages.student.quizzes.index'))->name('index');
        Route::get('/{quiz}', fn() => view('pages.student.quizzes.show'))->name('show');
    });

    // D. Scores (Riwayat Nilai)
    Route::prefix('scores')->name('scores.')->group(function () {
        // Index -> URL: /student/scores
        Route::get('/', fn() => view('pages.student.scores.index'))->name('index');

        // Detail -> URL: /student/scores/{id}
        Route::get('/{id}', fn() => view('pages.student.scores.show'))->name('show');
    });

});


// =========================================================================
// 4. PROFILE PENGGUNA (GURU & SISWA)
// =========================================================================

// [PENTING] Middleware 'auth' ditambahkan agar halaman ini TIDAK BISA diakses jika belum login.
// Ini mencegah error "Attempt to read property name on null".

Route::get('/profile', function () {
    return view('profile.edit', [
        'user' => Auth::user() // Mengirim data user agar dikenali di View
    ]);
})->middleware('auth')->name('profile.edit');