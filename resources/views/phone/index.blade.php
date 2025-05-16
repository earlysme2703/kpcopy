<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 flex items-center">
            <span class="iconify text-indigo-600 text-2xl mr-2" data-icon="mdi:google-classroom"></span>
            Siswa {{ $class->name }}
        </h2>
    </x-slot>

    <div class="py-6" x-data="walikelas()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md shadow-sm">
                    <div class="flex items-center">
                        <span class="iconify h-5 w-5 mr-3 text-green-600" data-icon="mdi:check-circle"></span>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <span class="iconify text-indigo-600 text-xl mr-2" data-icon="mdi:account-group"></span>
                    Daftar Siswa
                </h3>
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Petunjuk:</span> Sebagai Wali Kelas, Anda dapat mengedit nomor telepon orang tua siswa.
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-56">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">NIS</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">Jenis Kelamin</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Orang Tua</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">No. HP Orang Tua</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($students as $i => $student)
                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 max-w-56 break-words">{{ $student->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-32 break-words">{{ $student->nis }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-28">
                                    @if($student->gender == 'L')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Laki-laki</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-pink-100 text-pink-800">Perempuan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-48 break-words">{{ $student->parent_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-40 break-words">
                                    {{ $student->parent_phone ? '0'.substr($student->parent_phone, 2) : '-' }}
                                </td>
                                <td class="px-2 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex justify-center">
                                        <button @click="openEditModal({{ json_encode($student) }})" 
                                                class="text-indigo-600 hover:text-indigo-900" title="Edit No HP">
                                            <span class="iconify text-xl" data-icon="mdi:pencil"></span>
                                        </button>
                                    </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data siswa yang tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Modal Edit Nomor Telepon -->
        <div x-show="openModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" x-cloak>
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.away="openModal = false">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <span class="iconify text-indigo-600 text-2xl mr-2" data-icon="mdi:pencil"></span>
                        Edit Nomor HP Orang Tua
                    </h3>
                    <p class="mb-4 text-gray-600">Siswa: <span x-text="form.name" class="font-medium"></span></p>

                    <form :action="formAction" method="POST" @submit="validateAndSubmit">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP Orang Tua <span class="text-red-500">*</span></label>
                                <input type="text" name="parent_phone" 
                                       class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150" 
                                       x-model="form.parent_phone" placeholder="083113355381" @input="formatPhoneNumber" required>
                                <div x-show="errors.parent_phone" class="text-red-500 text-xs mt-1" x-text="errors.parent_phone"></div>
                                <span class="text-xs text-gray-500 mt-1 block">Format: Tulis nomor tanpa awalan +62 (contoh: 083113355381)</span>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                            <button type="button" @click="openModal = false" 
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-all duration-200 font-medium">Batal</button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-indigo-600 border border-indigo-700 rounded-md text-white hover:bg-indigo-700 transition-all duration-200 font-medium flex items-center">
                                <span class="iconify text-xl mr-2" data-icon="mdi:content-save"></span>
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function walikelas() {
        return {
            openModal: false,
            form: {
                id: '',
                name: '',
                parent_phone: ''
            },
            errors: {
                parent_phone: ''
            },
            formAction: '',

            openEditModal(student) {
                this.form = {
                    id: student.id,
                    name: student.name,
                    parent_phone: student.parent_phone?.startsWith('62') 
                        ? '0' + student.parent_phone.slice(2) 
                        : student.parent_phone || ''
                };
                this.errors = { parent_phone: '' };
                this.formAction = '/siswa/' + student.id + '/update-phone';
                this.openModal = true;
            },

            formatPhoneNumber() {
                let phoneNumber = this.form.parent_phone.replace(/\D/g, '');
                if (phoneNumber.startsWith('0')) {
                    phoneNumber = '62' + phoneNumber.substring(1);
                } else if (!phoneNumber.startsWith('62') && phoneNumber) {
                    phoneNumber = '62' + phoneNumber;
                }
                this.form.parent_phone = phoneNumber;
            },

            validateAndSubmit(e) {
                let valid = true;
                this.errors = { parent_phone: '' };

                let phoneNumber = this.form.parent_phone.replace(/\D/g, '');
                if (phoneNumber.startsWith('0')) {
                    phoneNumber = '62' + phoneNumber.substring(1);
                } else if (!phoneNumber.startsWith('62') && phoneNumber) {
                    phoneNumber = '62' + phoneNumber;
                }

                if (!phoneNumber) {
                    this.errors.parent_phone = 'Nomor HP tidak boleh kosong';
                    valid = false;
                } else if (!phoneNumber.startsWith('62')) {
                    this.errors.parent_phone = 'Format nomor harus diawali 0 atau 62';
                    valid = false;
                } else if (phoneNumber.length < 11) {
                    this.errors.parent_phone = 'Nomor terlalu pendek';
                    valid = false;
                }

                if (valid) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'parent_phone';
                    hiddenInput.value = phoneNumber;
                    e.target.appendChild(hiddenInput);

                    const visibleInput = e.target.querySelector('input[name="parent_phone"]');
                    if (visibleInput) {
                        visibleInput.name = 'parent_phone_display';
                    }
                } else {
                    e.preventDefault();
                }
            }
        }
    }
    </script>
</x-app-layout>