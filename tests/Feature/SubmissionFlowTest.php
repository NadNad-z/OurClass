<?php

namespace Tests\Feature;

use App\Models\Submission;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\OurClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SubmissionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_submit_and_teacher_grade()
    {
        $this->seed(OurClassSeeder::class);

        Storage::fake('public');

        $student = User::where('role', 'mahasiswa')->first();
        $dosen = User::where('role', 'dosen')->first();
        $task = Task::first();

        $file = UploadedFile::fake()->create('jawaban.pdf', 100);

        // Student submits
        $this->actingAs($student)
            ->post(route('submissions.submit', $task->id), [
                'file_jawaban' => $file,
                'catatan' => 'Ini jawaban saya',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submissions', [
            'task_id' => $task->id,
            'user_id' => $student->id,
            'status' => 'submitted',
        ]);

        $submission = Submission::where('task_id', $task->id)->where('user_id', $student->id)->first();

        // Dosen grades
        $this->actingAs($dosen)
            ->post(route('submissions.grade', $submission->id), [
                'nilai' => 80,
                'feedback' => 'Baik',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('submissions', [
            'id' => $submission->id,
            'nilai' => 80,
            'status' => 'graded',
        ]);
    }
}
