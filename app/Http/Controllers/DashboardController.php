<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Submission;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Get user classes
        $classes = $user->classes()->get();
        $ownedClasses = $user->ownedClasses()->get();
        $classes = $classes->merge($ownedClasses)->unique('id');
        $classesCount = $classes->count();
        $classIds = $classes->pluck('id');

        // 2. Get pending tasks
        $pendingTasksCount = 0;
        if ($user->role === 'mahasiswa') {
            // Tugas published di kelas mahasiswa
            $tasks = Task::whereIn('class_id', $classIds)->where('status', 'published')->get();
            foreach ($tasks as $task) {
                // Check if user has submitted
                $hasSubmitted = Submission::where('task_id', $task->id)->where('user_id', $user->id)->exists();
                if (! $hasSubmitted) {
                    $pendingTasksCount++;
                }
            }
        } else {
            // Jika Dosen: Hitung total tugas yang dibuat yang belum dinilai semuanya
            $tasks = Task::whereIn('class_id', $classIds)->get();
            foreach ($tasks as $task) {
                $studentsCount = $task->classModel->students()->count();
                $gradedCount = Submission::where('task_id', $task->id)->where('status', 'graded')->count();
                if ($gradedCount < $studentsCount) {
                    $pendingTasksCount++;
                }
            }
        }

        // 3. Today's schedules
        // Convert English day name of carbon to Indonesian
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $todayEnglish = Carbon::now()->format('l');
        $todayIndonesian = $dayMap[$todayEnglish] ?? 'Senin';

        $todaySchedules = Schedule::whereIn('class_id', $classIds)
            ->where('hari', $todayIndonesian)
            ->orderBy('waktu_mulai', 'asc')
            ->get();

        $todaySchedulesCount = $todaySchedules->count();

        // 4. Latest notifications
        $recentNotifications = $user->notifications()->orderBy('created_at', 'desc')->limit(5)->get();

        return view('dashboard.index', compact(
            'user',
            'classesCount',
            'pendingTasksCount',
            'todaySchedulesCount',
            'todaySchedules',
            'recentNotifications',
            'classes'
        ));
    }
}
