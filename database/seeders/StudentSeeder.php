<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate students (otomatis reset ID juga)
        DB::table('students')->truncate();
        
        // Nyalakan lagi foreign key check
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // Data manual yang sudah ada
        $manualStudents = [
            [
                'name' => 'Siswa 1',
                'nis' => '1001',
                'gender' => 'L',
                'parent_name' => 'Ortu Siswa 1',
                'parent_phone' => '6283113355381',
                'birth_place' => 'Bandung',
                'birth_date' => '2013-04-15',
                'class_id' => 3,
            ],
            [
                'name' => 'Siswa 2',
                'nis' => '1002',
                'gender' => 'P',
                'parent_name' => 'Ortu Siswa 2',
                'parent_phone' => '6281462260074',
                'birth_place' => 'Bandung',
                'birth_date' => '2013-05-10',
                'class_id' => 3,
            ],
            [
                'name' => 'Siswa 3',
                'nis' => '1003',
                'gender' => 'L',
                'parent_name' => 'Ortu Siswa 3',
                'parent_phone' => '6283113355381',
                'birth_place' => 'Cimahi',
                'birth_date' => '2013-06-20',
                'class_id' => 3,
            ],
        ];

        // Tambahkan data Faker
        $faker = \Faker\Factory::create('id_ID');
        $fakeStudents = [];
        
        // Generate 47 data tambahan (total 50 data)
        for ($i = 0; $i < 47; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            $birthDate = $faker->dateTimeBetween('-18 years', '-15 years')->format('Y-m-d');
            
            $fakeStudents[] = [
                'name' => $faker->name($gender == 'L' ? 'male' : 'female'),
                'nis' => $faker->unique()->numerify('1###'), // Dimulai dengan 1 dan 3 digit acak
                'gender' => $gender,
                'class_id' => $faker->numberBetween(1, 6),
                'parent_phone' => '62' . $faker->numerify('###########'), // Format nomor Indonesia
                'parent_name' => $faker->name,
                'birth_place' => $faker->city,
                'birth_date' => $birthDate,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Gabungkan data manual dan faker
        $allStudents = array_merge($manualStudents, $fakeStudents);
        
        // Insert semua data
        foreach ($allStudents as $student) {
            Student::create($student);
        }
    }
}