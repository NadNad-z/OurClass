<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Database\Seeders\OurClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassJoinTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_join_class_with_code()
    {
        $this->seed(OurClassSeeder::class);

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $class = ClassModel::first();

        $this->actingAs($student)
            ->post(route('classes.join'), [
                'kode_unik' => $class->kode_unik,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('class_user', [
            'class_id' => $class->id,
            'user_id' => $student->id,
        ]);
    }
}
