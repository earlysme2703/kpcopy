<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('semester')->after('subject_id'); // 'Odd' or 'Even'
            $table->decimal('average_task_score', 5, 2)->nullable()->after('semester');
            $table->decimal('midterm_score', 5, 2)->nullable()->after('average_task_score');
            $table->decimal('final_exam_score', 5, 2)->nullable()->after('midterm_score');
            $table->string('grade_letter')->nullable()->after('final_score'); // A, B, C, etc.
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn([
                'semester',
                'average_task_score',
                'midterm_score',
                'final_exam_score',
                'grade_letter',
            ]);
        });
    }
};
