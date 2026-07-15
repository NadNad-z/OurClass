<?php

namespace Tests\Feature;

use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrivateClassUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_class_label_is_visible_on_class_profile()
    {
        $dosen = User::factory()->create(['role' => 'dosen']);

        $class = ClassModel::create([
            'nama_kelas' => 'Kelas Privat UI',
            'kode_unik' => 'PRIVATE',
            'deskripsi' => 'Testing UI label',
            'admin_id' => $dosen->id,
            'color' => '#10B981',
            'is_private' => true,
        ]);

        $response = $this->actingAs($dosen)
            ->get(route('classes.show', $class->id));

        $response->assertStatus(200);
        $response->assertSee('Kelas Privat');
        $response->assertSee('<i data-lucide="lock"', false);
    }
}
