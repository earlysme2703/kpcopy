<!DOCTYPE html>
<html>
<head>
    <title>Daftar Nilai</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
        }
        .header {
            width: 100%;
            margin-bottom: 15px;
        }
        .left-header {
            width: 33%;
            float: left;
        }
        .center-header {
            width: 33%;
            float: left;
            text-align: center;
        }
        .right-header {
            width: 33%;
            float: right;
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        th, td {
            border: 1px solid black;
            padding: 3px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        .student-name {
            text-align: left;
            padding-left: 5px;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div class="left-header">
            <p><strong>Mapel:</strong> {{ $subject->name }}</p>
            <p><strong>Kelas:</strong> {{ $class->name }}</p>
        </div>
        <div class="center-header">
            <p><strong>DAFTAR NILAI MI.......................... TH. {{ $year }}/{{ $year+1 }}</strong></p>
        </div>
        <div class="right-header">
            <p><strong>SEMESTER:</strong> {{ $semester == 'Odd' ? '1 (GASAL)' : '2 (GENAP)' }}</p>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th rowspan="5">No</th>
                <th rowspan="5">NIS</th>
                <th rowspan="5">Nama Siswa</th>
                <th colspan="19">FORMATIF</th>
                <th colspan="2" rowspan="2">SUMATIF</th>
                <th rowspan="5">RATA-RATA</th>
                <th rowspan="5">PREDIKAT</th>
                <th rowspan="5">DESKRIPSI</th>
            </tr>
            <tr>
                <th colspan="8">TERTULIS (A)</th>
                <th colspan="5">PENGAMATAN (B)</th>
                <th colspan="6">PR</th>
            </tr>
            <tr>
                <!-- Tertulis -->
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>RT2</th>
                <!-- Pengamatan -->
                <th>1</th><th>2</th><th>3</th><th>4</th><th>RT2</th>
                <!-- PR -->
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>RT2</th>
                <!-- Sumatif -->
                <th>UTS</th><th>UAS</th>
            </tr>
            <tr>
                <!-- Penomoran kolom -->
                <th>(1)</th><th>(2)</th><th>(3)</th><th>(4)</th><th>(5)</th><th>(6)</th><th>(7)</th><th>(8)</th>
                <th>(9)</th><th>(10)</th><th>(11)</th><th>(12)</th><th>(13)</th>
                <th>(14)</th><th>(15)</th><th>(16)</th><th>(17)</th><th>(18)</th><th>(19)</th>
                <th>(20)</th><th>(21)</th>
                <th>(22)</th><th>(23)</th><th>(24)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $student)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $student['student_number'] }}</td>
                <td class="student-name">{{ $student['name'] }}</td>
                
                <!-- Tertulis -->
                @foreach($student['written'] as $written)
                    <td>{{ $written }}</td>
                @endforeach
                <td>{{ $student['average_written'] }}</td>
                
                <!-- Pengamatan -->
                @foreach($student['observation'] as $obs)
                    <td>{{ $obs }}</td>
                @endforeach
                <td>{{ $student['average_observation'] }}</td>
                
                <!-- PR -->
                @foreach($student['homework'] as $hw)
                    <td>{{ $hw }}</td>
                @endforeach
                <td>{{ $student['average_homework'] }}</td>
                
                <!-- Sumatif -->
                <td>{{ $student['midterm_score'] }}</td>
                <td>{{ $student['final_exam_score'] }}</td>
                
                <!-- Akhir -->
                <td>{{ $student['final_score'] }}</td>
                <td>{{ $student['grade_letter'] }}</td>
                <td>
                    @if($student['grade_letter'] == 'A')
                        Sangat Baik
                    @elseif($student['grade_letter'] == 'B')
                        Baik
                    @elseif($student['grade_letter'] == 'C')
                        Cukup
                    @elseif($student['grade_letter'] == 'D')
                        Perlu Bimbingan
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>