<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        Permission::create(['name' => 'kelola siswa', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola mapel', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola kelas', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola pengguna', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola guru', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola nilai', 'guard_name' => 'web']);
        Permission::create(['name' => 'kirim notifikasi orang tua', 'guard_name' => 'web']);
        Permission::create(['name' => 'kelola rapot', 'guard_name' => 'web']);

        // Create Roles
        $role1 = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $role2 = Role::create(['name' => 'Wali Kelas', 'guard_name' => 'web']);
        $role3 = Role::create(['name' => 'Guru Mata Pelajaran', 'guard_name' => 'web']);

        // Assign Permissions to Roles
        $role1->givePermissionTo('kelola siswa');
        $role1->givePermissionTo('kelola mapel');
        $role1->givePermissionTo('kelola kelas');
        $role1->givePermissionTo('kelola pengguna');
        $role1->givePermissionTo('kelola guru');
        $role2->givePermissionTo('kelola nilai');
        $role3->givePermissionTo('kelola nilai');
        $role2->givePermissionTo('kirim notifikasi orang tua');
        $role2->givePermissionTo('kelola rapot');
    }
}
