<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        Subject::create(['name' => 'Matematika']);
        Subject::create(['name' => 'Bahasa Indonesia']);
        Subject::create(['name' => 'Bahasa Daerah']);
        Subject::create(['name' => 'Pendidikan Pancasila']);
        Subject::create(['name' => 'Bahasa Inggris']);
        Subject::create(['name' => 'Seni Budaya dan Prakarya']);
        Subject::create(['name' => 'Pendidikan Agama Islam dan Budi Pekerti']);
        Subject::create(['name' => 'Ilmu Pengetahuan Alam dan Sosial']);
        Subject::create(['name' => 'PJOK']);
    }
}
