<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
        public function run()
    {
        // Buat Role
        Role::firstOrCreate(['id' => 1, 'name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['id' => 2, 'name' => 'Wali Kelas', 'guard_name' => 'web']);
        Role::firstOrCreate(['id' => 3, 'name' => 'Guru Mata Pelajaran', 'guard_name' => 'web']);

        // Buat Permission (meskipun belum digunakan)
        $permissions = [
            'kelola siswa',
            'kelola mapel',
            'kelola kelas',
            'kelola pengguna',
            'kelola guru',
            'kelola nilai',
            'kirim notifikasi orang tua',
            'kelola rapot',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permission ke role
        $adminRole = Role::find(1);
        $adminRole->syncPermissions(
            [
                'kelola siswa',
                'kelola mapel',
                'kelola kelas',
                'kelola pengguna',
                'kelola guru',
            ]
        );

        $waliKelasRole = Role::find(2);
        $waliKelasRole->syncPermissions([
            'kelola nilai',
            'kirim notifikasi orang tua',
            'kelola rapot',
        ]);

        $guruMapelRole = Role::find(3);
        $guruMapelRole->syncPermissions([
            'kelola nilai',
        ]);
    }
}
