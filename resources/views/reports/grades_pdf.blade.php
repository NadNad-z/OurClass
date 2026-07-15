<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai - {{ $class->nama_kelas }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            background-color: #fff;
            padding: 2rem;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .header h1 {
            font-size: 1.6rem;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.05em;
        }
        .header h2 {
            font-size: 1.2rem;
            text-transform: uppercase;
            margin: 0.25rem 0;
            font-weight: 600;
        }
        .header p {
            font-size: 0.9rem;
            margin: 0;
            font-style: italic;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        .meta-table td {
            padding: 0.25rem 0;
        }
        .meta-label {
            font-weight: bold;
            width: 180px;
        }
        .meta-value {
            width: auto;
        }
        .grade-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }
        .grade-table th, .grade-table td {
            border: 1px solid #000;
            padding: 0.5rem;
            text-align: left;
        }
        .grade-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .grade-table td.center {
            text-align: center;
        }
        .signature-container {
            margin-top: 3rem;
            display: flex;
            justify-content: flex-end;
        }
        .signature-box {
            text-align: center;
            width: 250px;
        }
        .signature-space {
            height: 70px;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Printable Header (Kop Surat) -->
    <div class="header">
        <h1>Laporan Nilai Evaluasi Kelas</h1>
        <h2>Aplikasi Kelas Digital - OurClass</h2>
        <p>{{ app()->getLocale() == 'en' ? 'Print Date: ' . date('d F Y, h:i A') : 'Tanggal Cetak: ' . date('d F Y, H.i') . ' WIB' }}</p>
    </div>

    <!-- Metadata -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Kelas</td>
            <td>: {{ $class->nama_kelas }}</td>
            <td class="meta-label">Dosen Pengampu</td>
            <td>: {{ $class->admin->name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Mata Kuliah</td>
            <td>: {{ $class->mata_kuliah ?: '-' }}</td>
            <td class="meta-label">Tahun Akademik</td>
            <td>: {{ $class->tahun_ajaran ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Ruangan</td>
            <td>: {{ $class->ruangan ?: '-' }}</td>
            <td class="meta-label">Semester</td>
            <td>: {{ $class->semester ?: '-' }}</td>
        </tr>
    </table>

    <!-- Grades Table -->
    <table class="grade-table">
        <thead>
            <tr>
                <th style="width: 50px;">No</th>
                <th style="width: 120px;">NIM</th>
                <th>Nama Mahasiswa</th>
                @foreach($tasks as $task)
                    <th>{{ $task->judul }}</th>
                @endforeach
                <th style="width: 100px;">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $student->nim_nip ?: '-' }}</td>
                    <td>{{ $student->name }}</td>
                    @foreach($tasks as $task)
                        <td class="center">
                            {{ $student->grades_list[$task->id] !== null ? $student->grades_list[$task->id] : '-' }}
                        </td>
                    @endforeach
                    <td class="center" style="font-weight: bold;">
                        {{ $student->grades_average }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature Footer Box -->
    <div class="signature-container">
        <div class="signature-box">
            <p>Dosen Pengampu,</p>
            <div class="signature-space"></div>
            <p style="font-weight: bold; text-decoration: underline;">{{ $class->admin->name }}</p>
            @if($class->admin->nim_nip)
                <p>NIP. {{ $class->admin->nim_nip }}</p>
            @endif
        </div>
    </div>

    <!-- Auto Print Script -->
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
