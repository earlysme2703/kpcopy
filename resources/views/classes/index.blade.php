<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Kelas') }}
        </h2>
    </x-slot>

    <!-- Alpine Root -->
    <div x-data="{
        showModal: false,
        showEditModal: false,
        editId: null,
        editName: '',
    }" class="p-4">

        <!-- Tombol Tambah -->
        <button @click="showModal = true" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
            + Tambah Kelas
        </button>

        <!-- Tabel -->
        <table class="table-auto w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Nama Kelas</th>
                    <th class="px-4 py-2">Jumlah Siswa</th>
                    <th class="px-4 py-2">Wali Kelas</th>
                    <th class="px-4 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classes as $index => $kelas)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $kelas->name }}</td>
                        <td class="px-4 py-2">{{ $kelas->students_count }} Siswa</td>
                        <td class="px-4 py-2">{{ $kelas->waliKelas?->name ?? '-' }}</td>
                        <td class="px-4 py-2 space-x-1">
                            <!-- Lihat -->
                            <a href="{{ route('admin.kelas.show', $kelas->id) }}"
                                class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Lihat</a>

                            <!-- Hapus -->
                            <form action="{{ route('admin.kelas.destroy', $kelas->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Yakin hapus kelas ini?')"
                                    class="bg-red-700 text-white px-2 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </form>

                            <!-- Edit -->
                            <button 
                                @click="
                                    showEditModal = true;
                                    editId = {{ $kelas->id }};
                                    editName = '{{ addslashes($kelas->name) }}';
                                "
                                class="bg-yellow-500 text-white px-2 py-1 rounded text-xs">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal Tambah -->
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="showModal = false" class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Tambah Kelas</h2>
                <form action="{{ route('admin.kelas.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Nama Kelas</label>
                        <input type="text" name="name" required
                            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-blue-500 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div @click.away="showEditModal = false" class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Edit Kelas</h2>
                <form :action="'/admin/kelas/' + editId" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Nama Kelas</label>
                        <input type="text" name="name" x-model="editName" required
                            class="w-full border border-gray-300 rounded px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded bg-gray-300">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded bg-blue-500 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
