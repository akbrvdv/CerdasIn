<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend View Mapper)
|--------------------------------------------------------------------------
|
| File ini hanya bertugas mengembalikan Tampilan (View).
| Tidak ada logika database atau middleware auth di sini.
| Semua proteksi dan data akan ditangani oleh JavaScript (Axios + Token).
|
*/

// --- HALAMAN PUBLIK ---

Route::get('/', function () {
    return view('landing');
});

// Route Auth (Halaman Login & Register)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

// Route Dashboard Umum (Jika ada)
Route::get('/dashboard', function () {
    // Logic redirect role dipindah ke JavaScript di dalam view ini
    return view('layouts.app'); 
})->name('dashboard');


// --- HALAMAN TEACHER (GURU) ---
Route::prefix('teacher')->name('teacher.')->group(function () {
    
    // Dashboard Guru
    Route::get('dashboard', function () {
        return view('pages.teacher.index');
    })->name('dashboard');

    // 1. Classes (Kelola Kelas)
    Route::prefix('classes')->name('classes.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.classes.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.classes.create'))->name('create');
        Route::get('/{class}/edit', fn() => view('pages.teacher.classes.edit'))->name('edit');
    });

    // 2. Materials (Kelola Materi)
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.materials.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.materials.create'))->name('create');
        Route::get('/{material}/edit', fn() => view('pages.teacher.materials.edit'))->name('edit');
    });

    // 3. Quizzes (Kelola Kuis)
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', fn() => view('pages.teacher.quizzes.index'))->name('index');
        Route::get('/create', fn() => view('pages.teacher.quizzes.create'))->name('create');
        Route::get('/{quiz}/edit', fn() => view('pages.teacher.quizzes.edit'))->name('edit');
        
        // Questions (Soal dalam Kuis)
        Route::get('/{quiz}/questions', fn() => view('pages.teacher.questions.index'))->name('questions.index');
        Route::get('/{quiz}/questions/create', fn() => view('pages.teacher.questions.create'))->name('questions.create');
        Route::get('/{quiz}/questions/{question}/edit', fn() => view('pages.teacher.questions.edit'))->name('questions.edit');
    });

});


// --- HALAMAN STUDENT (SISWA) ---
Route::prefix('student')->name('student.')->group(function () {

    // Dashboard Siswa
    Route::get('dashboard', function () {
        return view('pages.student.index');
    })->name('dashboard');

    // 1. Classes (Daftar Kelas)
    Route::get('classes', fn() => view('pages.student.classes.index'))->name('classes.index');

    // 2. Materials (Lihat Materi)
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', fn() => view('pages.student.materials.index'))->name('index');
        Route::get('/{material}', fn() => view('pages.student.materials.show'))->name('show');
    });

    // 3. Quizzes (Kerjakan Kuis)
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', fn() => view('pages.student.quizzes.index'))->name('index');
        Route::get('/{quiz}', fn() => view('pages.student.quizzes.show'))->name('show'); // Halaman detail/start kuis
    });

    // 4. Scores (Lihat Nilai)
    Route::get('scores', fn() => view('pages.student.scores.index'))->name('scores.index');

});


// --- PROFILE ---
Route::get('/profile', fn() => view('profile.edit'))->name('profile.edit');