<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Discussion;
use App\Models\DiscussionReply;
use App\Models\Notification;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscussionController extends Controller
{
    /** Simpan diskusi baru di kelas */
    public function store(ClassModel $class, Request $request)
    {
        $user = Auth::user();

        // Pastikan terdaftar di kelas
        if ($class->admin_id !== $user->id && ! $class->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'is_pinned' => 'nullable|boolean',
        ], [
            'judul.required' => 'Judul diskusi wajib diisi.',
            'konten.required' => 'Konten/Pertanyaan diskusi wajib diisi.',
        ]);

        // Hanya admin/dosen kelas yang bisa pin diskusi
        $isPinned = $request->has('is_pinned') && $user->isClassAdmin($class);

        $discussion = Discussion::create([
            'class_id' => $class->id,
            'task_id'  => $request->filled('task_id') ? $request->input('task_id') : null,
            'user_id'  => $user->id,
            'judul'    => $request->judul,
            'konten'   => $request->konten,
            'is_pinned' => $isPinned,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'create_discussion',
            'model_type' => Discussion::class,
            'model_id' => $discussion->id,
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke dosen jika mahasiswa yang posting
        if ($user->role === 'mahasiswa') {
            Notification::create([
                'user_id' => $class->admin_id,
                'class_id' => $class->id,
                'judul' => 'Diskusi Kelas Baru',
                'pesan' => $user->name.' memulai diskusi baru: "'.$discussion->judul.'"',
                'tipe' => 'kelas',
                'link' => route('discussions.show', $discussion->id),
            ]);
        }

        return back()->with('success', 'Diskusi baru berhasil dimulai!');
    }

    /** Detail Halaman Diskusi & Balasan */
    public function show(Discussion $discussion)
    {
        $user = Auth::user();
        $class = $discussion->classModel;

        // Pastikan terdaftar di kelas
        if ($class->admin_id !== $user->id && ! $class->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('classes.index')->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        $discussion->load(['user', 'replies.user']);

        return view('discussions.show', compact('discussion', 'class', 'user'));
    }

    /** Kirim balasan diskusi */
    public function reply(Discussion $discussion, Request $request)
    {
        $user = Auth::user();
        $class = $discussion->classModel;

        // Pastikan terdaftar di kelas
        if ($class->admin_id !== $user->id && ! $class->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        $request->validate([
            'konten' => 'required|string',
        ], [
            'konten.required' => 'Balasan tidak boleh kosong.',
        ]);

        $reply = DiscussionReply::create([
            'discussion_id' => $discussion->id,
            'user_id' => $user->id,
            'konten' => $request->konten,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'reply_discussion',
            'model_type' => DiscussionReply::class,
            'model_id' => $reply->id,
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke pembuat thread diskusi (jika orang lain yang balas)
        if ($discussion->user_id !== $user->id) {
            Notification::create([
                'user_id' => $discussion->user_id,
                'class_id' => $class->id,
                'judul' => 'Diskusi Anda Dibalas',
                'pesan' => $user->name.' membalas diskusi "'.$discussion->judul.'"',
                'tipe' => 'kelas',
                'link' => route('discussions.show', $discussion->id),
            ]);
        }

        return back()->with('success', 'Balasan Anda berhasil dikirim!');
    }
}
