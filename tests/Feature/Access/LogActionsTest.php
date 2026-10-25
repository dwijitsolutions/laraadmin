<?php

namespace Tests\Feature\Access;

use App\Models\LALog;
use Tests\Concerns\ActsAsRoles;
use Tests\TestCase;

/**
 * LA Logs details, as every role.
 */
class LogActionsTest extends TestCase
{
    use ActsAsRoles;

    /**
     * Fresh DB with one user per role.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:refresh');
        $this->artisan('db:seed --class=LaraAdminSeeder');
        $this->createRoleUsers();
    }

    /**
     * Log details open for roles that may view LA Logs.
     *
     * @return void
     */
    public function test_log_details()
    {
        $log = LALog::first();
        $url = '/admin/get_lalog_details/'.$log->id;

        $this->asRole('guest')->post($url)->assertRedirect('/login');
        $this->asRole('customer')->post($url)->assertStatus(403);
        $this->asRole('admin')->post($url)->assertStatus(403)->assertJsonPath('status', 'error');

        foreach (['viewer', 'manager', 'super_admin'] as $role) {
            $this->asRole($role)->post($url)->assertStatus(200)->assertJsonPath('lalog.id', $log->id)->assertJsonPath('lalog.type', $log->type);
        }
    }
}
