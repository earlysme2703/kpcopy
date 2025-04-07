<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grade_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('grades_id')->after('subject_id');
            $table->enum('type', ['written', 'observation', 'homework'])->after('task_name');

            $table->foreign('grades_id')->references('id')->on('grades')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('grade_tasks', function (Blueprint $table) {
            $table->dropForeign(['grades_id']);
            $table->dropColumn(['grades_id', 'type']);
        });
    }
};
