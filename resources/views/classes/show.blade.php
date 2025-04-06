<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Kelas') }}
        </h2>
    </x-slot>

<h2 class="text-xl font-bold mb-4">Detail Kelas: {{ $kelas->name }}</h2>

<div class="mb-6">
    <p><strong>Wali Kelas:</strong> {{ $kelas->waliKelas?->name ?? '-' }}</p>
</div>

<h3 class="text-lg font-semibold mb-2">Daftar Siswa</h3>
<table class="table-auto w-full border text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="px-4 py-2">No</th>
            <th class="px-4 py-2">Nama</th>
            <th class="px-4 py-2">NIS</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($kelas->students as $index => $siswa)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $index + 1 }}</td>
                <td class="px-4 py-2">{{ $siswa->name }}</td>
                <td class="px-4 py-2">{{ $siswa->nis }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="px-4 py-2 text-center text-gray-500">Belum ada siswa.</td>
            </tr>
        @endforelse
    </tbody>
</table>
</x-app-layout>
