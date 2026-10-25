<?php

namespace Tests\Feature\Access;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\LALog;
use App\Models\LAModule;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ActsAsRoles;
use Tests\Concerns\FillsForms;
use Tests\TestCase;

/**
 * Fills and submits the add and edit form of every module, as every role, then deletes the record.
 *
 * Super Admin adds, the manager role edits and deletes; guests, customers, the admin role (no module access) and
 * the read-only viewer role are refused and change nothing.
 */
class ModuleFormsTest extends TestCase
{
    use ActsAsRoles;
    use FillsForms;

    /**
     * Roles that may not add, edit or delete records.
     *
     * @var array<int, string>
     */
    protected const REFUSED = ['guest', 'customer', 'admin', 'viewer'];

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
     * Employees: the form also assigns roles and creates the login user.
     *
     * @return void
     */
    public function test_employees_form()
    {
        $admin = Role::where('name', 'ADMIN')->first();
        $viewer = Role::where('name', 'VIEWER')->first();

        $user = null;
        $this->checkModuleForm(
            'Employees',
            'employees',
            'employee',
            true,
            ['role[]' => [$admin->id]],
            ['role[]' => [$viewer->id]],
            function ($employee) use (&$user) {
                $user = User::get($employee->id, 'Employee');
                $this->assertNotNull($user, 'The employee has no login user with an Employee role');
                $this->assertSame($employee->email_primary, $user->email);
                $this->assertSame(['VIEWER'], $user->roles()->pluck('name')->all());
            }
        );

        $this->assertSame(1, LALog::where('type', 'USER_CREATED')->where('context_id', $user->id)->count());
        $this->assertSame(1, LALog::where('type', 'USER_UPDATED')->where('context_id', $user->id)->count());
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Editing the Super Admin's own employee record keeps the SUPER_ADMIN role, which the form doesn't offer.
     *
     * @return void
     */
    public function test_editing_super_admin_keeps_the_role()
    {
        $user = $this->users['super_admin'];
        $module = LAModule::get('Employees');
        $html = $this->asRole('super_admin')->get('/admin/employees/'.$user->context_id.'/edit')->assertStatus(200)->getContent();
        $pairs = $this->fillControls($module, $this->formControls($html, 'employee-edit-form', $choices), $choices, 'Self Edit');

        $this->asRole('super_admin')->submitAjax('PUT', '/admin/employees/'.$user->context_id, $pairs)
            ->assertStatus(200)->assertJsonPath('status', 'success');

        $this->assertTrue($user->fresh()->hasRole('SUPER_ADMIN'));
        $this->assertSame('Self Edit Name', Employee::find($user->context_id)->name);
    }

    /**
     * A user who may edit employees cannot make anyone Super Admin, even with a crafted request.
     *
     * @return void
     */
    public function test_employee_form_cannot_grant_super_admin()
    {
        $module = LAModule::get('Employees');
        $html = $this->asRole('manager')->get('/admin/employees')->assertStatus(200)->getContent();
        $superAdmin = Role::where('name', 'SUPER_ADMIN')->first();
        $pairs = $this->fillControls($module, $this->formControls($html, 'employee-add-form', $choices), $choices, 'Sneaky', ['role[]' => [$superAdmin->id]]);

        $this->asRole('manager')->submitAjax('POST', '/admin/employees', $pairs)->assertStatus(201);

        $employee = Employee::where('name', 'Sneaky Name')->first();
        $this->assertFalse(User::get($employee->id, 'Employee')?->hasRole('SUPER_ADMIN') ?? false);
        $this->assertSame(1, Role::where('name', 'SUPER_ADMIN')->first()->users()->count());
    }

    /**
     * Customers: the form creates a login user with the CUSTOMER role.
     *
     * @return void
     */
    public function test_customers_form()
    {
        $user = null;
        $this->checkModuleForm('Customers', 'customers', 'customer', true, ['create_user' => 'on'], [], function ($customer) use (&$user) {
            $user = User::get($customer->id, 'Customer');
            $this->assertNotNull($user, 'The customer has no login user');
            $this->assertSame($customer->email_primary, $user->email);
        });

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * Departments.
     *
     * @return void
     */
    public function test_departments_form()
    {
        $department = $this->checkModuleForm('Departments', 'departments', 'department', true);

        $this->assertSame(['alpha', 'beta'], json_decode($department->tags));
        $this->assertSame('Updated Departments Color', $department->color);
    }

    /**
     * Roles: a new role gets read-only access to every module and the admin panel.
     *
     * @return void
     */
    public function test_roles_form()
    {
        $role = $this->checkModuleForm('Roles', 'roles', 'role', true);

        $this->assertSame('UPDATED_ROLES_NAME', $role->name);
        $this->assertTrue($role->hasPermission('ADMIN_PANEL'));
    }

    /**
     * Permissions.
     *
     * @return void
     */
    public function test_permissions_form()
    {
        $this->checkModuleForm('Permissions', 'permissions', 'permission', true);
    }

    /**
     * Blog categories (regular form posts).
     *
     * @return void
     */
    public function test_blog_categories_form()
    {
        $this->checkModuleForm('Blog_categories', 'blog_categories', 'blog_category', false);
    }

    /**
     * Blog posts.
     *
     * @return void
     */
    public function test_blog_posts_form()
    {
        $post = $this->checkModuleForm('Blog_posts', 'blog_posts', 'blog_post', true);

        $this->assertSame('2026-10-24', substr($post->post_date, 0, 10));
        $this->assertSame(['alpha', 'beta'], json_decode($post->tags));
    }

    /**
     * Add a record as Super Admin, edit and delete it as the manager role; every other role is refused at each step.
     * Returns the record as it was before deletion.
     *
     * @param  string  $moduleName
     * @param  string  $resource  URL segment
     * @param  string  $singular  form id prefix
     * @param  bool  $ajax  whether the forms submit JSON over Ajax (otherwise regular posts)
     * @param  array  $addOverrides  values for controls that are not module fields
     * @param  array  $editOverrides
     * @param  callable|null  $beforeDelete  receives the edited record
     * @return object
     */
    protected function checkModuleForm($moduleName, $resource, $singular, $ajax, $addOverrides = [], $editOverrides = [], $beforeDelete = null)
    {
        $module = LAModule::get($moduleName);
        $table = $module->name_db;
        $viewCol = $module->view_col;
        $model = 'App\\Models\\'.$module->model;
        $submit = fn ($method, $url, $pairs) => $ajax ? $this->submitAjax($method, $url, $pairs) : $this->submitForm($method, $url, $pairs);

        // Add
        $html = $this->asRole('super_admin')->get("/admin/$resource")->assertStatus(200)->getContent();
        $pairs = $this->fillControls($module, $this->formControls($html, "$singular-add-form", $choices), $choices, "Created $moduleName", $addOverrides);
        $count = DB::table($table)->count();
        foreach (self::REFUSED as $role) {
            $this->assertRefused($this->asRole($role)->callAs($submit, 'POST', "/admin/$resource", $pairs), $role, $ajax, "add $moduleName");
        }
        $this->assertSame($count, DB::table($table)->count(), "A refused role added a $moduleName record");

        $response = $this->asRole('super_admin')->callAs($submit, 'POST', "/admin/$resource", $pairs);
        $ajax ? $response->assertStatus(201)->assertJsonPath('status', 'success') : $response->assertRedirect()->assertSessionHasNoErrors();
        $id = DB::table($table)->max('id');
        $record = $model::find($id);
        $this->assertSame($count + 1, DB::table($table)->count());
        $this->assertStringStartsWith('CREATED', strtoupper((string) $record->$viewCol), "$moduleName view column not saved");
        $this->assertLogged($moduleName, 'CREATED', $id, 'super_admin');

        // The record pages show the saved values
        $this->asRole('viewer')->get("/admin/$resource/$id")->assertStatus(200)->assertSee($record->$viewCol);

        // Edit
        $html = $this->asRole('manager')->get("/admin/$resource/$id/edit")->assertStatus(200)->assertSee($record->$viewCol)->getContent();
        $pairs = $this->fillControls($module, $this->formControls($html, "$singular-edit-form", $choices), $choices, "Updated $moduleName", $editOverrides);
        foreach (self::REFUSED as $role) {
            $this->assertRefused($this->asRole($role)->callAs($submit, 'PUT', "/admin/$resource/$id", $pairs), $role, $ajax, "edit $moduleName");
        }
        $this->assertSame($record->$viewCol, $model::find($id)->$viewCol, "A refused role edited a $moduleName record");

        $response = $this->asRole('manager')->callAs($submit, 'PUT', "/admin/$resource/$id", $pairs);
        $ajax ? $response->assertStatus(200)->assertJsonPath('status', 'success') : $response->assertRedirect()->assertSessionHasNoErrors();
        $record = $model::find($id);
        $this->assertStringStartsWith('UPDATED', strtoupper((string) $record->$viewCol), "$moduleName edit not saved");
        $this->assertLogged($moduleName, 'UPDATED', $id, 'manager');

        if ($beforeDelete) {
            $beforeDelete($record);
        }

        // Delete (the listing's delete button is a regular form)
        foreach (self::REFUSED as $role) {
            $this->assertRefused($this->asRole($role)->delete("/admin/$resource/$id"), $role, false, "delete $moduleName");
        }
        $this->assertNotNull($model::find($id), "A refused role deleted a $moduleName record");

        $this->asRole('manager')->delete("/admin/$resource/$id")->assertRedirect("/admin/$resource");
        $this->assertNull($model::find($id));
        $this->assertLogged($moduleName, 'DELETED', $id, 'manager');

        return $record;
    }

    /**
     * Call a submit closure (keeps asRole() chainable).
     *
     * @return \Illuminate\Testing\TestResponse
     */
    protected function callAs($submit, $method, $url, $pairs)
    {
        return $submit($method, $url, $pairs);
    }

    /**
     * Assert a refused request: guests go to login, customers get 403, users without module access are refused.
     *
     * @return void
     */
    protected function assertRefused($response, $role, $ajax, $action)
    {
        $status = $response->getStatusCode();
        $message = "$role should not be able to $action (got $status)";
        if ($role === 'guest') {
            $ajax ? $this->assertSame(401, $status, $message) : $this->assertTrue($response->isRedirect(url('/login')), $message);
        } elseif ($role === 'customer') {
            $this->assertSame(403, $status, $message);
        } elseif ($ajax) {
            $this->assertSame(403, $status, $message);
            $this->assertSame('error', $response->json('status'), $message);
        } else {
            $this->assertTrue($response->isRedirect(url('/admin')) || $response->isRedirect(url('/admin/')), $message);
        }
    }

    /**
     * Assert the module's LA Log entry for an action.
     *
     * @return void
     */
    protected function assertLogged($moduleName, $action, $id, $role)
    {
        $type = strtoupper(LAModule::get($moduleName)->model).'_'.$action;
        $type = str_replace('BLOGCATEGORY', 'BLOG_CATEGORY', str_replace('BLOGPOST', 'BLOG_POST', $type));
        $this->assertSame(1, LALog::where('type', $type)->where('context_id', $id)->where('user_id', $this->users[$role]->id)->count(), "$type not logged");
    }
}
