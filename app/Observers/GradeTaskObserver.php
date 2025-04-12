<?php

namespace App\Observers;

use App\Models\GradeTask;
use App\Models\Grade;

class GradeTaskObserver
{
    /**
     * Handle the GradeTask "created" event.
     */
    public function created(GradeTask $gradeTask): void
    {
        $this->updateAverages($gradeTask);
    }

    /**
     * Handle the GradeTask "updated" event.
     */
    public function updated(GradeTask $gradeTask): void
    {
        $this->updateAverages($gradeTask);
    }

    /**
     * Handle the GradeTask "deleted" event.
     */
    public function deleted(GradeTask $gradeTask): void
    {
        $this->updateAverages($gradeTask);
    }

    /**
     * Update the averages and final_score in the grades table.
     */
    protected function updateAverages(GradeTask $gradeTask): void
    {
        $grade = Grade::find($gradeTask->grades_id);
        if ($grade) {
            $tasks = $grade->gradeTasks;

            // Array untuk menghitung rata-rata
            $writtenScores = $tasks->where('type', 'written')->pluck('score')->toArray();
            $observationScores = $tasks->where('type', 'observation')->pluck('score')->toArray();
            $homeworkScores = $tasks->where('type', 'homework')->pluck('score')->toArray();

            // Hitung rata-rata
            $averageWritten = !empty($writtenScores) ? array_sum($writtenScores) / count($writtenScores) : null;
            $averageObservation = !empty($observationScores) ? array_sum($observationScores) / count($observationScores) : null;
            $averageHomework = !empty($homeworkScores) ? array_sum($homeworkScores) / count($homeworkScores) : null;

            // Hitung final_score
            $components = array_filter([
                $averageWritten,
                $averageObservation,
                $averageHomework,
                $grade->midterm_score,
                $grade->final_exam_score
            ], fn($value) => !is_null($value));

            $finalScore = !empty($components) ? array_sum($components) / count($components) : 0;

            // Update kolom rata-rata dan final_score di tabel grades
            $grade->update([
                'average_written' => $averageWritten,
                'average_observation' => $averageObservation,
                'average_homework' => $averageHomework,
                'final_score' => $finalScore,
            ]);
        }
    }
}