<x-app-layout title="Dashboard Guru Mata Pelajaran">
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Guru Mata Pelajaran
                <span class="text-blue-600">{{ $subjectName }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Statistik Utama --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-500">
                            <span class="iconify w-8 h-8" data-icon="mdi:chart-bar"></span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Rata-rata Nilai</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $overallStats->average ? round($overallStats->average, 1) : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-500">
                            <span class="iconify w-8 h-8" data-icon="mdi:alert-circle"></span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Siswa Tanpa Nilai</p>
                            <p class="text-2xl font-semibold text-gray-800">{{ $studentsWithoutGrades }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Notifikasi Siswa Tanpa Nilai --}}
            @if($studentsWithoutGrades > 0)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <span class="iconify w-5 h-5 text-yellow-400" data-icon="mdi:alert"></span>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Ada <span class="font-medium">{{ $studentsWithoutGrades }}</span> siswa yang belum memiliki catatan nilai untuk {{ $subjectName }}.
                            <a href="{{ route('grades.create', auth()->user()->subject_id) }}" class="font-medium underline text-yellow-700 hover:text-yellow-600">
                                Input nilai sekarang
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                {{-- Distribusi Nilai --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Distribusi Nilai</h3>
                    <div class="relative h-80">
                        <canvas id="gradeDistributionChart"></canvas>
                    </div>
                </div>
                
                {{-- Aktivitas Input Nilai Terbaru --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Aktivitas Input Nilai Terbaru</h3>
                    <div class="space-y-4">
                        @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-500">
                                    <span class="iconify w-6 h-6" data-icon="mdi:pencil"></span>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $activity->student_name }}
                                </p>
                                <div class="flex items-center">
                                    <span class="text-xs text-gray-500">{{ $activity->task_name }}</span>
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $activity->score >= 75 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $activity->score }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $activity->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-500">
                            Belum ada aktivitas input nilai
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Statistik Nilai Per Tugas --}}
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-700 border-b pb-2 mb-4">Statistik Nilai Per Tugas</h3>
                <div class="relative h-80">
                    <canvas id="taskStatsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    {{-- JavaScript untuk Chart --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Chart Distribusi Nilai
            const ctx1 = document.getElementById('gradeDistributionChart');
            new Chart(ctx1, {
                type: 'pie',
                data: {
                    labels: ['Sangat Baik (≥90)', 'Baik (75-89)', 'Cukup (60-74)', 'Perlu Perbaikan (<60)'],
                    datasets: [{
                        data: [
                            {{ $gradeDistribution['sangat_baik'] }}, 
                            {{ $gradeDistribution['baik'] }}, 
                            {{ $gradeDistribution['cukup'] }}, 
                            {{ $gradeDistribution['perlu_perbaikan'] }}
                        ],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(250, 204, 21, 0.7)',
                            'rgba(239, 68, 68, 0.7)'
                        ],
                        borderColor: [
                            'rgba(34, 197, 94, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(250, 204, 21, 1)',
                            'rgba(239, 68, 68, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Chart Statistik Nilai Per Tugas
            const ctx2 = document.getElementById('taskStatsChart');
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: [@foreach($taskStats as $task)'{{ $task['name'] }}', @endforeach],
                    datasets: [{
                        label: 'Rata-rata Nilai',
                        data: [@foreach($taskStats as $task){{ $task['average'] }}, @endforeach],
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>