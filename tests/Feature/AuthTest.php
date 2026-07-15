<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_and_login()
    {
        $email = 'newstudent@example.com';

        // Registration
        $response = $this->post('/register', [
            'name' => 'New Student',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'mahasiswa',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => $email]);

        // Logout then login
        $this->post('/logout');
        $login = $this->post('/login', ['email' => $email, 'password' => 'password']);
        $login->assertRedirect('/dashboard');
    }
}
