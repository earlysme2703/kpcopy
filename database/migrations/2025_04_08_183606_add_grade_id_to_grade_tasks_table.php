<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGradeIdToGradeTasksTable extends Migration
{
    public function up()
    {
        Schema::table('grade_tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('grades_id')->after('type');
            $table->foreign('grades_id')->references('id')->on('grades')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('grade_tasks', function (Blueprint $table) {
            $table->dropForeign(['grades_id']);
            $table->dropColumn('grades_id');
        });
    }
}