<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /** Tampilkan Halaman Analisis Beban Belajar */
    public function index()
    {
        $user = Auth::user();

        // Ambil daftar kelas user
        $classes = $user->classes()->get();
        if (in_array($user->role, ['dosen', 'guru'])) {
            $ownedClasses = $user->ownedClasses()->get();
            $classes = $classes->merge($ownedClasses)->unique('id');
        }

        $classIds = $classes->pluck('id');

        // 1. Grafik Beban Belajar: Jumlah tugas yang aktif per kelas
        // Fix N+1: load semua tasks sekaligus lalu group by class_id
        $allPublishedTasks = Task::whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->get()
            ->groupBy('class_id');

        $loadLabels = [];
        $loadData   = [];
        foreach ($classes as $class) {
            $loadLabels[] = $class->nama_kelas;
            $loadData[]   = $allPublishedTasks->get($class->id, collect())->count();
        }

        // 2. Statistik Produktivitas Tugas
        $productivityData = [
            'submitted' => 0,
            'late'      => 0,
            'pending'   => 0,
            'graded'    => 0,
        ];

        if ($user->role === 'mahasiswa') {
            // Tugas yang harus dikerjakan di seluruh kelas mahasiswa
            $totalTasks = Task::whereIn('class_id', $classIds)->where('status', 'published')->get();
            $taskIds    = $totalTasks->pluck('id');

            // Fix N+1: load semua submissions mahasiswa ini sekaligus, keyed by task_id
            $mySubmissions = Submission::where('user_id', $user->id)
                ->whereIn('task_id', $taskIds)
                ->get()
                ->keyBy('task_id');

            foreach ($totalTasks as $task) {
                $sub = $mySubmissions->get($task->id);
                if ($sub) {
                    if ($sub->status === 'graded') {
                        $productivityData['graded']++;
                    } elseif ($sub->status === 'late') {
                        $productivityData['late']++;
                    } else {
                        $productivityData['submitted']++;
                    }
                } else {
                    $productivityData['pending']++;
                }
            }
        } else {
            // Jika dosen: hitung rata-rata pengumpulan tugas oleh mahasiswa di kelasnya
            // Fix N+1: eager load classModel, load semua submissions sekaligus
            $tasks   = Task::whereIn('class_id', $classIds)->with('classModel')->get();
            $taskIds = $tasks->pluck('id');

            // Load semua submissions untuk semua task dosen ini dalam SATU query
            $allSubmissions = Submission::whereIn('task_id', $taskIds)
                ->get()
                ->groupBy('task_id');

            // Load jumlah member per kelas dalam satu query
            $memberCountByClass = [];
            foreach ($classes as $class) {
                $memberCountByClass[$class->id] = $class->students()->count();
            }

            foreach ($tasks as $task) {
                $taskSubs  = $allSubmissions->get($task->id, collect());
                $subCount  = $taskSubs->count();
                $memberCount = $memberCountByClass[$task->class_id] ?? 0;
                $pending   = max(0, $memberCount - $subCount);

                $productivityData['submitted'] += $taskSubs->whereIn('status', ['submitted', 'late'])->count();
                $productivityData['graded']    += $taskSubs->where('status', 'graded')->count();
                $productivityData['late']      += $taskSubs->where('status', 'late')->count();
                $productivityData['pending']   += $pending;
            }
        }

        // 3. Aktivitas Tugas Mingguan (Harian Senin - Minggu)
        $startOfWeek = Carbon::now()->startOfWeek();
        $weeklyActivity = [
            'Senin' => 0, 'Selasa' => 0, 'Rabu' => 0, 'Kamis' => 0, 'Jumat' => 0, 'Sabtu' => 0, 'Minggu' => 0,
        ];

        // Ambil tenggat waktu tugas minggu ini — sudah satu query, tidak ada N+1
        $tasksThisWeek = Task::whereIn('class_id', $classIds)
            ->whereBetween('deadline', [$startOfWeek, $startOfWeek->copy()->endOfWeek()])
            ->get();

        foreach ($tasksThisWeek as $task) {
            $dayName = $task->deadline->translatedFormat('l'); // Get indonesian day name
            if (isset($weeklyActivity[$dayName])) {
                $weeklyActivity[$dayName]++;
            }
        }

        return view('analytics.index', compact('classes', 'user', 'loadLabels', 'loadData', 'productivityData', 'weeklyActivity'));
    }
}
