<?php

namespace Tests\Unit;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/***
 * Basic Page tests
 *
 * php artisan test --filter LABasicTest
 */
class LABasicTest extends DuskTestCase
{
    /**
     * Basic setup before testing.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Fresh DB
        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
    }

    /**
     * Validate Homepage.
     *
     * @return void
     */
    public function testHomepage()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('LaraAdmin');
    }

    /**
     * Test Login Page.
     *
     * @return void
     */
    public function testLoginPage()
    {
        $response = $this->get('/login');
        $response->assertRedirect('/register');
    }

    /**
     * Test Register Page.
     *
     * @return void
     */
    public function testRegisterPage()
    {
        $response = $this->get('/register');
        $response->assertSee('Register Super Admin');
    }

    /**
     * Test required fields on registration page.
     *
     * @return void
     */
    public function testRegistrationPageRequiredFields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Register Super Admin')
                ->click('btn-register')
                ->assertSee('The name field is required')
                ->assertSee('The email field is required')
                ->assertSee('The password field is required');
        });
    }
}
