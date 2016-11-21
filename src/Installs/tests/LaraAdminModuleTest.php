<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LaraAdminModuleTest extends TestCase
{
	use DatabaseMigrations;

	var $probable_module_id = 9;

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
			->press('Submit');
		$this->see("StudentsController")
			->type('Name', 'label')
			->type('name', 'colname')
			->select('16', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('10', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see('StudentsController')
			->click('view_col_name')
			->dontSee('view_col_name')
			->see('generate_migr_crud');
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
			->check('required');
		$this->see("StudentsController")
			->type('Price', 'label')
			->type('price', 'colname')
			->select('3', 'field_type')
			->type('', 'defaultvalue')
			->type('0', 'minlength')
			->type('10', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Price', 'label')
			->type('price', 'colname')
			->select('3', 'field_type')
			->type('', 'defaultvalue')
			->type('0', 'minlength')
			->type('10', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Date of Release', 'label')
			->type('date_release', 'colname')
			->select('4', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Date of Release', 'label')
			->type('date_release', 'colname')
			->select('4', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Start Time', 'label')
			->type('time_started', 'colname')
			->select('5', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->uncheck('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Weight', 'label')
			->type('weight', 'colname')
			->select('6', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Publisher', 'label')
			->type('publisher', 'colname')
			->select('7', 'field_type')
			->type('Marvel', 'defaultvalue')
			->check('required')
			->select('list', 'popup_value_type')
			->select(['Marvel', 'Bloomsbury', 'Universal'], 'popup_vals_list')
			->press('Submit')
			->see('Marvel')
			->see('Bloomsbury')
			->see('Universal');
		$this->see("StudentsController")
			->type('Assigned to', 'label')
			->type('assigned_to', 'colname')
			->select('7', 'field_type')
			->type('1', 'defaultvalue')
			->check('required')
			->select('table', 'popup_value_type')
			->select('employees', 'popup_vals_table')
			->press('Submit')
			->see('@employees');
		$this->see("StudentsController")
			->type('Email', 'label')
			->type('email', 'colname')
			->select('8', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('', 'maxlength')
			->check('required')
			->press('Submit');
		$this->see("StudentsController")
			->type('Test File', 'label')
			->type('test_file', 'colname')
			->select('9', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit')
			->see('test_file');
		$this->see("StudentsController")
			->type('Pressure', 'label')
			->type('pressure', 'colname')
			->select('10', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('', 'maxlength')
			->check('required')
			->press('Submit')
			->see('Pressure');
		$this->see("StudentsController")
			->type('Biography', 'label')
			->type('biography', 'colname')
			->select('11', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit')
			->see('Biography');
		$this->see("StudentsController")
			->type('Profile Image', 'label')
			->type('profile_image', 'colname')
			->select('12', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit')
			->see('profile_image');
		$this->see("StudentsController")
			->type('Pages', 'label')
			->type('pages', 'colname')
			->select('13', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('0', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see('pages');
		$this->see("StudentsController")
			->type('Mobile', 'label')
			->type('mobile', 'colname')
			->select('14', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('0', 'minlength')
			->type('20', 'maxlength')
			->check('required')
			->press('Submit')
			->see('Mobile');
		$this->see("StudentsController")
			->type('Media Type', 'label')
			->type('media_type', 'colname')
			->select('15', 'field_type')
			->type('["Bloomsbury","Universal"]', 'defaultvalue')
			->check('required')
			->select('list', 'popup_value_type')
			->select(['Marvel', 'Bloomsbury', 'Universal'], 'popup_vals_list')
			->press('Submit')
			->see('Media Type');
		$this->see("StudentsController")
			->type('Media Role', 'label')
			->type('media_role', 'colname')
			->select('15', 'field_type')
			->type('1', 'defaultvalue')
			->check('required')
			->select('table', 'popup_value_type')
			->select('roles', 'popup_vals_table')
			->press('Submit')
			->see('@roles');
		$this->see("StudentsController")
			->type('User Password', 'label')
			->type('password', 'colname')
			->select('17', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->type('0', 'minlength')
			->type('64', 'maxlength')
			->press('Submit')
			->see('User Password');
		$this->see("StudentsController")
			->type('User Status', 'label')
			->type('user_status', 'colname')
			->select('18', 'field_type')
			->type('Bloomsbury', 'defaultvalue')
			->check('required')
			->select('list', 'popup_value_type')
			->select(['Marvel', 'Bloomsbury', 'Universal'], 'popup_vals_list')
			->press('Submit')
			->see('User Status');
		$this->see("StudentsController")
			->type('Author', 'label')
			->type('author', 'colname')
			->select('19', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('0', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see('Author');
		$this->see("StudentsController")
			->type('Genre', 'label')
			->type('genre', 'colname')
			->select('20', 'field_type')
			->type('Bloomsbury', 'defaultvalue')
			->check('required')
			->select('list', 'popup_value_type')
			->select(['Marvel', 'Bloomsbury', 'Universal'], 'popup_vals_list')
			->press('Submit')
			->see('Genre');
		$this->see("StudentsController")
			->type('Description', 'label')
			->type('description', 'colname')
			->select('21', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('1000', 'maxlength')
			->check('required')
			->press('Submit')
			->see('Description');
		$this->see("StudentsController")
			->type('Introduction', 'label')
			->type('short_intro', 'colname')
			->select('22', 'field_type')
			->uncheck('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see('short_intro');
		$this->see("StudentsController")
			->type('Website', 'label')
			->type('website', 'colname')
			->select('23', 'field_type')
			->check('unique')
			->type('', 'defaultvalue')
			->type('', 'minlength')
			->type('100', 'maxlength')
			->check('required')
			->press('Submit')
			->see('website');
		$this->see("StudentsController")
			->type('Test Files', 'label')
			->type('test_files', 'colname')
			->select('24', 'field_type')
			->type('', 'defaultvalue')
			->check('required')
			->press('Submit')
			->see('test_files');
		$response = $this->call('GET', '/admin/module_generate_migr_crud/'.$this->probable_module_id);
		$this->assertEquals(200, $response->status());
		$this->visit('/admin/modules/'.$this->probable_module_id)
			->see('Module Generated')
			->see('Update Module')
			->see('StudentsController');
		
	}

	/**
	 * Module Usage Test Full
	 *
	 * @return void
	 */
	public function testModuleUsageFull()
	{
		$this->visit('/admin/students')
			->see('Students listing')
			->see('Add Student');
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
