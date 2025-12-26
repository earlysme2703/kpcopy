<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentClass;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $student1 = Student::create([
            'name' => 'Siswa Titipan Dara',
            'nis' => 20240670,
            'gender' => 'L',
            'parent_phone' => 6283113355381,
            'parent_name' => 'Rahayu Rina Wahyuni',
            'birth_place' => 'Padang',
            'birth_date' => '2004-12-02',
        ]);

        $student2 = Student::create([
            'name' => 'Siswa Titipan Early',
            'nis' => 20240671,
            'gender' => 'P',
            'parent_phone' => 6283113355381,
            'parent_name' => 'Rahayu Rina Wahyuni',
            'birth_place' => 'Padang',
            'birth_date' => '2004-12-02',
        ]);

        // Student-Class relationships
        StudentClass::create([
            'student_id' => 1,
            'class_id' => 1,
            'academic_year_id' => 2,
        ]);

        StudentClass::create([
            'student_id' => 2,
            'class_id' => 3,
            'academic_year_id' => 2,
        ]);

        StudentClass::create([
            'student_id' => 1,
            'class_id' => 2,
            'academic_year_id' => 1,
        ]);

    }
}
