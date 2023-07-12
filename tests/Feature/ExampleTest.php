<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
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
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
