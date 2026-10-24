<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Tests\TestCase;

class AdminSmokeTest extends TestCase
{
    /**
     * Admin index pages of every seeded module.
     *
     * @var array
     */
    protected $indexPages = [
        'dashboard', 'users', 'employees', 'customers', 'departments', 'roles', 'permissions',
        'uploads', 'backups', 'blog_categories', 'blog_posts', 'la_logs',
        'la_modules', 'la_menus', 'la_configs', 'hierarchy_view',
    ];

    /**
     * DataTables Ajax endpoints of every seeded module.
     *
     * @var array
     */
    protected $dtAjaxEndpoints = [
        'user_dt_ajax', 'employee_dt_ajax', 'customer_dt_ajax', 'department_dt_ajax', 'role_dt_ajax',
        'permission_dt_ajax', 'backup_dt_ajax', 'blog_category_dt_ajax', 'blog_post_dt_ajax', 'la_log_dt_ajax',
    ];

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

    /**
     * Public pages render. Until the first user registers, login redirects to registration and vice versa after.
     *
     * @return void
     */
    public function test_public_pages()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/'.config('laraadmin.blogRoute'))->assertStatus(200);
        $this->get('/login')->assertRedirect('register');
        $this->get('/register')->assertStatus(200);

        $this->registerSuperAdmin();
        auth()->logout();

        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertRedirect('login');
    }

    /**
     * Guests are redirected away from the admin panel.
     *
     * @return void
     */
    public function test_admin_requires_login()
    {
        $this->get('/'.config('laraadmin.adminRoute'))->assertRedirect('/login');
    }

    /**
     * Every admin index page, DataTables endpoint and the Super Admin's employee pages render.
     *
     * @return void
     */
    public function test_admin_pages_render_for_super_admin()
    {
        $user = $this->registerSuperAdmin();
        $admin = '/'.config('laraadmin.adminRoute');

        foreach ($this->indexPages as $page) {
            $this->actingAs($user)->get($admin.'/'.$page)->assertStatus(200);
        }
        foreach ($this->dtAjaxEndpoints as $endpoint) {
            $this->actingAs($user)->get($admin.'/'.$endpoint)->assertStatus(200)->assertJsonStructure(['data']);
        }

        $this->actingAs($user)->get($admin.'/employees/'.$user->context_id)->assertStatus(200);
        $this->actingAs($user)->get($admin.'/employees/'.$user->context_id.'/edit')->assertStatus(200);
        $this->actingAs($user)->get($admin.'/users/'.$user->id)->assertRedirect();
    }

    /**
     * A record can be created through a module controller (LAModule::insert, validation, observer and LALog).
     *
     * @return void
     */
    public function test_create_department()
    {
        $user = $this->registerSuperAdmin();

        $response = $this->actingAs($user)->post('/'.config('laraadmin.adminRoute').'/departments', [
            'name' => 'Engineering',
            'tags' => ['core', 'tech'],
            'color' => '#123456',
        ]);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $department = Department::where('name', 'Engineering')->first();
        $this->assertNotNull($department);
        $this->assertSame('#123456', $department->color);

        $this->actingAs($user)->get('/'.config('laraadmin.adminRoute').'/departments/'.$department->id)->assertStatus(200);
    }
}
