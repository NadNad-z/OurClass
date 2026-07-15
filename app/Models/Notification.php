<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'class_id',
        'judul',
        'pesan',
        'tipe',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    // === Relationships ===

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function getTranslatedJudulAttribute()
    {
        if (app()->getLocale() == 'id') return $this->judul;
        
        $titles = [
            'Tugas Baru Diterbitkan' => 'New Task Published',
            'Tugas Baru Dikumpulkan' => 'New Task Submitted',
            'Tugas Dinilai' => 'Task Graded',
            'Jadwal Kelas Ditambahkan' => 'Class Schedule Added',
            'Diskusi Kelas Baru' => 'New Class Discussion',
            'Diskusi Anda Dibalas' => 'Your Discussion Replied',
            'Mahasiswa Baru Bergabung' => 'New Student Joined',
            'Pengingat Deadline Tugas' => 'Task Deadline Reminder',
            'Pengingat Kelas' => 'Class Reminder',
        ];
        
        return $titles[$this->judul] ?? $this->judul;
    }

    public function getTranslatedPesanAttribute()
    {
        if (app()->getLocale() == 'id') return $this->pesan;
        
        $pesan = $this->pesan;
        $pesan = str_replace(' memulai diskusi baru: ', ' started a new discussion: ', $pesan);
        $pesan = str_replace(' membalas diskusi ', ' replied to discussion ', $pesan);
        $pesan = str_replace('Dosen mengunggah ', 'Lecturer uploaded ', $pesan);
        $pesan = str_replace(' baru: ', ' new: ', $pesan);
        $pesan = str_replace(' mengumpulkan tugas: ', ' submitted task: ', $pesan);
        $pesan = preg_replace('/Tugas "(.*)" Anda telah dinilai: /', 'Your task "$1" has been graded: ', $pesan);
        $pesan = preg_replace('/Jadwal baru ditambahkan untuk hari (.*) pukul (.*)/', 'New schedule added for $1 at $2', $pesan);
        $pesan = preg_replace('/Tugas "(.*)" dari kelas (.*) akan jatuh tempo dalam waktu kurang dari 24 jam\./', 'Task "$1" from class $2 is due in less than 24 hours.', $pesan);
        $pesan = preg_replace('/Kelas (.*) akan dimulai pada pukul (.*) di ruangan (.*)\./', 'Class $1 will start at $2 in room $3.', $pesan);
        $pesan = str_replace(' telah bergabung ke kelas ', ' has joined class ', $pesan);
        
        $days = ['Senin' => 'Monday', 'Selasa' => 'Tuesday', 'Rabu' => 'Wednesday', 'Kamis' => 'Thursday', 'Jumat' => 'Friday', 'Sabtu' => 'Saturday', 'Minggu' => 'Sunday'];
        foreach ($days as $id => $en) {
            $pesan = str_replace($id, $en, $pesan);
        }
        
        return $pesan;
    }
}
