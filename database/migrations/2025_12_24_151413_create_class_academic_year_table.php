<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates pivot table to establish many-to-many relationship between classes and academic years.
     * This allows filtering classes by academic year.
     */
    public function up(): void
    {
        // Create pivot table
        Schema::create('class_academic_year', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('class_id')
                ->constrained('classes')
                ->onDelete('cascade');
            
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->onDelete('cascade');
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Prevent duplicate: one class can't be registered twice in the same year
            $table->unique(['class_id', 'academic_year_id']);
        });

        // Populate pivot table with existing data
        // Associate all existing classes with the active academic year
        $activeYear = DB::table('academic_years')->where('is_active', 1)->first();
        
        if ($activeYear) {
            $classes = DB::table('classes')->get();
            
            foreach ($classes as $class) {
                DB::table('class_academic_year')->insert([
                    'class_id' => $class->id,
                    'academic_year_id' => $activeYear->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_academic_year');
    }
};
