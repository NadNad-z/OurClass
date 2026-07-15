<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FileUploadSecurity;
use App\Models\ActivityLog;
use App\Models\ClassModel;
use App\Models\Discussion;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    use FileUploadSecurity;

    /** Simpan tugas baru (Dosen) */
    public function store(ClassModel $class, Request $request)
    {
        $user = Auth::user();
        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat membuat tugas.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date_format:Y-m-d\TH:i',
            'tipe' => 'required|in:tugas,kuis,ujian',
            'nilai_max' => 'required|integer|min:0|max:1000',
            'file_soal' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar,png,jpg,jpeg|max:10240', // Max 10MB
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'deadline.required' => 'Waktu tenggat (deadline) wajib diisi.',
        ]);

        $filePath = null;
        if ($request->hasFile('file_soal')) {
            $file = $request->file('file_soal');
            $filename = uniqid('soal_').'.'.$file->getClientOriginalExtension();
            $filePath = $file->storeAs('soal_files', $filename, 'public');

            // Optional virus scan using clamscan if enabled via CLAMAV_SCAN
            if (! $this->scanUploadedFile('public', $filePath)) {
                return back()->with('error', 'File diunggah terdeteksi berbahaya dan telah dihapus.');
            }
        }

        $task = Task::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'class_id' => $class->id,
            'created_by' => $user->id,
            'deadline' => $request->deadline,
            'file_soal' => $filePath,
            'tipe' => $request->tipe,
            'nilai_max' => $request->nilai_max,
            'status' => 'published',
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'create_task',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'new_values' => $task->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke semua mahasiswa di kelas
        $students = $class->students()->get();
        foreach ($students as $student) {
            Notification::create([
                'user_id' => $student->id,
                'class_id' => $class->id,
                'judul' => 'Tugas Baru Diterbitkan',
                'pesan' => 'Dosen mengunggah '.$task->tipe.' baru: "'.$task->judul.'"',
                'tipe' => 'tugas',
                'link' => route('tasks.show', $task->id),
            ]);
        }

        return back()->with('success', 'Tugas "'.$task->judul.'" berhasil diterbitkan!');
    }

    /** Detail Halaman Tugas */
    public function show(Task $task)
    {
        $user = Auth::user();
        $class = $task->classModel;

        // Pastikan user terdaftar di kelas
        if ($class->admin_id !== $user->id && ! $class->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('classes.index')->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        $task->load(['creator', 'classModel']);

        // Load task-specific discussions (Q&A) with their replies
        $taskDiscussions = Discussion::where('task_id', $task->id)
            ->with(['user', 'replies.user'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        if ($user->isClassAdmin($class)) {
            // Jika Dosen: load semua submissions
            $submissions = Submission::where('task_id', $task->id)
                ->with(['user', 'privateComments.user'])
                ->orderBy('submitted_at', 'desc')
                ->get();

            return view('tasks.show', compact('task', 'submissions', 'user', 'class', 'taskDiscussions'));
        } else {
            // Jika Mahasiswa: load submission sendiri
            $submission = Submission::where('task_id', $task->id)
                ->where('user_id', $user->id)
                ->with('privateComments.user')
                ->first();

            return view('tasks.show', compact('task', 'submission', 'user', 'class', 'taskDiscussions'));
        }
    }

    /** Form Edit Tugas (Dosen) */
    public function edit(Task $task)
    {
        $user  = Auth::user();
        $class = $task->classModel;

        if (! $user->isClassAdmin($class)) {
            return redirect()->route('tasks.show', $task->id)
                ->with('error', 'Hanya pengajar kelas yang dapat mengedit tugas.');
        }

        $task->load(['creator', 'classModel']);

        $taskDiscussions = Discussion::where('task_id', $task->id)
            ->with(['user', 'replies.user'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        $submissions = Submission::where('task_id', $task->id)
            ->with('user')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('tasks.show', compact('task', 'submissions', 'user', 'class', 'taskDiscussions'))
            ->with('editMode', true);
    }

    /** Proses Update Tugas (Dosen) */
    public function update(Task $task, Request $request)
    {
        $user  = Auth::user();
        $class = $task->classModel;

        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat mengedit tugas.');
        }

        $request->validate([
            'judul'        => 'required|string|max:255',
            'deskripsi'    => 'nullable|string',
            'deadline'     => 'required|date_format:Y-m-d\TH:i',
            'tipe'         => 'required|in:tugas,kuis,ujian',
            'nilai_max'    => 'required|integer|min:0|max:1000',
            'file_soal'    => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar,png,jpg,jpeg|max:10240',
            'hapus_file'   => 'nullable|boolean',
        ], [
            'judul.required'    => 'Judul tugas wajib diisi.',
            'deadline.required' => 'Waktu tenggat (deadline) wajib diisi.',
        ]);

        $filePath = $task->file_soal;

        // Jika user meminta hapus file lama
        if ($request->boolean('hapus_file') && $filePath) {
            Storage::disk('public')->delete($filePath);
            $filePath = null;
        }

        // Jika ada file baru diunggah
        if ($request->hasFile('file_soal')) {
            // Hapus file lama terlebih dahulu
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $file     = $request->file('file_soal');
            $filename = uniqid('soal_').'.'.$file->getClientOriginalExtension();
            $filePath = $file->storeAs('soal_files', $filename, 'public');

            if (! $this->scanUploadedFile('public', $filePath)) {
                return back()->with('error', 'File diunggah terdeteksi berbahaya dan telah dihapus.');
            }
        }

        $task->update([
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'deadline'  => $request->deadline,
            'tipe'      => $request->tipe,
            'nilai_max' => $request->nilai_max,
            'file_soal' => $filePath,
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id'    => $user->id,
            'action'     => 'update_task',
            'model_type' => Task::class,
            'model_id'   => $task->id,
            'new_values' => $task->fresh()->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Notifikasi ke mahasiswa bahwa tugas diperbarui
        $students = $class->students()->get();
        foreach ($students as $student) {
            Notification::create([
                'user_id'  => $student->id,
                'class_id' => $class->id,
                'judul'    => 'Tugas Diperbarui',
                'pesan'    => 'Dosen memperbarui '.$task->tipe.': "'.$task->judul.'"',
                'tipe'     => 'tugas',
                'link'     => route('tasks.show', $task->id),
            ]);
        }

        return redirect()->route('tasks.show', $task->id)
            ->with('success', 'Tugas "'.$task->judul.'" berhasil diperbarui!');
    }

    /** Hapus Tugas (Dosen) */
    public function destroy(Task $task, Request $request)
    {
        $user = Auth::user();
        $class = $task->classModel;

        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat menghapus tugas ini.');
        }

        // Log Activity before delete
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'delete_task',
            'model_type' => Task::class,
            'model_id' => $task->id,
            'old_values' => $task->toArray(),
            'ip_address' => $request->ip(),
        ]);

        // Hapus file soal dari storage jika ada
        if ($task->file_soal) {
            Storage::disk('public')->delete($task->file_soal);
        }

        $task->delete();

        return redirect()->route('classes.show', $class->id)
            ->with('success', 'Tugas berhasil dihapus.');
    }
}
