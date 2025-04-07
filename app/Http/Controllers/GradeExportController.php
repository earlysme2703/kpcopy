<?php
// app/Http/Controllers/GradeExportController.php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeTask;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\GradesExport;
use App\Models\ClassModel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class GradeExportController extends Controller
{

    public function index()
    {
        // Get the class for the logged-in class teacher based on class_id
        $class = null;
        if (auth()->user()->class_id) {
            $class = ClassModel::find(auth()->user()->class_id);
        }
        
        $subjects = Subject::all();
        $studentsData = [];
        $selectedSubject = null;
        $selectedSemester = null;
        
        // Check if filter is applied via request parameters
        if (request()->has('subject_id') && request()->has('semester')) {
            $selectedSubject = Subject::find(request('subject_id'));
            $selectedSemester = request('semester');
            
            if ($class && $selectedSubject) {
                // Get students and their grades for the selected subject and semester
                $students = Student::where('class_id', $class->id)->get();
                
                foreach ($students as $student) {
                    $studentData = [
                        'student_id' => $student->id,
                        'student_number' => $student->nis,
                        'name' => $student->name,
                        'grade_details' => []
                    ];
                    
                    // Get the detailed grade data for this student
                    $grade = Grade::where('student_id', $student->id)
                             ->where('subject_id', $selectedSubject->id)
                             ->where('semester', $selectedSemester)
                             ->first();
                    
                    if ($grade) {
                        $studentData['grade_details'] = [
                            'average_written' => $grade->average_written,
                            'average_observation' => $grade->average_observation,
                            'average_homework' => $grade->average_homework,
                            'midterm_score' => $grade->midterm_score,
                            'final_exam_score' => $grade->final_exam_score,
                            'final_score' => $grade->final_score,
                            'grade_letter' => $grade->grade_letter
                        ];
                    }
                    
                    // Get the individual task scores
                    $tasks = GradeTask::where('student_id', $student->id)
                            ->where('subject_id', $selectedSubject->id)
                            ->get();
                    
                    $written = [];
                    $observation = [];
                    $homework = [];
                    
                    foreach ($tasks as $task) {
                        if ($task->type == 'written') {
                            $written[] = $task->score;
                        } elseif ($task->type == 'observation') {
                            $observation[] = $task->score;
                        } elseif ($task->type == 'homework') {
                            $homework[] = $task->score;
                        }
                    }
                    
                    // Pad arrays to ensure consistent columns
                    $studentData['written'] = array_pad($written, 7, '');
                    $studentData['observation'] = array_pad($observation, 4, '');
                    $studentData['homework'] = array_pad($homework, 5, '');
                    
                    $studentsData[] = $studentData;
                }
            }
        }
        
        return view('exports.index', compact('class', 'subjects', 'studentsData', 'selectedSubject', 'selectedSemester'));
    }
    public function exportPDF(Request $request)
    {
        $subject_id = $request->subject_id;
        $semester = $request->semester;
        $class_id = $request->class_id;
        
        $subject = Subject::find($subject_id);
        $class = ClassModel::find($class_id);
        $data = $this->getGradeData($subject_id, $semester, $class_id);
        
        $pdf = PDF::loadView('exports.grade_pdf', [
            'data' => $data,
            'subject' => $subject,
            'class' => $class,
            'semester' => $semester,
            'year' => date('Y'),
        ])->setPaper('a4', 'landscape'); 
        
        return $pdf->download('daftar_nilai_' . $subject->name . '.pdf');
    }
    
    public function exportExcel(Request $request)
    {
        $subject_id = $request->subject_id;
        $semester = $request->semester;
        $class_id = $request->class_id;
        
        $subject = Subject::find($subject_id);
        $class = ClassModel::find($class_id);
        
        return Excel::download(new GradesExport($subject_id, $semester, $class_id), 'daftar_nilai_' . $subject->name . '.xlsx');
    }
    
    public function generateExport(Request $request)
    {
        if ($request->export_type == 'pdf') {
            return $this->exportPDF($request);
        } else {
            return $this->exportExcel($request);
        }
    }
    
    private function getGradeData($subject_id, $semester, $class_id)
    {
        $students = Student::where('class_id', $class_id)->get();
        $result = [];
        
        foreach ($students as $student) {
            $studentData = [
                'student_id' => $student->id,
                'student_number' => $student->nis,
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
                            ->where('subject_id', $subject_id)
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
                         ->where('subject_id', $subject_id)
                         ->where('semester', $semester)
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