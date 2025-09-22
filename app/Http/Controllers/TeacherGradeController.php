<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\GradeTask;
use App\Models\Subject;
use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherGradeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Cek otorisasi guru
        if ($user->role_id != 3 || !$user->subject_id) {
            Log::error('Unauthorized access to grades index', [
                'user_id' => $user->id,
                'role_id' => $user->role_id,
                'subject_id' => $user->subject_id
            ]);
            abort(403, 'Akses ditolak. Anda bukan guru mata pelajaran.');
        }

        $subject = Subject::findOrFail($user->subject_id);
        $selectedClass = $request->input('class_id');
        
        // Log untuk debugging
        Log::info('Teacher accessing grades index', [
            'user_id' => $user->id,
            'subject' => $subject->name,
            'subject_id' => $subject->id,
            'class_id' => $selectedClass ?? 'null',
            'request_params' => $request->all()
        ]);
        
        // Ambil semua kelas
        $classes = ClassModel::all();
        if ($classes->isEmpty()) {
            Log::error('No classes found in database', [
                'query' => 'SELECT * FROM classes'
            ]);
            return view('grades.teacher.index', [
                'subject' => $subject,
                'classes' => $classes,
                'task_types' => collect(),
                'selectedClass' => null,
                'selectedTaskType' => null,
                'className' => null,
                'students' => collect(),
                'grades' => collect(),
                'error' => 'Tidak ada kelas yang tersedia. Silakan hubungi admin untuk menambahkan kelas.'
            ]);
        }

        // Validasi class_id dari request
        if ($selectedClass && !$classes->pluck('id')->contains($selectedClass)) {
            Log::warning('Invalid class_id provided', [
                'class_id' => $selectedClass,
                'available_class_ids' => $classes->pluck('id')->toArray()
            ]);
            $selectedClass = null; // Reset jika invalid
        }

        // Fallback ke kelas pertama jika tidak ada class_id dipilih
        if (!$selectedClass && $classes->isNotEmpty()) {
            $selectedClass = $classes->first()->id;
            Log::info('Default class selected', [
                'class_id' => $selectedClass,
                'class_name' => $classes->first()->name
            ]);
        }

        $selectedTaskType = $request->input('task_name');

        // Ambil data siswa
        $students = $this->getStudents($selectedClass);
        if ($students->isEmpty() && $selectedClass) {
            Log::warning('No students found for selected class', [
                'class_id' => $selectedClass,
                'class_name' => $classes->find($selectedClass)->name ?? 'Unknown'
            ]);
        }

        $taskTypes = $this->getTaskTypes($user->subject_id, $selectedClass);
        
        // Data untuk view
        $data = [
            'subject' => $subject,
            'classes' => $classes,
            'task_types' => $taskTypes,
            'selectedClass' => $selectedClass,
            'selectedTaskType' => $selectedTaskType,
            'className' => $selectedClass ? ($classes->find($selectedClass)->name ?? 'Kelas Tidak Ditemukan') : null,
            'students' => $students,
            'grades' => $this->getGrades($user->subject_id, $selectedClass, $selectedTaskType),
            'error' => $students->isEmpty() && $selectedClass ? 'Tidak ada siswa di kelas ini. Silakan hubungi admin untuk menambahkan siswa.' : ($classes->isEmpty() ? 'Tidak ada kelas yang tersedia.' : null),
            'debug' => [ // Tambahan untuk debug di view
                'students_count' => $students->count(),
                'class_id' => $selectedClass,
                'available_class_ids' => $classes->pluck('id')->toArray()
            ]
        ];

        Log::info('Grades index data prepared', [
            'selectedClass' => $selectedClass,
            'students_count' => $students->count(),
            'classes_count' => $classes->count(),
            'className' => $data['className'],
            'grades_count' => $data['grades']->count(),
            'task_types_count' => $taskTypes->count(),
            'available_class_ids' => $classes->pluck('id')->toArray()
        ]);

        return view('grades.teacher.index', $data);
    }

    private function getStudents($classId)
    {
        if (!$classId) {
            Log::warning('No class_id provided for getStudents');
            return collect();
        }
        
        // Tambah debug query
        $students = Student::where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name', 'class_id']);
        if ($students->isEmpty()) {
            Log::warning('No students found for class_id', [
                'class_id' => $classId,
                'query' => 'SELECT id, name, class_id FROM students WHERE class_id = ' . $classId
            ]);
        } else {
            Log::info('Students retrieved', [
                'class_id' => $classId,
                'students_count' => $students->count(),
                'student_ids' => $students->pluck('id')->toArray(),
                'student_names' => $students->pluck('name')->toArray()
            ]);
        }
        
        return $students;
    }

    // Method lain tetap sama seperti rework sebelumnya

