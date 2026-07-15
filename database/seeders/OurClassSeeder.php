<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OurClassSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin (dosen)
        $dosen = User::factory()->create([
            'name' => 'Dosen Utama',
            'email' => 'dosen@example.com',
            'role' => 'dosen',
            'password' => bcrypt('password'),
        ]);

        // Create students
        $students = User::factory()->count(5)->create([
            'role' => 'mahasiswa',
            'password' => bcrypt('password'),
        ]);

        // Create a class
        $class = ClassModel::create([
            'nama_kelas' => 'Pemrograman Web',
            'deskripsi' => 'Kelas dasar pemrograman web menggunakan Laravel',
            'mata_kuliah' => 'Pemrograman Web',
            'ruangan' => 'Lab 1',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'kode_unik' => Str::upper(Str::random(6)),
            'admin_id' => $dosen->id,
            'is_active' => true,
        ]);

        // Attach students to class
        foreach ($students as $s) {
            $class->members()->attach($s->id, ['role' => 'member']);
        }

        // Create tasks
        $task1 = Task::create([
            'judul' => 'Tugas 1 - Dasar Laravel',
            'deskripsi' => 'Buat project Laravel sederhana',
            'class_id' => $class->id,
            'created_by' => $dosen->id,
            'deadline' => now()->addWeek(),
            'tipe' => 'tugas',
            'nilai_max' => 100,
            'status' => 'published',
        ]);

        $task2 = Task::create([
            'judul' => 'Kuis 1',
            'deskripsi' => 'Kuis singkat',
            'class_id' => $class->id,
            'created_by' => $dosen->id,
            'deadline' => now()->addDays(3),
            'tipe' => 'kuis',
            'nilai_max' => 50,
            'status' => 'published',
        ]);

        // Create a sample submission for first student
        Submission::create([
            'task_id' => $task1->id,
            'user_id' => $students->first()->id,
            'file' => null,
            'catatan' => 'Contoh pengumpulan',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }
}
