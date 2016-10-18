<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LaraAdminModuleTest extends TestCase
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

		// Register Super Admin
		$this->visit('/register')
			->type('Taylor Otwell', 'name')
			->type('test@example.com', 'email')
			->type('12345678', 'password')
			->type('12345678', 'password_confirmation')
			->press('Register')
			->seePageIs('/');
    }

	/**
     * Module Creation Test
     *
     * @return void
     */
    public function testModuleCreation()
    {
		$this->visit('/admin/modules')
             ->see('modules listing')
			 ->type('Students', 'name')
			 ->type('fa-user-plus', 'icon')
			 ->press('Submit');
    }
}