public function store(Request $request)
{
    $user = Auth::user();
    
    // Cek otorisasi dasar
    if ($user->role_id != 3 || !$user->subject_id) {
        Log::error('Unauthorized store attempt (role/subject check)', ['user_id' => $user->id]);
        return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
    }

    // Cek permission tambahan
    if (!$user->hasPermissionTo('kelola nilai')) {
        Log::error('Unauthorized store attempt (permission denied)', [
            'user_id' => $user->id,
            'role_id' => $user->role_id,
            'subject_id' => $user->subject_id,
            'permissions' => $user->getPermissionNames()->toArray()
        ]);
        return response()->json(['success' => false, 'message' => 'Anda tidak memiliki izin untuk mengelola nilai'], 403);
    }

    // Log data yang diterima sebelum validasi
    Log::info('Received data before validation', [
        'all' => $request->all(),
        'type' => $request->input('type'),
        'student_id' => $request->input('student_id'),
        'task_name' => $request->input('task_name'),
        'score' => $request->input('score'),
        'semester' => $request->input('semester'),
        'subject_id' => $request->input('subject_id'),
    ]);

    $validated = $request->validate([
        'student_id' => 'required|exists:students,id',
        'task_name' => 'required|string|max:255',
        'score' => 'required|numeric|min:0|max:100',
        'type' => 'required|in:written,observation,sumatif', // Pastikan sesuai
        'semester' => 'required|in:odd,even',
        'subject_id' => 'required|exists:subjects,id',
    ], [
        'student_id.required' => 'Harap pilih siswa.',
        'task_name.required' => 'Nama tugas wajib diisi.',
        'score.required' => 'Nilai wajib diisi.',
        'score.numeric' => 'Nilai harus berupa angka.',
        'score.min' => 'Nilai minimal adalah 0.',
        'score.max' => 'Nilai maksimal adalah 100.',
        'type.required' => 'Tipe tugas wajib dipilih.',
        'semester.required' => 'Semester wajib dipilih.',
        'subject_id.required' => 'Subject ID wajib ada.',
    ]);

    try {
        DB::beginTransaction();
        $grade = Grade::firstOrCreate(
            [
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'semester' => $validated['semester']
            ],
            [
                'score' => $validated['score'],
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        $gradeTask = GradeTask::create([
            'student_id' => $validated['student_id'],
            'subject_id' => $validated['subject_id'],
            'task_name' => $validated['task_name'],
            'score' => $validated['score'],
            'type' => $validated['type'],
            'grades_id' => $grade->id,
        ]);
        DB::commit();
        Log::info('Grade stored successfully', ['grade_task_id' => $gradeTask->id]);
        return response()->json(['success' => true, 'message' => 'Nilai berhasil disimpan'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to store grade', ['error' => $e->getMessage()]);
        return response()->json(['success' => false, 'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()], 500);
    }
}


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if ($user->role_id != 3 || !$user->subject_id) {
            Log::error('Unauthorized update attempt', ['user_id' => $user->id, 'grade_task_id' => $id]);
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'score' => 'required|numeric|min:0|max:100',
            'assignment_type' => 'required|in:written,observation,sumatif',
        ], [
            'task_name.required' => 'Nama tugas wajib diisi.',
            'score.required' => 'Nilai wajib diisi.',
            'score.numeric' => 'Nilai harus berupa angka.',
            'score.min' => 'Nilai minimal adalah 0.',
            'score.max' => 'Nilai maksimal adalah 100.',
            'assignment_type.required' => 'Tipe tugas wajib dipilih.',
        ]);

        try {
            $gradeTask = GradeTask::where('subject_id', $user->subject_id)->findOrFail($id);
            
            DB::beginTransaction();

            $gradeTask->update([
                'task_name' => $validated['task_name'],
                'score' => $validated['score'],
                'type' => $validated['assignment_type']
            ]);

            // Update Grade utama jika ada
            if ($gradeTask->grade) {
                $gradeTask->grade->update(['score' => $validated['score']]);
            }

            DB::commit();

            Log::info('Grade updated successfully', [
                'grade_task_id' => $gradeTask->id,
                'student_id' => $gradeTask->student_id,
                'subject_id' => $user->subject_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui',
                'data' => $gradeTask->load(['student', 'subject'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update grade', [
                'error' => $e->getMessage(),
                'grade_task_id' => $id,
                'subject_id' => $user->subject_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if ($user->role_id != 3 || !$user->subject_id) {
            Log::error('Unauthorized delete attempt', ['user_id' => $user->id, 'grade_task_id' => $id]);
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        try {
            $gradeTask = GradeTask::where('subject_id', $user->subject_id)->findOrFail($id);
            
            DB::beginTransaction();
            $gradeTask->delete();
            DB::commit();

            Log::info('Grade deleted successfully', [
                'grade_task_id' => $id,
                'subject_id' => $user->subject_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete grade', [
                'error' => $e->getMessage(),
                'grade_task_id' => $id,
                'subject_id' => $user->subject_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeBatch(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role_id != 3 || !$user->subject_id) {
            Log::error('Unauthorized batch store attempt', ['user_id' => $user->id]);
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'task_name' => 'required|string|max:255',
            'assignment_type' => 'required|in:written,observation,sumatif',
            'semester' => 'required|in:odd,even',
            'scores' => 'required|array',
            'scores.*' => 'nullable|numeric|min:0|max:100'
        ], [
            'class_id.required' => 'Kelas wajib dipilih.',
            'task_name.required' => 'Nama tugas wajib diisi.',
            'assignment_type.required' => 'Tipe tugas wajib dipilih.',
            'semester.required' => 'Semester wajib dipilih.',
            'scores.required' => 'Nilai wajib diisi untuk setidaknya satu siswa.',
            'scores.*.numeric' => 'Nilai harus berupa angka.',
            'scores.*.min' => 'Nilai minimal adalah 0.',
            'scores.*.max' => 'Nilai maksimal adalah 100.',
        ]);

        try {
            DB::beginTransaction();
            
            $savedCount = 0;
            foreach ($validated['scores'] as $studentId => $score) {
                if (is_null($score) || $score === '') continue;
                
                // Verifikasi siswa ada di kelas
                if (!Student::where('id', $studentId)->where('class_id', $validated['class_id'])->exists()) {
                    Log::warning('Student not found in class', [
                        'student_id' => $studentId,
                        'class_id' => $validated['class_id']
                    ]);
                    continue;
                }
                
                // Cari atau buat Grade
                $grade = Grade::firstOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $user->subject_id,
                        'semester' => $validated['semester']
                    ],
                    [
                        'score' => $score,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );

                // Buat GradeTask
                $gradeTask = GradeTask::create([
                    'student_id' => $studentId,
                    'subject_id' => $user->subject_id,
                    'task_name' => $validated['task_name'],
                    'score' => $score,
                    'type' => $validated['assignment_type'],
                    'grades_id' => $grade->id,
                ]);
                
                $savedCount++;
            }
            
            DB::commit();

            Log::info('Batch grades stored successfully', [
                'saved_count' => $savedCount,
                'subject_id' => $user->subject_id,
                'class_id' => $validated['class_id']
            ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil menyimpan nilai untuk {$savedCount} siswa"
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to store batch grades', [
                'error' => $e->getMessage(),
                'class_id' => $request->input('class_id'),
                'subject_id' => $user->subject_id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getTaskTypes($subjectId, $classId = null)
    {
        $query = GradeTask::where('subject_id', $subjectId);
        
        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }
        
        $taskTypes = $query->pluck('task_name')->unique()->values();
        Log::info('Task types retrieved', [
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'task_types_count' => $taskTypes->count()
        ]);
        
        return $taskTypes;
    }

    private function getGrades($subjectId, $classId, $taskType = null)
    {
        if (!$classId) {
            Log::warning('No class_id provided for getGrades');
            return collect();
        }
        
        $query = GradeTask::with(['student', 'subject'])
            ->where('subject_id', $subjectId)
            ->whereHas('student', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
            
        if ($taskType) {
            $query->where('task_name', $taskType);
        }
        
        $grades = $query->latest()->get()->groupBy('student_id');
        Log::info('Grades retrieved', [
            'subject_id' => $subjectId,
            'class_id' => $classId,
            'task_type' => $taskType,
            'grades_count' => $grades->count()
        ]);
        
        return $grades;
    }
}