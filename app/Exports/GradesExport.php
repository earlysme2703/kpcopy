<?php
// app/Exports/GradesExport.php

namespace App\Exports;

use App\Models\Grade;
use App\Models\GradeTask;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Classes;
use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class GradesExport implements FromView
{
    protected $subject_id;
    protected $semester;
    protected $class_id;

    public function __construct($subject_id, $semester, $class_id)
    {
        $this->subject_id = $subject_id;
        $this->semester = $semester;
        $this->class_id = $class_id;
    }

    public function view(): View
    {
        $subject = Subject::find($this->subject_id);
        $class = ClassModel::find($this->class_id);
        $data = $this->getGradeData();
        
        return view('exports.grade_excel', [
            'data' => $data,
            'subject' => $subject,
            'class' => $class,
            'semester' => $this->semester,
            'year' => date('Y'),
        ]);
    }

    private function getGradeData()
    {
        $students = Student::where('class_id', $this->class_id)->get();
        $result = [];
        
        foreach ($students as $student) {
            $studentData = [
                'student_id' => $student->id,
                'student_number' => $student->student_number,
                'name' => $student->name,
                'written' => [],
                'observation' => [],
                'homework' => [],
                'average_written' => 0,
                'average_observation' => 0,
                'average_homework' => 0,
                'midterm_score' => 0,
                'final_exam_score' => 0,
                'final_score' => 0,
                'grade_letter' => ''
            ];
            
            // Ambil nilai tugas individu
            $tasks = GradeTask::where('student_id', $student->id)
                            ->where('subject_id', $this->subject_id)
                            ->get();
                            
            foreach ($tasks as $task) {
                if ($task->type == 'written') {
                    $studentData['written'][] = $task->score;
                } elseif ($task->type == 'observation') {
                    $studentData['observation'][] = $task->score;
                } elseif ($task->type == 'homework') {
                    $studentData['homework'][] = $task->score;
                }
            }
            
            // Ambil ringkasan nilai
            $grade = Grade::where('student_id', $student->id)
                         ->where('subject_id', $this->subject_id)
                         ->where('semester', $this->semester)
                         ->first();
                         
            if ($grade) {
                $studentData['average_written'] = $grade->average_written;
                $studentData['average_observation'] = $grade->average_observation;
                $studentData['average_homework'] = $grade->average_homework;
                $studentData['midterm_score'] = $grade->midterm_score;
                $studentData['final_exam_score'] = $grade->final_exam_score;
                $studentData['final_score'] = $grade->final_score;
                $studentData['grade_letter'] = $grade->grade_letter;
            }
            
            // Pastikan array memiliki data untuk semua kolom
            $studentData['written'] = array_pad($studentData['written'], 7, '');
            $studentData['observation'] = array_pad($studentData['observation'], 4, '');
            $studentData['homework'] = array_pad($studentData['homework'], 5, '');
            
            $result[] = $studentData;
        }
        
        return $result;
    }
    
    private function getDescription($grade)
    {
        switch ($grade) {
            case 'A':
                return 'Sangat Baik';
            case 'B':
                return 'Baik';
            case 'C':
                return 'Cukup';
            case 'D':
                return 'Perlu Bimbingan';
            default:
                return '';
        }
    }
}