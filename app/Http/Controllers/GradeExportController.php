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
use Illuminate\Support\Facades\Log;

class GradeExportController extends Controller
{
    public function index()
    {
        $class = null;
        if (auth()->user()->class_id) {
            $class = ClassModel::find(auth()->user()->class_id);
        }
        
        $subjects = Subject::all();
        $studentsData = [];
        $selectedSubject = null;
        $selectedSemester = null;
        
        if (request()->has('subject_id') && request()->has('semester')) {
            $selectedSubject = Subject::find(request('subject_id'));
            $selectedSemester = request('semester');
            
            if ($class && $selectedSubject) {
                $students = Student::where('class_id', $class->id)->get();
                
                foreach ($students as $student) {
                    $studentData = [
                        'student_id' => $student->id,
                        'student_number' => $student->nis,
                        'name' => $student->name,
                        'written' => array_fill(0, 5, '-'),
                        'observation' => array_fill(0, 5, '-'),
                        'homework' => array_fill(0, 5, '-'),
                        'grade_details' => [
                            'average_written' => null,
                            'average_observation' => null,
                            'average_homework' => null,
                            'midterm_score' => null,
                            'final_exam_score' => null,
                            'final_score' => null,
                            'grade_letter' => '-'
                        ]
                    ];
                    
                    // Ambil grade untuk semester tertentu
                    $grade = Grade::where('student_id', $student->id)
                             ->where('subject_id', $selectedSubject->id)
                             ->where('semester', $selectedSemester)
                             ->first();
                    
                    if ($grade) {
                        // Ambil tugas individu melalui relasi
                        $tasks = $grade->gradeTasks;
                        
                        // Log untuk debugging
                        Log::info("Grade for student {$student->name}", ['grade' => $grade->toArray()]);
                        Log::info("Tasks for student {$student->name}", ['tasks' => $tasks->toArray()]);
                        
                        foreach ($tasks as $task) {
                            if (preg_match('/Tertulis (\d+)/i', $task->task_name, $matches)) {
                                $index = min((int)$matches[1] - 1, 4);
                                $studentData['written'][$index] = $task->score;
                            } elseif (preg_match('/Pengamatan (\d+)/i', $task->task_name, $matches)) {
                                $index = min((int)$matches[1] - 1, 4);
                                $studentData['observation'][$index] = $task->score;
                            } elseif (preg_match('/Tugas (\d+)/i', $task->task_name, $matches)) {
                                $index = min((int)$matches[1] - 1, 4);
                                $studentData['homework'][$index] = $task->score;
                            }
                        }
                        
                        $studentData['grade_details'] = [
                            'average_written' => $grade->average_written,
                            'average_observation' => $grade->average_observation,
                            'average_homework' => $grade->average_homework,
                            'midterm_score' => $grade->midterm_score,
                            'final_exam_score' => $grade->final_exam_score,
                            'final_score' => $grade->final_score,
                            'grade_letter' => $grade->grade_letter
                        ];
                    } else {
                        // Log jika grade tidak ditemukan
                        Log::warning("No grade found for student {$student->name}", [
                            'student_id' => $student->id,
                            'subject_id' => $selectedSubject->id,
                            'semester' => $selectedSemester
                        ]);
                    }
                    
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
                'written' => array_fill(0, 5, '-'),
                'observation' => array_fill(0, 5, '-'),
                'homework' => array_fill(0, 5, '-'),
                'average_written' => null,
                'average_observation' => null,
                'average_homework' => null,
                'midterm_score' => null,
                'final_exam_score' => null,
                'final_score' => null,
                'grade_letter' => '-'
            ];
            
            $grade = Grade::where('student_id', $student->id)
                         ->where('subject_id', $subject_id)
                         ->where('semester', $semester)
                         ->first();
                         
            if ($grade) {
                $tasks = $grade->gradeTasks;
                
                // Log untuk debugging
                Log::info("Grade for student {$student->name}", ['grade' => $grade->toArray()]);
                Log::info("Tasks for student {$student->name}", ['tasks' => $tasks->toArray()]);
                
                foreach ($tasks as $task) {
                    if (preg_match('/Tertulis (\d+)/i', $task->task_name, $matches)) {
                        $index = min((int)$matches[1] - 1, 4);
                        $studentData['written'][$index] = $task->score;
                    } elseif (preg_match('/Pengamatan (\d+)/i', $task->task_name, $matches)) {
                        $index = min((int)$matches[1] - 1, 4);
                        $studentData['observation'][$index] = $task->score;
                    } elseif (preg_match('/Tugas (\d+)/i', $task->task_name, $matches)) {
                        $index = min((int)$matches[1] - 1, 4);
                        $studentData['homework'][$index] = $task->score;
                    }
                }
                
                $studentData['average_written'] = $grade->average_written;
                $studentData['average_observation'] = $grade->average_observation;
                $studentData['average_homework'] = $grade->average_homework;
                $studentData['midterm_score'] = $grade->midterm_score;
                $studentData['final_exam_score'] = $grade->final_exam_score;
                $studentData['final_score'] = $grade->final_score;
                $studentData['grade_letter'] = $grade->grade_letter;
            }
            
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