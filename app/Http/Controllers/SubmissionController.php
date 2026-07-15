<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\FileUploadSecurity;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Submission;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    use FileUploadSecurity;

    /** Pengumpulan Tugas oleh Mahasiswa */
    public function submit(Task $task, Request $request)
    {
        $user = Auth::user();
        $class = $task->classModel;

        if (! $class) {
            return back()->with('error', 'Kelas untuk tugas ini tidak ditemukan.');
        }

        if ($user->role !== 'mahasiswa') {
            return back()->with('error', 'Hanya mahasiswa yang dapat mengumpulkan tugas.');
        }

        // Cek jika terdaftar di kelas
        if (! $class->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Anda tidak terdaftar di kelas ini.');
        }

        $request->validate([
            'file_jawaban' => 'required|file|mimes:pdf,zip,rar,doc,docx,jpg,jpeg,png|max:20480', // Max 20MB
            'catatan' => 'nullable|string',
        ], [
            'file_jawaban.required' => 'File jawaban wajib diunggah.',
            'file_jawaban.max' => 'Ukuran file jawaban maksimal 20MB.',
        ]);

        // Cari submission yang sudah ada (untuk edit/revisi)
        $submission = Submission::where('task_id', $task->id)
            ->where('user_id', $user->id)
            ->first();

        // Hapus file lama jika merevisi
        if ($submission && $submission->file) {
            Storage::disk('public')->delete($submission->file);
        }

        $file = $request->file('file_jawaban');
        $filename = uniqid('jawab_').'.'.$file->getClientOriginalExtension();
        $filePath = $file->storeAs('jawaban_files', $filename, 'public');

        // Optional virus scan using clamscan if enabled via CLAMAV_SCAN
        if (! $this->scanUploadedFile('public', $filePath)) {
            return back()->with('error', 'File jawaban terdeteksi berbahaya dan telah dihapus.');
        }

        $status = 'submitted';
        if ($task->isOverdue()) {
            $status = 'late';
        }

        $data = [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'file' => $filePath,
            'catatan' => $request->catatan,
            'status' => $status,
            'submitted_at' => now(),
        ];

        if ($submission) {
            $submission->update($data);
            $action = 'update_submission';
        } else {
            $submission = Submission::create($data);
            $action = 'create_submission';
        }

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => Submission::class,
            'model_id' => $submission->id,
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke dosen
        Notification::create([
            'user_id' => $class->admin_id,
            'class_id' => $class->id,
            'judul' => 'Tugas Baru Dikumpulkan',
            'pesan' => $user->name.' mengumpulkan tugas: "'.$task->judul.'"',
            'tipe' => 'tugas',
            'link' => route('tasks.show', $task->id),
        ]);

        return back()->with('success', 'Jawaban Anda berhasil dikumpulkan!');
    }

    /** Penilaian Tugas oleh Dosen */
    public function grade(Submission $submission, Request $request)
    {
        $user = Auth::user();
        $task = $submission->task;
        $class = $task->classModel;

        if (! $user->isClassAdmin($class)) {
            return back()->with('error', 'Hanya pengajar kelas yang dapat memberikan nilai.');
        }

        $request->validate([
            'nilai' => 'required|numeric|min:0|max:'.$task->nilai_max,
            'feedback' => 'nullable|string',
        ], [
            'nilai.required' => 'Nilai wajib diisi.',
            'nilai.max' => 'Nilai tidak boleh melebihi nilai maksimal ('.$task->nilai_max.').',
        ]);

        $submission->update([
            'nilai' => $request->nilai,
            'feedback' => $request->feedback,
            'status' => 'graded',
            'graded_at' => now(),
        ]);

        // Log Activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'grade_submission',
            'model_type' => Submission::class,
            'model_id' => $submission->id,
            'new_values' => $submission->only(['nilai', 'feedback', 'status']),
            'ip_address' => $request->ip(),
        ]);

        // Kirim notifikasi ke mahasiswa bersangkutan
        Notification::create([
            'user_id' => $submission->user_id,
            'class_id' => $class->id,
            'judul' => 'Tugas Dinilai',
            'pesan' => 'Tugas "'.$task->judul.'" Anda telah dinilai: '.$submission->nilai,
            'tipe' => 'tugas',
            'link' => route('tasks.show', $task->id),
        ]);

        return back()->with('success', 'Nilai untuk '.$submission->user->name.' berhasil disimpan!');
    }
}
