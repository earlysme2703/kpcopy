<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Pilih Kelas</h2>
    </x-slot>

    <div class="py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($classes as $class)
                <a href="{{ route('admin.siswa.list', $class->id) }}" class="bg-white p-6 rounded-lg shadow hover:bg-blue-100">
                    <h3 class="text-lg font-bold text-center">Kelas {{ $class->name }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</x-app-layout>