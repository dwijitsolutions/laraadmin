<?php

namespace Tests\Feature\Access;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\LALog;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Registration, login / logout (and their LA Logs), password reset and confirmation, API authentication.
 */
class AuthTest extends TestCase
{
    /**
     * Fresh DB.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
    }

    /**
     * The first user registers through /register and becomes Super Admin; the form is then closed.
     *
     * @return void
     */
    public function test_first_user_registers_as_super_admin()
    {
        $this->get('/login')->assertRedirect('register');
        $this->get('/register')->assertStatus(200)->assertSee('name="context_type" value="Employee"', false);

        $user = $this->registerSuperAdmin();
        $this->assertSame('Super Admin', Employee::find($user->context_id)->designation);
        $this->assertSame(1, LALog::where('type', 'USER_CREATED')->count());
        $this->get('/admin')->assertStatus(200);

        auth()->logout();
        $this->get('/register')->assertRedirect('login');
    }

    /**
     * Once a user exists, posting an Employee registration is refused: it would create another Super Admin.
     *
     * @return void
     */
    public function test_nobody_can_register_as_super_admin_once_a_user_exists()
    {
        $this->registerSuperAdmin();
        auth()->logout();

        $this->post('/register', [
            'name' => 'Intruder', 'email' => 'intruder@example.com', 'password' => 'secret1234',
            'password_confirmation' => 'secret1234', 'context_type' => 'Employee',
        ])->assertStatus(403);

        $this->assertSame(1, User::count());
        $this->assertSame(1, Employee::count());
        $this->assertGuest();
    }

    /**
     * Registration needs a known context type.
     *
     * @return void
     */
    public function test_registration_needs_a_context_type()
    {
        foreach ([null, 'Admin'] as $contextType) {
            $this->post('/register', array_filter([
                'name' => 'Someone', 'email' => 'someone@example.com', 'password' => 'secret1234',
                'password_confirmation' => 'secret1234', 'context_type' => $contextType,
            ]))->assertRedirect()->assertSessionHasErrors('context_type');
        }

        $this->assertSame(0, User::count());
    }

    /**
     * Customers register themselves, get the CUSTOMER role and no admin panel.
     *
     * @return void
     */
    public function test_customers_can_register()
    {
        $this->registerSuperAdmin();
        auth()->logout();

        $this->get('/customers/register')->assertStatus(200)->assertSee('name="context_type" value="Customer"', false);

        $customer = [
            'name' => 'Customer One', 'email' => 'customer1@example.com', 'password' => 'secret1234',
            'password_confirmation' => 'secret1234', 'context_type' => 'Customer',
        ];
        $this->post('/register', $customer)->assertSessionHasErrors('phone');

        $this->post('/register', $customer + ['phone' => '9876543210'])->assertRedirect('/');
        $user = User::where('email', 'customer1@example.com')->first();
        $this->assertTrue($user->hasRole('CUSTOMER'));
        $this->assertSame('9876543210', Customer::find($user->context_id)->phone_primary);
        $this->assertAuthenticatedAs($user);

        $this->get('/admin')->assertStatus(403);
    }

    /**
     * Login and logout, through GET and POST, are written to LA Logs; failed logins are not.
     *
     * @return void
     */
    public function test_login_and_logout_are_logged()
    {
        $user = $this->registerSuperAdmin(); // registering logs the user in
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();

        $this->get('/login')->assertStatus(200);
        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'wrong-password'])
            ->assertRedirect()->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'secret1234'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/login')->assertRedirect('/home'); // RouteServiceProvider::HOME

        $this->get('/logout')->assertRedirect('/');
        $this->assertGuest();

        $logs = LALog::where('user_id', $user->id)->whereIn('type', ['USER_LOGIN', 'USER_LOGOUT'])->orderBy('id')->pluck('type')->all();
        $this->assertSame(['USER_LOGIN', 'USER_LOGOUT', 'USER_LOGIN', 'USER_LOGOUT'], $logs);
        $this->assertSame('User Super Admin Login', LALog::where('type', 'USER_LOGIN')->value('title'));
    }

    /**
     * Forgotten password: request a link, open it, set a new password.
     *
     * @return void
     */
    public function test_password_reset()
    {
        Notification::fake();
        $user = $this->registerSuperAdmin();
        auth()->logout();

        $this->get('/password/reset')->assertStatus(200);
        $this->post('/password/email', ['email' => 'admin@example.com'])->assertRedirect()->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->get('/password/reset/'.$token)->assertStatus(200);
        $this->post('/password/reset', [
            'token' => $token, 'email' => 'admin@example.com', 'password' => 'new-secret-1234',
            'password_confirmation' => 'new-secret-1234',
        ])->assertRedirect('/home');

        $this->assertTrue(Hash::check('new-secret-1234', $user->fresh()->password));
    }

    /**
     * The password confirmation page renders in LaraAdmin's layout and accepts the current password.
     *
     * @return void
     */
    public function test_password_confirmation()
    {
        $this->get('/password/confirm')->assertRedirect('/login');

        $user = $this->registerSuperAdmin();
        $this->actingAs($user)->get('/password/confirm')->assertStatus(200)->assertSee('Confirm Password');
        $this->actingAs($user)->post('/password/confirm', ['password' => 'wrong-password'])->assertSessionHasErrors('password');
        $this->actingAs($user)->post('/password/confirm', ['password' => 'secret1234'])->assertRedirect()->assertSessionHas('auth.password_confirmed_at');
    }

    /**
     * The API needs a Sanctum token.
     *
     * @return void
     */
    public function test_api_user_needs_a_token()
    {
        $user = $this->registerSuperAdmin();
        auth()->logout();

        $this->getJson('/api/user')->assertStatus(401);

        Sanctum::actingAs($user);
        $this->getJson('/api/user')->assertStatus(200)->assertJsonPath('email', 'admin@example.com')->assertJsonMissingPath('password');
    }
}
