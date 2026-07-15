<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Database\Seeders\OurClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_user_can_create_class()
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        // Dosen should be able to create
        $this->actingAs($dosen)
            ->post(route('classes.store'), [
                'nama_kelas' => 'Kelas Test Dosen',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classes', ['nama_kelas' => 'Kelas Test Dosen']);

        // Mahasiswa should also be able to create now (Mode Kelas Bersama / Mandiri)
        $this->actingAs($mahasiswa)
            ->post(route('classes.store'), [
                'nama_kelas' => 'Kelas Test Mahasiswa',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('classes', ['nama_kelas' => 'Kelas Test Mahasiswa']);
    }

    public function test_any_user_can_join_class()
    {
        $this->seed(OurClassSeeder::class);

        $student = User::where('role', 'mahasiswa')->first();
        $dosen = User::where('role', 'dosen')->first();
        $class = ClassModel::first();

        // Mahasiswa can join
        $this->actingAs($student)
            ->post(route('classes.join'), ['kode_unik' => $class->kode_unik])
            ->assertRedirect();

        $this->assertDatabaseHas('class_user', [
            'class_id' => $class->id,
            'user_id' => $student->id,
        ]);

        // Dosen can also join now
        $this->actingAs($dosen)
            ->post(route('classes.join'), ['kode_unik' => $class->kode_unik])
            ->assertRedirect();

        $this->assertDatabaseHas('class_user', [
            'class_id' => $class->id,
            'user_id' => $dosen->id,
        ]);
    }
}
