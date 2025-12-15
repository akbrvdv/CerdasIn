@extends(Auth::user()->role === 'teacher' ? 'layouts.teacher' : 'layouts.student')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-purple-700">Detail Profil</h1>

    <div class="bg-white p-6 rounded-lg border">
        <div class="max-w-xl space-y-4">
            <div>
                <label class="text-sm text-gray-500">Nama Lengkap</label>
                <p class="font-semibold text-gray-900">{{ $user->name }}</p>
            </div>

            <div>
                <label class="text-sm text-gray-500">Email</label>
                <p class="font-semibold text-gray-900">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    @if ($teacher)
        <div class="bg-white p-6 rounded-lg border">
            <h2 class="text-lg font-semibold mb-4">Informasi Guru</h2>

            <div class="space-y-3">
                <div>
                    <label class="text-sm text-gray-500">NIK</label>
                    <p class="font-semibold">{{ $teacher->nik ?? '-' }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">No. Telepon</label>
                    <p class="font-semibold">{{ $teacher->phone ?? '-' }}</p>
                </div>
            </div>
        </div>
    @endif

    <div>
        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition">
            Edit Profil
        </a>
    </div>
</div>
@endsection
