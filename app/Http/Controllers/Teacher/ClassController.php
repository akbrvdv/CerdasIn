<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    // public function index()
    // {
    //     $classes = Classroom::all();
    //     return view('pages.teacher.classes.index', compact('classes'));
    // }
    public function index()
    {
        // 1. URL Endpoint API
        $apiUrl = 'https://71870eaf2b39.ngrok-free.app/api/teacher/classrooms';

        // 2. Ambil Data dari API
        // Pastikan menambahkan header Authorization jika API butuh token
        // $response = Http::withToken('TOKEN_ANDA')->get($apiUrl);
        
        // Asumsi API tidak butuh token untuk GET ini (sesuai contoh soal)
        $response = Http::get($apiUrl);

        $classes = [];

        // 3. Cek apakah request berhasil (Status 200)
        if ($response->successful()) {
            // Ambil body response sebagai array/object
            // Sesuaikan dengan struktur JSON API Anda. 
            // Biasanya data ada di key 'data' atau langsung array.
            $data = $response->json(); 
            
            // Jika struktur JSON: { "data": [...] }
            // $classes = $data['data'] ?? [];
            
            // Jika struktur JSON langsung array: [...]
            $classes = $data;
        } else {
            // Opsional: Handle error jika API mati/gagal
            // return back()->with('error', 'Gagal mengambil data kelas dari API');
        }

        // 4. Konversi ke Collection (Opsional, agar mirip Eloquent)
        $classes = collect($classes)->map(function ($item) {
            // Konversi array ke object agar sintaks $class->name di view tetap jalan
            return (object) $item;
        });

        // 5. Kirim ke View
        return view('pages.teacher.classes.index', compact('classes'));
    }

    public function create()
    {
        return view('pages.teacher.classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Classroom::create([
            'name' => $request->name,
        ]);

        return redirect()->route('teacher.classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Classroom $class)
    {
        return view('pages.teacher.classes.edit', compact('class'));
    }

    public function update(Request $request, Classroom $class)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $class->update(['name' => $request->name]);

        return redirect()->route('teacher.classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Classroom $class)
    {
        $class->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}