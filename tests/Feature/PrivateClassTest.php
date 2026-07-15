<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Database\Seeders\OurClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_class_cannot_be_joined_by_students()
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $class = ClassModel::create([
            'nama_kelas' => 'Kelas Privat Test',
            'kode_unik' => 'PRIVAT',
            'deskripsi' => 'Kelas tes privat',
            'admin_id' => $dosen->id,
            'color' => '#10B981',
            'is_private' => true,
        ]);

        $student = User::factory()->create(['role' => 'mahasiswa']);

        $this->actingAs($student)
            ->post(route('classes.join'), ['kode_unik' => $class->kode_unik])
            ->assertRedirect()
            ->assertSessionHas('error', 'Kelas ini bersifat privat dan tidak dapat diikuti oleh anggota lain.');

        $this->assertDatabaseMissing('class_user', [
            'class_id' => $class->id,
            'user_id' => $student->id,
        ]);
    }

    public function test_private_class_owner_can_still_view_its_page()
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $class = ClassModel::create([
            'nama_kelas' => 'Kelas Privat Owner',
            'kode_unik' => 'OWNER1',
            'deskripsi' => 'Kelas privat milik pemilik',
            'admin_id' => $dosen->id,
            'color' => '#10B981',
            'is_private' => true,
        ]);

        $this->actingAs($dosen)
            ->get(route('classes.show', $class->id))
            ->assertOk();
    }
}
