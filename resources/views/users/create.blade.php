<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
            <span class="iconify text-indigo-600 text-2xl mr-2" data-icon="mdi:account-plus"></span>
            {{ __('Tambah User') }}
        </h2>
    </x-slot>

    <div class="py-6 container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded-lg border border-gray-200 p-6">
            <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6" x-data="{ role: '', classId: '' }">
                @csrf

                <!-- Nama -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="name" id="name" required
                           class="w-full border  rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" required
                           class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" id="role" required x-model="role"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('role') border-red-500 @enderror">
                        <option value="" disabled selected>Pilih Role</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kelas (Khusus Wali Kelas) -->
                <div x-show="role == '2'" x-transition>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-1">Kelas (Khusus Wali Kelas)</label>
                    <select name="class_id" id="class_id" x-model="classId" x-bind:required="role == '2'"
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('class_id') border-red-500 @enderror">
                        <option value="">Pilih Kelas</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
             <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        autocomplete="new-password"
                        class="w-full borderrounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Konfirmasi Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        autocomplete="new-password"
                        class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 @error('password_confirmation') border-red-500 @enderror">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <!-- Tombol Submit -->
               <!-- Tombol Submit dan Kembali -->
<div class="flex justify-end space-x-4">
    <button type="button" onclick="window.history.back()"
            class="bg-gray-500 text-white px-6 py-2 rounded-lg border border-gray-600 hover:bg-gray-600 hover:shadow-md transition-all duration-200 flex items-center">
        <span class="iconify text-xl mr-2" data-icon="mdi:arrow-left"></span>
        Kembali
    </button>
    <button type="submit"
            class="bg-indigo-600 text-white px-6 py-2 rounded-lg border border-indigo-700 hover:bg-indigo-700 hover:shadow-md transition-all duration-200 flex items-center">
        <span class="iconify text-xl mr-2" data-icon="mdi:content-save"></span>
        Simpan
    </button>
</div>
            </form>
        </div>
    </div>
</x-app-layout>