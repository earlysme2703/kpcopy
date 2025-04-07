<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Tambah kolom baru
            $table->decimal('average_written', 5, 2)->nullable()->after('semester');
            $table->decimal('average_observation', 5, 2)->nullable()->after('average_written');
            $table->decimal('average_homework', 5, 2)->nullable()->after('average_observation');
            $table->string('description')->nullable()->after('grade_letter');

            // Opsional: hapus kolom lama average_task_score
            $table->dropColumn('average_task_score');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            // Kembalikan ke kondisi sebelumnya kalau rollback
            $table->decimal('average_task_score', 5, 2)->nullable()->after('semester');
            $table->dropColumn([
                'average_written',
                'average_observation',
                'average_homework',
                'description',
            ]);
        });
    }
};
