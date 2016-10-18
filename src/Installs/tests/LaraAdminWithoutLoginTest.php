<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LaraAdminWithoutLoginTest extends TestCase
{
	use DatabaseMigrations;

	/**
     * Basic setup before testing
     *
     * @return void
     */
    public function setUp()
    {
		parent::setUp();
		// Generate Seeds
		$this->artisan('db:seed');
    }

	/**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
		$this->visit('/')
             ->see('LaraAdmin');
    }

	/**
     * Test Login Page.
     *
     * @return void
     */
    public function testLoginPage()
    {
		$this->visit('/login')
            ->seePageIs('/register');
    }

	/**
     * Test Register Page.
     *
     * @return void
     */
    public function testRegisterPage()
    {
        $this->visit('/register')
            ->see('Register Super Admin');
    }

	/**
     * Test required fields on registration page.
     *
     * @return void
     */
    public function testRequiredFieldsOnRegistrationPage()
    {
        $this->visit('/register')
            ->press('Register')
            ->see('The name field is required')
            ->see('The email field is required')
            ->see('The password field is required');
    }

	/**
     * Test Registration
     *
     * @return void
     */
    public function testRegistration()
    {
        $this->visit('/register')
            ->see('Register Super Admin')
			->type('Taylor Otwell', 'name')
			->type('test@example.com', 'email')
			->type('12345678', 'password')
			->type('12345678', 'password_confirmation')
			->press('Register')
			->seePageIs('/')
			->click('Taylor Otwell')
			->seePageIs('/admin')
			->see('Dashboard')
			->visit('/logout')
			->seePageIs('/')
			->click('Login')
			->type('test@example.com', 'email')
			->type('12345678', 'password')
			->press('Sign In')
			->seePageIs('/')
			->click('Taylor Otwell')
			->seePageIs('/admin')
			->see('Dashboard');
    }

	/**
     * Test Password reset Page.
     *
     * @return void
     */
    public function testPasswordResetPage()
    {
        $this->visit('/password/reset')
            ->see('Reset Password');
    }

	/**
     * Test send password reset.
     *
     * @return void
     */
    public function testSendPasswordReset()
    {
        $user = factory(App\User::class)->create();

        $this->visit('password/reset')
            ->type($user->email, 'email')
            ->press('Send Password Reset Link')
            ->see('We have e-mailed your password reset link!');
    }

	/**
     * Test send password reset user not exists.
     *
     * @return void
     */
    public function testSendPasswordResetUserNotExists()
    {
        $this->visit('password/reset')
            ->type('notexistingemail@gmail.com', 'email')
            ->press('Send Password Reset Link')
            ->see('There were some problems with your input');
    }

	/**
     * Test home page is only for authorized Users.
     *
     * @return void
     */
    public function testHomePageForUnauthenticatedUsers()
    {
        $this->visit('/admin')
            ->seePageIs('/register');
    }

	/**
     * Test 404 Error page.
     *
     * @return void
     */
    public function test404Page()
    {
        $this->get('asdasdjlapmnnk')
            ->seeStatusCode(404)
            ->see('Record not found');
    }
}
