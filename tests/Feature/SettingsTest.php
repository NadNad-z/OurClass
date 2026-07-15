<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\OurClassSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_settings_page()
    {
        $this->seed(OurClassSeeder::class);
        $user = User::first();

        $response = $this->actingAs($user)->get(route('settings.index'));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_profile_info_and_avatar()
    {
        $this->seed(OurClassSeeder::class);
        $user = User::first();

        Storage::fake('public');
        $file = UploadedFile::fake()->image('new-avatar.jpg', 100, 100);

        $response = $this->actingAs($user)->post(route('settings.profile'), [
            'name' => 'Nama Baru User',
            'email' => 'newemail@example.com',
            'phone' => '08999999999',
            'avatar' => $file,
        ]);

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertEquals('Nama Baru User', $user->name);
        $this->assertEquals('newemail@example.com', $user->email);
        $this->assertEquals('08999999999', $user->phone);
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_change_password()
    {
        $this->seed(OurClassSeeder::class);
        
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post(route('settings.password'), [
            'current_password' => 'password123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password()
    {
        $this->seed(OurClassSeeder::class);
        
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->post(route('settings.password'), [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
        $user->refresh();
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_can_select_default_avatar()
    {
        $this->seed(OurClassSeeder::class);
        $user = User::first();

        $defaultAvatarUrl = 'https://api.dicebear.com/7.x/adventurer/svg?seed=Felix';

        $response = $this->actingAs($user)->post(route('settings.profile'), [
            'name' => 'Nama User Baru',
            'email' => $user->email,
            'default_avatar' => $defaultAvatarUrl,
        ]);

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertEquals($defaultAvatarUrl, $user->avatar);
    }
}
