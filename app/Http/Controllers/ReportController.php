<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Report;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /** Export nilai ke format CSV (Excel-ready) */
    public function exportCsv(ClassModel $class, Request $request)
    {
        $user = Auth::user();
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat mengunduh laporan nilai.');
        }

        $students = $class->students()->get();
        $tasks    = $class->tasks()->where('status', 'published')->get();

        // Fix N+1: load ALL submissions for this class in ONE query, keyed by "task_id_user_id"
        $taskIds = $tasks->pluck('id');
        $studentIds = $students->pluck('id');
        $submissionsMap = Submission::whereIn('task_id', $taskIds)
            ->whereIn('user_id', $studentIds)
            ->get()
            ->keyBy(fn ($s) => $s->task_id.'_'.$s->user_id);

        $fileName = 'Laporan_Nilai_'.str_replace(' ', '_', $class->nama_kelas).'_'.date('Ymd_His').'.csv';
        // Build CSV in memory then save to storage and return as download
        $handle = fopen('php://temp', 'r+');
        // Add UTF-8 BOM to fix Excel encoding issue
        fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));

        // CSV Header Row
        $headerRow = ['NIM', 'Nama Mahasiswa'];
        foreach ($tasks as $task) {
            $headerRow[] = $task->judul.' (Max: '.$task->nilai_max.')';
        }
        $headerRow[] = 'Rata-rata Nilai';
        fputcsv($handle, $headerRow);

        // CSV Data Rows — O(1) lookup from pre-loaded map, no extra queries
        foreach ($students as $student) {
            $row = [
                $student->nim_nip ?: '-',
                $student->name,
            ];

            $totalGrade = 0;
            $gradedTasksCount = 0;

            foreach ($tasks as $task) {
                $sub   = $submissionsMap->get($task->id.'_'.$student->id);
                $grade = $sub ? $sub->nilai : null;

                $row[] = $grade !== null ? $grade : 'Belum dinilai';

                if ($grade !== null) {
                    $totalGrade += $grade;
                    $gradedTasksCount++;
                }
            }

            $average = $gradedTasksCount > 0 ? round($totalGrade / $gradedTasksCount, 2) : 0;
            $row[] = $average;

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        // Save to storage (public disk)
        $reportPath = 'reports/'.$fileName;
        Storage::disk('public')->put($reportPath, $csvContent);

        // Save report history in DB
        Report::create([
            'class_id' => $class->id,
            'generated_by' => $user->id,
            'judul' => 'Laporan Nilai - '.$class->nama_kelas,
            'tipe' => 'excel',
            'file_path' => $reportPath,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'export_grades_excel',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'ip_address' => $request->ip(),
        ]);

        // Return download response
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /** Export nilai ke format PDF (Printable HTML view) */
    public function exportPdf(ClassModel $class, Request $request)
    {
        $user = Auth::user();
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat mencetak laporan nilai.');
        }

        $students = $class->students()->get();
        $tasks    = $class->tasks()->where('status', 'published')->get();

        // Fix N+1: load ALL submissions for this class in ONE query, keyed by "task_id_user_id"
        $taskIds = $tasks->pluck('id');
        $studentIds = $students->pluck('id');
        $submissionsMap = Submission::whereIn('task_id', $taskIds)
            ->whereIn('user_id', $studentIds)
            ->get()
            ->keyBy(fn ($s) => $s->task_id.'_'.$s->user_id);

        // Calculate student averages — O(1) lookup from pre-loaded map, no extra queries
        foreach ($students as $student) {
            $totalGrade = 0;
            $gradedCount = 0;
            $studentGrades = [];

            foreach ($tasks as $task) {
                $sub   = $submissionsMap->get($task->id.'_'.$student->id);
                $grade = $sub ? $sub->nilai : null;
                $studentGrades[$task->id] = $grade;

                if ($grade !== null) {
                    $totalGrade += $grade;
                    $gradedCount++;
                }
            }

            $student->grades_list    = $studentGrades;
            $student->grades_average = $gradedCount > 0 ? round($totalGrade / $gradedCount, 2) : 0;
        }

        // Save report history in DB
        $fileName = 'Laporan_Nilai_'.str_replace(' ', '_', $class->nama_kelas).'_'.date('Ymd_His').'.pdf';
        Report::create([
            'class_id' => $class->id,
            'generated_by' => $user->id,
            'judul' => 'Laporan Nilai - '.$class->nama_kelas,
            'tipe' => 'pdf',
            'file_path' => 'reports/'.$fileName,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'export_grades_pdf',
            'model_type' => ClassModel::class,
            'model_id' => $class->id,
            'ip_address' => $request->ip(),
        ]);

        return view('reports.grades_pdf', compact('class', 'tasks', 'students', 'user'));
    }
}
