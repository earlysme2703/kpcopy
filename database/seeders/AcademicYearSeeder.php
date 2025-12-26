<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        AcademicYear::create([
            'name' => '2025/2026',
            'is_active' => 1,
        ]);

        AcademicYear::create([
            'name' => '2024/2025',
            'is_active' => 0,
        ]);

        AcademicYear::create([
            'name' => '2026/2027',
            'is_active' => 0,
        ]);

    }
}
