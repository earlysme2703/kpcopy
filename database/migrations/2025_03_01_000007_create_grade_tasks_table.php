<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradeTasksTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grade_tasks', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->enum('type', ['written', 'observation', 'sumatif']); // jenis tugas
            $table->unsignedBigInteger('grades_id'); // relasi ke tabel grades
            $table->decimal('score', 5, 2);
            $table->timestamps();

            // Foreign key opsional tergantung kamu aktifkan atau tidak
            $table->foreign('grades_id')->references('id')->on('grades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_tasks');
    }
}


