<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">Siswa Kelas {{ $class->name }}</h2>
    </x-slot>

    <div class="py-6" x-data="studentManager()">
        <div class="flex justify-end mb-4">
            <button @click="openAddModal()" class="bg-blue-600 text-white px-4 py-2 rounded shadow">
                Tambah Siswa
            </button>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-left">
                        <th class="p-2">No</th>
                        <th class="p-2">Nama</th>
                        <th class="p-2">NIS</th>
                        <th class="p-2">Jenis Kelamin</th>
                        <th class="p-2">Orang Tua</th>
                        <th class="p-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $i => $student)
                        <tr>
                            <td class="p-2">{{ $i + 1 }}</td>
                            <td class="p-2">{{ $student->name }}</td>
                            <td class="p-2">{{ $student->nis }}</td>
                            <td class="p-2">{{ $student->gender }}</td>
                            <td class="p-2">{{ $student->parent_name }}</td>
                            <td class="p-2">
                                <button @click="openEditModal({{ json_encode($student) }})" class="text-blue-500">Edit</button>
                                <form action="{{ route('admin.siswa.destroy', $student->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus siswa ini?')" class="text-red-500 ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Modal Tambah/Edit --}}
        <div x-show="openModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded-lg w-full max-w-lg relative">
                <h3 class="text-lg font-bold mb-4" x-text="isEdit ? 'Edit Siswa' : 'Tambah Siswa'"></h3>

                <form :action="formAction" method="POST">
                    @csrf
                    <input type="hidden" name="_method" x-bind:value="isEdit ? 'PUT' : 'POST'">
                    <input type="hidden" name="class_id" value="{{ $class->id }}">

                    <div class="mb-2">
                        <label class="block">Nama</label>
                        <input type="text" name="name" class="w-full border rounded p-2" x-model="form.name">
                    </div>
                    <div class="mb-2">
                        <label class="block">NIS</label>
                        <input type="text" name="nis" class="w-full border rounded p-2" x-model="form.nis">
                    </div>
                    <div class="mb-2">
                        <label class="block">Jenis Kelamin</label>
                        <select name="gender" class="w-full border rounded p-2" x-model="form.gender">
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="block">Nama Orang Tua</label>
                        <input type="text" name="parent_name" class="w-full border rounded p-2" x-model="form.parent_name">
                    </div>
                    <div class="mb-2">
                        <label class="block">Tempat Lahir</label>
                        <input type="text" name="birth_place" class="w-full border rounded p-2" x-model="form.birth_place">
                    </div>
                    <div class="mb-2">
                        <label class="block">Tanggal Lahir</label>
                        <input type="date" name="birth_date" class="w-full border rounded p-2" x-model="form.birth_date">
                    </div>
                    <div class="mb-2">
                        <label class="block">Nomor HP Orang Tua</label>
                        <input type="text" name="parent_phone" class="w-full border rounded p-2" x-model="form.parent_phone" placeholder="62812xxxxxxxx">
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" @click="openModal = false" class="bg-gray-300 px-4 py-2 rounded">Batal</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function studentManager() {
            return {
                openModal: false,
                isEdit: false,
                form: {
                    name: '',
                    nis: '',
                    gender: 'L',
                    parent_name: '',
                    birth_place: '',
                    birth_date: '',
                    parent_phone: ''
                },
                formAction: '{{ route('admin.siswa.store') }}',
                
                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        name: '',
                        nis: '',
                        gender: 'L',
                        parent_name: '',
                        birth_place: '',
                        birth_date: '',
                        parent_phone: ''
                    };
                    this.formAction = '{{ route('admin.siswa.store') }}';
                    this.openModal = true;
                },
                
                openEditModal(student) {
                    this.isEdit = true;
                    this.form = { ...student };
                    this.formAction = '/admin/siswa/' + student.id;
                    this.openModal = true;
                }
            }
        }
    </script>
</x-app-layout>