<?php

namespace App\Observers;

use App\Models\Grade;

class GradeObserver
{
    /**
     * Handle the Grade "updated" event.
     */
    public function updated(Grade $grade): void
    {
        $this->updateFinalScore($grade);
    }

    /**
     * Update the final_score in the grades table.
     */
    protected function updateFinalScore(Grade $grade): void
    {
        $components = array_filter([
            $grade->average_written,
            $grade->average_observation,
            $grade->average_homework,
            $grade->midterm_score,
            $grade->final_exam_score
        ], fn($value) => !is_null($value));

        $finalScore = !empty($components) ? array_sum($components) / count($components) : 0;

        $grade->update([
            'final_score' => $finalScore,
        ]);
    }
}