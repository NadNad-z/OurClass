<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Notification;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /** Simpan jadwal kuliah baru (Dosen) */
    public function store(ClassModel $class, Request $request)
    {
        $user = Auth::user();
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat menambahkan jadwal.');
        }

        $request->validate([
            'mata_kuliah' => 'required|string|max:255',
            'dosen' => 'nullable|string|max:255',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'ruangan' => 'nullable|string|max:100',
            'catatan' => 'nullable|string',
            'color' => 'nullable|string|max:7',
        ], [
            'mata_kuliah.required' => 'Nama mata kuliah/topik wajib diisi.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.',
        ]);

        $color = $request->color ?: $class->color ?: '#3B82F6';

        $schedule = Schedule::create([
            'class_id' => $class->id,
            'mata_kuliah' => $request->mata_kuliah,
            'dosen' => $request->dosen ?: $user->name,
            'hari' => $request->hari,
            'waktu_mulai' => $request->waktu_mulai,
            'waktu_selesai' => $request->waktu_selesai,
            'ruangan' => $request->ruangan ?: $class->ruangan,
            'catatan' => $request->catatan,
            'color' => $color,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'create_schedule',
            'model_type' => Schedule::class,
            'model_id' => $schedule->id,
            'new_values' => $schedule->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke mahasiswa
        $students = $class->students()->get();
        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->id,
                'class_id' => $class->id,
                'judul' => 'Jadwal Kelas Ditambahkan',
                'pesan' => 'Jadwal baru ditambahkan untuk hari '.$schedule->hari.' pukul '.substr($schedule->waktu_mulai, 0, 5),
                'tipe' => 'jadwal',
                'link' => route('classes.show', $class->id),
            ]);
        }

        return back()->with('success', 'Jadwal perkuliahan baru berhasil ditambahkan!');
    }
}
