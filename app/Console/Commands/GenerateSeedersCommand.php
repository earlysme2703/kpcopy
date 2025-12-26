<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateSeedersCommand extends Command
{
    protected $signature = 'generate:seeders';
    protected $description = 'Generate seeders from current database data';

    public function handle()
    {
        $this->info('Starting seeder generation from database...');

        try {
            // Generate each seeder
            $this->generateAcademicYearSeeder();
            $this->generateRolePermissionSeeder();
            $this->generateClassSeeder();
            $this->generateSubjectSeeder();
            $this->generateUserSeeder();
            $this->generateStudentSeeder();
            $this->generateGradeSeeder();
            $this->generateGradeTaskSeeder();

            $this->info('All seeders generated successfully!');
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }

    private function generateAcademicYearSeeder()
    {
        $this->info('Generating AcademicYearSeeder...');
        
        $years = DB::table('academic_years')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\AcademicYear;\n\nclass AcademicYearSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($years as $year) {
            $content .= "        AcademicYear::create([\n";
            $content .= "            'name' => '{$year->name}',\n";
            $content .= "            'is_active' => {$year->is_active},\n";
            $content .= "        ]);\n\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/AcademicYearSeeder.php'), $content);
        $this->line('✓ AcademicYearSeeder generated');
    }

    private function generateRolePermissionSeeder()
    {
        $this->info('Generating RolePermissionSeeder...');
        
        $roles = DB::table('roles')->get();
        $permissions = DB::table('permissions')->get();
        $roleHasPermissions = DB::table('role_has_permissions')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse Spatie\\Permission\\Models\\Role;\nuse Spatie\\Permission\\Models\\Permission;\n\nclass RolePermissionSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        // Permissions
        $content .= "        // Create Permissions\n";
        foreach ($permissions as $perm) {
            $content .= "        Permission::create(['name' => '{$perm->name}', 'guard_name' => '{$perm->guard_name}']);\n";
        }
        
        $content .= "\n        // Create Roles\n";
        foreach ($roles as $role) {
            $content .= "        \$role{$role->id} = Role::create(['name' => '{$role->name}', 'guard_name' => '{$role->guard_name}']);\n";
        }
        
        $content .= "\n        // Assign Permissions to Roles\n";
        foreach ($roleHasPermissions as $rp) {
            $permission = $permissions->firstWhere('id', $rp->permission_id);
            $content .= "        \$role{$rp->role_id}->givePermissionTo('{$permission->name}');\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/RolePermissionSeeder.php'), $content);
        $this->line('✓ RolePermissionSeeder generated');
    }

    private function generateClassSeeder()
    {
        $this->info('Generating ClassSeeder...');
        
        $classes = DB::table('classes')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\ClassModel;\n\nclass ClassSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($classes as $class) {
            $content .= "        ClassModel::create(['name' => '{$class->name}']);\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/ClassSeeder.php'), $content);
        $this->line('✓ ClassSeeder generated');
    }

    private function generateSubjectSeeder()
    {
        $this->info('Generating SubjectSeeder...');
        
        $subjects = DB::table('subjects')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\Subject;\n\nclass SubjectSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($subjects as $subject) {
            $content .= "        Subject::create(['name' => '{$subject->name}']);\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/SubjectSeeder.php'), $content);
        $this->line('✓ SubjectSeeder generated');
    }

    private function generateUserSeeder()
    {
        $this->info('Generating UserSeeder...');
        
        $users = DB::table('users')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\User;\n\nclass UserSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($users as $user) {
            $content .= "        \$user = User::create([\n";
            $content .= "            'name' => " . $this->formatValue($user->name) . ",\n";
            $content .= "            'email' => " . $this->formatValue($user->email) . ",\n";
            $content .= "            'password' => " . $this->formatValue($user->password) . ",\n";
            $content .= "            'nip' => " . $this->formatValue($user->nip) . ",\n";
            $content .= "            'nuptk' => " . $this->formatValue($user->nuptk) . ",\n";
            $content .= "            'profile_picture' => " . $this->formatValue($user->profile_picture) . ",\n";
            $content .= "            'role_id' => " . $this->formatValue($user->role_id) . ",\n";
            $content .= "            'class_id' => " . $this->formatValue($user->class_id) . ",\n";
            $content .= "            'subject_id' => " . $this->formatValue($user->subject_id) . ",\n";
            $content .= "        ]);\n";
            
            // Get role
            $roleId = DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', 'App\\Models\\User')->value('role_id');
            if ($roleId) {
                $roleName = DB::table('roles')->where('id', $roleId)->value('name');
                $content .= "        \$user->assignRole('{$roleName}');\n";
            }
            
            $content .= "\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/UserSeeder.php'), $content);
        $this->line('✓ UserSeeder generated');
    }

    private function generateStudentSeeder()
    {
        $this->info('Generating StudentSeeder...');
        
        $students = DB::table('students')->get();
        $studentClasses = DB::table('student_classes')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\Student;\nuse App\\Models\\StudentClass;\n\nclass StudentSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($students as $student) {
            $content .= "        \$student{$student->id} = Student::create([\n";
            $content .= "            'name' => " . $this->formatValue($student->name) . ",\n";
            $content .= "            'nis' => " . $this->formatValue($student->nis) . ",\n";
            $content .= "            'gender' => " . $this->formatValue($student->gender) . ",\n";
            $content .= "            'parent_phone' => " . $this->formatValue($student->parent_phone) . ",\n";
            $content .= "            'parent_name' => " . $this->formatValue($student->parent_name) . ",\n";
            $content .= "            'birth_place' => " . $this->formatValue($student->birth_place) . ",\n";
            $content .= "            'birth_date' => " . $this->formatValue($student->birth_date) . ",\n";
            $content .= "        ]);\n\n";
        }
        
        $content .= "        // Student-Class relationships\n";
        foreach ($studentClasses as $sc) {
            $content .= "        StudentClass::create([\n";
            $content .= "            'student_id' => {$sc->student_id},\n";
            $content .= "            'class_id' => {$sc->class_id},\n";
            $content .= "            'academic_year_id' => {$sc->academic_year_id},\n";
            $content .= "        ]);\n\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/StudentSeeder.php'), $content);
        $this->line('✓ StudentSeeder generated');
    }

    private function generateGradeSeeder()
    {
        $this->info('Generating GradeSeeder...');
        
        $grades = DB::table('grades')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\Grade;\n\nclass GradeSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($grades as $grade) {
            $content .= "        Grade::create([\n";
            $content .= "            'student_id' => {$grade->student_id},\n";
            $content .= "            'subject_id' => {$grade->subject_id},\n";
            $content .= "            'semester' => " . $this->formatValue($grade->semester) . ",\n";
            $content .= "            'average_written' => " . $this->formatValue($grade->average_written) . ",\n";
            $content .= "            'average_observation' => " . $this->formatValue($grade->average_observation) . ",\n";
            $content .= "            'average_homework' => " . $this->formatValue($grade->average_homework) . ",\n";
            $content .= "            'midterm_score' => " . $this->formatValue($grade->midterm_score) . ",\n";
            $content .= "            'final_exam_score' => " . $this->formatValue($grade->final_exam_score) . ",\n";
            $content .= "            'final_score' => " . $this->formatValue($grade->final_score) . ",\n";
            $content .= "            'grade_letter' => " . $this->formatValue($grade->grade_letter) . ",\n";
            $content .= "            'academic_year_id' => {$grade->academic_year_id},\n";
            $content .= "        ]);\n\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/GradeSeeder.php'), $content);
        $this->line('✓ GradeSeeder generated');
    }

    private function generateGradeTaskSeeder()
    {
        $this->info('Generating GradeTaskSeeder...');
        
        $gradeTasks = DB::table('grade_tasks')->get();
        
        $content = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Seeder;\nuse App\\Models\\GradeTask;\n\nclass GradeTaskSeeder extends Seeder\n{\n    public function run(): void\n    {\n";
        
        foreach ($gradeTasks as $task) {
            $content .= "        GradeTask::create([\n";
            $content .= "            'student_id' => {$task->student_id},\n";
            $content .= "            'subject_id' => {$task->subject_id},\n";
            $content .= "            'task_name' => " . $this->formatValue($task->task_name) . ",\n";
            $content .= "            'type' => " . $this->formatValue($task->type) . ",\n";
            $content .= "            'grades_id' => {$task->grades_id},\n";
            $content .= "            'score' => " . $this->formatValue($task->score) . ",\n";
            $content .= "        ]);\n\n";
        }
        
        $content .= "    }\n}\n";
        
        File::put(database_path('seeders/GradeTaskSeeder.php'), $content);
        $this->line('✓ GradeTaskSeeder generated');
    }

    private function formatValue($value)
    {
        if (is_null($value)) {
            return 'null';
        }
        
        if (is_numeric($value)) {
            return $value;
        }
        
        // Escape single quotes in strings
        $escaped = str_replace("'", "\\'", $value);
        return "'{$escaped}'";
    }
}
