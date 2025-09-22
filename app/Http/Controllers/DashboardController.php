<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GradeTask;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Dashboard Admin
        if ($user->hasRole('Admin')) {
            $totalWaliKelas = User::where('role_id', 2)->count();
            $totalSiswa = Student::count();
            $totalKelas = ClassModel::count();
            $totalMapel = Subject::count();

            return view('dashboards.admin', compact(
                'totalWaliKelas',
                'totalSiswa',
                'totalKelas',
                'totalMapel'
            ));
        }

        // Dashboard Wali Kelas
        if ($user->hasRole('Wali Kelas')) {
            $class_id = $user->class_id;
            $class = ClassModel::find($class_id);
            if (!$class) {
                abort(404, 'Kelas tidak ditemukan.');
            }
            $className = $class->name;

            $totalStudents = Student::where('class_id', $class_id)->count();
            $subjects = Subject::all();

            $subjectStats = [];
            foreach ($subjects as $subject) {
                $statsData = GradeTask::where('subject_id', $subject->id)
                    ->whereIn('student_id', function ($query) use ($class_id) {
                        $query->select('id')->from('students')->where('class_id', $class_id);
                    })
                    ->selectRaw('AVG(score) as average, MAX(score) as highest, MIN(score) as lowest, COUNT(*) as count')
                    ->first();

                if ($statsData && $statsData->count > 0) {
                    $subjectStats[$subject->id] = [
                        'name' => $subject->name,
                        'average' => round($statsData->average, 1),
                        'highest' => $statsData->highest,
                        'lowest' => $statsData->lowest,
                        'count' => $statsData->count
                    ];
                }
            }

            $overallStats = GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                $query->select('id')->from('students')->where('class_id', $class_id);
            })
                ->selectRaw('AVG(score) as average, MAX(score) as highest, MIN(score) as lowest, COUNT(*) as count, COUNT(DISTINCT student_id) as student_count')
                ->first();

            $gradeDistribution = [
                'sangat_baik' => GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                    $query->select('id')->from('students')->where('class_id', $class_id);
                })->where('score', '>=', 90)->count(),
                'baik' => GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                    $query->select('id')->from('students')->where('class_id', $class_id);
                })->whereBetween('score', [75, 89.99])->count(),
                'cukup' => GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                    $query->select('id')->from('students')->where('class_id', $class_id);
                })->whereBetween('score', [60, 74.99])->count(),
                'perlu_perbaikan' => GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                    $query->select('id')->from('students')->where('class_id', $class_id);
                })->where('score', '<', 60)->count(),
            ];

            $topStudents = GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                $query->select('id')->from('students')->where('class_id', $class_id);
            })
                ->join('students', 'grade_tasks.student_id', '=', 'students.id')
                ->select('students.id', 'students.name', DB::raw('AVG(grade_tasks.score) as average_score'))
                ->groupBy('students.id', 'students.name')
                ->orderByDesc('average_score')
                ->limit(5)
                ->get();

            $lowStudents = GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                $query->select('id')->from('students')->where('class_id', $class_id);
            })
                ->join('students', 'grade_tasks.student_id', '=', 'students.id')
                ->select('students.id', 'students.name', DB::raw('AVG(grade_tasks.score) as average_score'))
                ->groupBy('students.id', 'students.name')
                ->orderBy('average_score')
                ->limit(5)
                ->get();

            $recentActivities = GradeTask::whereIn('student_id', function ($query) use ($class_id) {
                $query->select('id')->from('students')->where('class_id', $class_id);
            })
                ->join('students', 'grade_tasks.student_id', '=', 'students.id')
                ->join('subjects', 'grade_tasks.subject_id', '=', 'subjects.id')
                ->select(
                    'grade_tasks.id',
                    'grade_tasks.task_name',
                    'grade_tasks.score',
                    'grade_tasks.created_at',
                    'students.name as student_name',
                    'subjects.name as subject_name'
                )
                ->orderByDesc('grade_tasks.created_at')
                ->limit(10)
                ->get();

            $studentsWithoutGrades = Student::where('class_id', $class_id)
                ->whereNotIn('id', function ($query) {
                    $query->select('student_id')->from('grade_tasks')->distinct();
                })
                ->count();

            return view('dashboards.walikelas', compact(
                'className',
                'totalStudents',
                'overallStats',
                'subjectStats',
                'gradeDistribution',
                'topStudents',
                'lowStudents',
                'recentActivities',
                'studentsWithoutGrades'
            ));
        }

        // Dashboard Guru Mata Pelajaran
        if ($user->hasRole('Guru Mata Pelajaran')) {
            $subject_id = $user->subject_id;
            $subject = Subject::find($subject_id);
            if (!$subject) {
                abort(404, 'Mata pelajaran tidak ditemukan.');
            }
            $subjectName = $subject->name;

            // Total siswa yang punya nilai untuk mata pelajaran ini
            $totalStudents = GradeTask::where('subject_id', $subject_id)
                ->distinct('student_id')
                ->count('student_id');

            // Statistik nilai keseluruhan
            $overallStats = GradeTask::where('subject_id', $subject_id)
                ->selectRaw('AVG(score) as average, MAX(score) as highest, MIN(score) as lowest, COUNT(*) as count')
                ->first();

            // Distribusi nilai untuk pie chart
            $gradeDistribution = [
                'sangat_baik' => GradeTask::where('subject_id', $subject_id)->where('score', '>=', 90)->count(),
                'baik' => GradeTask::where('subject_id', $subject_id)->whereBetween('score', [75, 89.99])->count(),
                'cukup' => GradeTask::where('subject_id', $subject_id)->whereBetween('score', [60, 74.99])->count(),
                'perlu_perbaikan' => GradeTask::where('subject_id', $subject_id)->where('score', '<', 60)->count(),
            ];

            // Aktivitas input nilai terbaru
            $recentActivities = GradeTask::where('subject_id', $subject_id)
                ->join('students', 'grade_tasks.student_id', '=', 'students.id')
                ->select(
                    'grade_tasks.id',
                    'grade_tasks.task_name',
                    'grade_tasks.score',
                    'grade_tasks.created_at',
                    'students.name as student_name'
                )
                ->orderByDesc('grade_tasks.created_at')
                ->limit(10)
                ->get();

            // Siswa tanpa nilai untuk mata pelajaran ini
            $studentsWithoutGrades = Student::whereNotIn('id', function ($query) use ($subject_id) {
                $query->select('student_id')->from('grade_tasks')->where('subject_id', $subject_id)->distinct();
            })
                ->whereIn('class_id', function ($query) use ($subject_id) {
                    $query->select('class_id')->from('students')
                        ->whereIn('id', function ($subQuery) use ($subject_id) {
                            $subQuery->select('student_id')->from('grade_tasks')->where('subject_id', $subject_id);
                        });
                })
                ->count();

            // Statistik nilai per tugas untuk bar chart
            $taskStats = GradeTask::where('subject_id', $subject_id)
                ->groupBy('task_name')
                ->selectRaw('task_name as name, AVG(score) as average')
                ->get();

            return view('dashboards.guru', compact(
                'subjectName',
                'totalStudents',
                'overallStats',
                'gradeDistribution',
                'recentActivities',
                'studentsWithoutGrades',
                'taskStats'
            ));
        }

        abort(403, 'Unauthorized action.');
    }
}