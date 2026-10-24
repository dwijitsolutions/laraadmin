<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Register the first user, who becomes Super Admin, through the real registration flow.
     *
     * @return User
     */
    protected function registerSuperAdmin()
    {
        $response = $this->post('/register', [
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => 'secret1234',
            'password_confirmation' => 'secret1234',
            'context_type' => 'Employee',
        ]);
        $response->assertRedirect();

        $user = User::where('email', 'admin@example.com')->first();
        $this->assertNotNull($user, 'Super Admin user was not registered');
        $this->assertTrue($user->hasRole('SUPER_ADMIN'));

        return $user;
    }
}
