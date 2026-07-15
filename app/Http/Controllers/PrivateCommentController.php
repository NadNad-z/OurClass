<?php

namespace App\Http\Controllers;

use App\Models\PrivateComment;
use App\Models\Submission;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrivateCommentController extends Controller
{
    public function store(Request $request, Submission $submission)
    {
        $request->validate([
            'komentar' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Cek akses: hanya pembuat submission atau admin kelas yang boleh komen
        $class = $submission->task->classModel;
        if ($submission->user_id !== $user->id && ! $user->isClassAdmin($class)) {
            abort(403, 'Akses ditolak.');
        }

        PrivateComment::create([
            'submission_id' => $submission->id,
            'user_id' => $user->id,
            'komentar' => $request->komentar,
        ]);

        // Buat Notifikasi
        $recipientId = ($user->id === $submission->user_id) ? $class->admin_id : $submission->user_id;
        
        Notification::create([
            'user_id' => $recipientId,
            'class_id' => $class->id,
            'judul' => 'Komentar Pribadi Baru',
            'pesan' => $user->name . ' mengirim pesan pribadi di tugas ' . $submission->task->judul,
            'tipe' => 'sistem',
            'link' => route('tasks.show', $submission->task_id), // Akan diarahkan ke halaman task
        ]);

        return back()->with('success', 'Komentar pribadi terkirim.');
    }
}
