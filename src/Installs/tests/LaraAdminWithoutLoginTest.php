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
