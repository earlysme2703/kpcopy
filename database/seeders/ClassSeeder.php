<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassModel;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        ClassModel::create(['name' => 'Kelas 1']);
        ClassModel::create(['name' => 'Kelas 2']);
        ClassModel::create(['name' => 'Kelas 3']);
        ClassModel::create(['name' => 'Kelas 4']);
        ClassModel::create(['name' => 'Kelas 5']);
        ClassModel::create(['name' => 'Kelas 6']);
        ClassModel::create(['name' => 'Kelas 1B']);
    }
}
