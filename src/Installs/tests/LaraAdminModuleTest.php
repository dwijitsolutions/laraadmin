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
	 * Module Creation Test Basic
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

	/**
	 * Module Creation Test Full
	 *
	 * @return void
	 */
	public function testModuleCreationFull()
	{
		$this->visit('/admin/modules')
			->see('modules listing')
			->type('Students', 'name')
			->type('fa-user-plus', 'icon')
			->press('Submit')
			->see("StudentsController")
			->type('Name', 'label')
			->type('name', 'colname')
			->select('16', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('10', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Address', 'label')
			->type('address', 'colname')
			->select('1', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('10', 'minlength')
			->type('1000', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Is Public', 'label')
			->type('is_public', 'colname')
			->select('2', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit');
	}

	/**
	 * Test Module Field - Name
	 *
	 * @return void
	 */
	public function testModuleFieldName()
	{
		$this->visit('/admin/modules')
			->see('modules listing')
			->type('Students', 'name')
			->type('fa-user-plus', 'icon')
			->press('Submit')
			->see("StudentsController");
		
		// Create Name Field
		$this->see("StudentsController")
			->type('Name', 'label')
			->type('name', 'colname')
			->select('16', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('10', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit');
		
		// Edit Name Field - As it is
		$this->see("StudentsController")
			->click('edit_name')
			->see('from Student module')
			->press('Update');
		
		// Delete Name Field
		$this->see("StudentsController")
			->click('delete_name');
	}
}
