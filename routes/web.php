<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Frontend View Mapper)
|--------------------------------------------------------------------------
|
| File ini hanya bertugas mengembalikan Tampilan (View).
| Logika data & database ditangani oleh JavaScript (Axios) ke API.
|
*/

// --- HALAMAN PUBLIK / AUTH ---

Route::get('/', function () {
    return redirect()->route('login'); // Redirect root ke login
});

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/register', fn() => view('auth.register'))->name('register');


// --- HALAMAN TEACHER (GURU) ---
Route::prefix('teacher')->name('teacher.')->group(function () {
    
    // Dashboard
    Route::get('dashboard', fn() => view('pages.teacher.index'))->name('dashboard');

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
        // Halaman Daftar Kuis
        Route::get('/', fn() => view('pages.teacher.quizzes.index'))->name('index');
        
        // Halaman Buat Kuis Baru (PENTING untuk tombol di index)
        Route::get('/create', fn() => view('pages.teacher.quizzes.create'))->name('create');
        
        // Halaman Edit Kuis
        Route::get('/{quiz}/edit', fn() => view('pages.teacher.quizzes.edit'))->name('edit');

        // --- Sub-Route: Questions (Kelola Soal) ---
        // URL: /teacher/quizzes/{quiz_id}/questions
        Route::get('/{quiz}/questions', fn() => view('pages.teacher.questions.index'))->name('questions.index');
        Route::get('/{quiz}/questions/create', fn() => view('pages.teacher.questions.create'))->name('questions.create');
        Route::get('/{quiz}/questions/{question}/edit', fn() => view('pages.teacher.questions.edit'))->name('questions.edit');
    });

});


// --- HALAMAN STUDENT (SISWA) ---
Route::prefix('student')->name('student.')->group(function () {

    // Dashboard
    Route::get('dashboard', fn() => view('pages.student.index'))->name('dashboard');

    // 1. Classes & Materials
    Route::get('classes', fn() => view('pages.student.classes.index'))->name('classes.index');
    
    Route::prefix('materials')->name('materials.')->group(function () {
        Route::get('/', fn() => view('pages.student.materials.index'))->name('index');
        Route::get('/{material}', fn() => view('pages.student.materials.show'))->name('show');
    });

    // 2. Quizzes (Mengerjakan Kuis)
    Route::prefix('quizzes')->name('quizzes.')->group(function () {
        Route::get('/', fn() => view('pages.student.quizzes.index'))->name('index');
        Route::get('/{quiz}', fn() => view('pages.student.quizzes.show'))->name('show');
    });

    // 3. Scores (Riwayat Nilai)
    Route::prefix('scores')->name('scores.')->group(function () {
        Route::get('/', fn() => view('pages.student.scores.index'))->name('index');
        Route::get('/{score}', fn() => view('pages.student.scores.show'))->name('show');
    });

});