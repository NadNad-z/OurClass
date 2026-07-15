<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\Schedule;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    /** Halaman Agenda & Jadwal - semua jadwal dari semua kelas user */
    public function index()
    {
        $user = Auth::user();

        // Ambil semua kelas user (ikut + buat)
        $classes = $user->classes()->get()
            ->merge($user->ownedClasses()->get())
            ->unique('id');

        $classIds = $classes->pluck('id');

        // Ambil semua jadwal dari kelas-kelas tersebut
        $schedules = Schedule::whereIn('class_id', $classIds)
            ->with('classModel')
            ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('waktu_mulai')
            ->get()
            ->groupBy('hari');

        // Urutan hari
        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        return view('agenda.index', compact('user', 'classes', 'schedules', 'hariOrder'));
    }

    /** Halaman Tugas - semua tugas dari semua kelas user */
    public function tasks()
    {
        $user = Auth::user();

        $classes = $user->classes()->get()
            ->merge($user->ownedClasses()->get())
            ->unique('id');

        $classIds = $classes->pluck('id');

        // Ambil tugas per status deadline
        $allTasks = Task::whereIn('class_id', $classIds)
            ->with(['classModel', 'creator'])
            ->orderBy('deadline', 'asc')
            ->get();

        // Kelompokkan: mendatang, lewat deadline, semua
        $now = now();
        $upcoming = $allTasks->filter(fn($t) => $t->deadline >= $now)->values();
        $overdue  = $allTasks->filter(fn($t) => $t->deadline < $now)->values();

        return view('agenda.tasks', compact('user', 'classes', 'upcoming', 'overdue', 'allTasks'));
    }

    /** Halaman Diskusi Kelas - semua diskusi dari semua kelas user */
    public function discussions()
    {
        $user = Auth::user();

        $classes = $user->classes()->get()
            ->merge($user->ownedClasses()->get())
            ->unique('id');

        $classIds = $classes->pluck('id');

        // Hanya diskusi kelas umum (task_id null), bukan Q&A tugas
        $discussions = Discussion::whereIn('class_id', $classIds)
            ->whereNull('task_id')
            ->with(['user', 'classModel', 'replies'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        return view('agenda.discussions', compact('user', 'classes', 'discussions'));
    }
}
