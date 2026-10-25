<?php

namespace Tests\Feature\Access;

use App\Models\LAModule;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\ActsAsRoles;
use Tests\Concerns\FillsForms;
use Tests\TestCase;

/**
 * Employee passwords, role access, permissions and the role hierarchy.
 */
class TeamActionsTest extends TestCase
{
    use ActsAsRoles;
    use FillsForms;

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
     * Employees change their own password; only a Super Admin changes someone else's.
     *
     * @return void
     */
    public function test_change_password()
    {
        $change = fn ($role, $target, $password = 'changed-1234') => $this->asRole($role)
            ->post('/admin/change_password/'.$this->users[$target]->context_id, ['password' => $password, 'password_confirmation' => $password]);
        $passwordIs = fn ($target, $password) => Hash::check($password, $this->users[$target]->fresh()->password);

        // Own password, through the form on the employee page
        $html = $this->asRole('viewer')->get('/admin/employees/'.$this->users['viewer']->context_id)->assertStatus(200)->getContent();
        $this->assertStringContainsString('id="password-reset-form"', $html);
        $change('viewer', 'viewer', 'viewer-new-1234')->assertRedirect('/admin/employees/'.$this->users['viewer']->context_id.'#tab-account-settings');
        $this->assertTrue($passwordIs('viewer', 'viewer-new-1234'));

        // Someone else's password: refused, even with full access to Employees
        $change('guest', 'super_admin')->assertRedirect('/login');
        $change('customer', 'super_admin')->assertStatus(403);
        foreach (['admin', 'viewer', 'manager'] as $role) {
            $change($role, 'super_admin')->assertRedirect('/admin');
        }
        $change('manager', 'viewer')->assertRedirect('/admin');
        $this->assertTrue($passwordIs('super_admin', 'secret1234'));
        $this->assertTrue($passwordIs('viewer', 'viewer-new-1234'));

        // Super Admin changes anyone's
        $change('super_admin', 'manager', 'manager-new-1234')->assertRedirect();
        $this->assertTrue($passwordIs('manager', 'manager-new-1234'));

        // Confirmation must match
        $this->asRole('viewer')->post('/admin/change_password/'.$this->users['viewer']->context_id, [
            'password' => 'one-password', 'password_confirmation' => 'another-password',
        ])->assertSessionHasErrors('password_confirmation');
        $this->assertTrue($passwordIs('viewer', 'viewer-new-1234'));
    }

    /**
     * Only the Super Admin changes a role's module and field access.
     *
     * @return void
     */
    public function test_save_module_role_permissions()
    {
        $viewer = Role::where('name', 'VIEWER')->first();
        $departments = LAModule::get('Departments');
        $html = $this->asRole('super_admin')->get('/admin/roles/'.$viewer->id)->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, '//form[contains(@action, "save_module_role_permissions")]', $choices);

        // Give the viewer role create access to Departments, and write access to its fields
        $pairs[] = ['module_create_'.$departments->id, 'on'];
        foreach ($departments->fields as $field) {
            $pairs = array_values(array_filter($pairs, fn ($pair) => $pair[0] !== $field['colname'].'_'.$departments->id.'_'.$viewer->id));
            $pairs[] = [$field['colname'].'_'.$departments->id.'_'.$viewer->id, '2'];
        }

        $url = '/admin/save_module_role_permissions/'.$viewer->id;
        $this->asRole('guest')->submitForm('POST', $url, $pairs)->assertRedirect('/login');
        $this->asRole('customer')->submitForm('POST', $url, $pairs)->assertStatus(403);
        foreach (['admin', 'viewer', 'manager'] as $role) {
            $this->asRole($role)->submitForm('POST', $url, $pairs)->assertRedirect('/admin');
        }
        $this->assertFalse(LAModule::hasAccess($departments->id, 'create', $this->users['viewer']->id));

        $this->asRole('super_admin')->submitForm('POST', $url, $pairs)->assertRedirect('/admin/roles/'.$viewer->id.'#tab-access');
        $this->assertTrue(LAModule::hasAccess($departments->id, 'create', $this->users['viewer']->id));
        $this->assertFalse(LAModule::hasAccess($departments->id, 'edit', $this->users['viewer']->id));
        $this->assertTrue(LAModule::hasAccess('Employees', 'view', $this->users['viewer']->id), 'Other modules keep their access');
        $this->assertSame('write', DB::table('role_la_module_fields')->where('role_id', $viewer->id)
            ->where('field_id', $departments->fields['name']['id'])->value('access'));
    }

    /**
     * Only the Super Admin changes which roles have a permission.
     *
     * @return void
     */
    public function test_save_permissions()
    {
        $permission = new Permission;
        $permission->forceFill(['name' => 'REPORTS', 'display_name' => 'Reports', 'description' => ''])->save();
        $viewer = Role::where('name', 'VIEWER')->first();
        $customer = Role::where('name', 'CUSTOMER')->first();

        $html = $this->asRole('super_admin')->get('/admin/permissions/'.$permission->id)->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, '//form[contains(@action, "save_permissions")]', $choices);
        $pairs[] = ['permi_role_'.$viewer->id, '1'];

        $url = '/admin/save_permissions/'.$permission->id;
        $this->asRole('guest')->submitForm('POST', $url, $pairs)->assertRedirect('/login');
        $this->asRole('customer')->submitForm('POST', $url, $pairs)->assertStatus(403);
        foreach (['admin', 'viewer', 'manager'] as $role) {
            $this->asRole($role)->submitForm('POST', $url, $pairs)->assertRedirect('/admin');
        }
        // A crafted request giving customers the admin panel is refused too
        $this->asRole('manager')->post('/admin/save_permissions/1', ['permi_role_'.$customer->id => '1'])->assertRedirect('/admin');
        $this->assertFalse($customer->fresh()->hasPermission('ADMIN_PANEL'));
        $this->assertFalse($viewer->fresh()->hasPermission('REPORTS'));

        $this->asRole('super_admin')->submitForm('POST', $url, $pairs)->assertRedirect('/admin/permissions/'.$permission->id.'#tab-access');
        $this->assertTrue($viewer->fresh()->hasPermission('REPORTS'));
        $this->assertTrue(Role::where('name', 'SUPER_ADMIN')->first()->hasPermission('ADMIN_PANEL'), 'Unchecked roles keep other permissions');
    }

    /**
     * The role hierarchy is saved by users who may edit roles.
     *
     * @return void
     */
    public function test_update_role_hierarchy()
    {
        $roles = Role::pluck('id', 'name');
        $tree = ['jsonData' => [
            ['id' => $roles['SUPER_ADMIN'], 'children' => [
                ['id' => $roles['ADMIN'], 'children' => [['id' => $roles['CUSTOMER']]]],
                ['id' => $roles['MANAGER'], 'children' => [['id' => $roles['VIEWER']]]],
            ]],
        ]];

        $this->asRole('guest')->post('/admin/update_role_hierarchy', $tree)->assertRedirect('/login');
        $this->asRole('customer')->post('/admin/update_role_hierarchy', $tree)->assertStatus(403);
        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->post('/admin/update_role_hierarchy', $tree)->assertStatus(403);
        }
        $this->assertSame(1, (int) Role::find($roles['VIEWER'])->parent);

        $this->asRole('manager')->post('/admin/update_role_hierarchy', $tree)->assertStatus(200);
        $this->assertSame($roles['MANAGER'], (int) Role::find($roles['VIEWER'])->parent);
        $this->assertNull(Role::find($roles['SUPER_ADMIN'])->parent);
    }

    /**
     * A user's page opens the record of the employee or customer behind it.
     *
     * @return void
     */
    public function test_user_pages_open_their_record()
    {
        $this->asRole('super_admin')->get('/admin/users/'.$this->users['viewer']->id)
            ->assertRedirect('/admin/employees/'.$this->users['viewer']->context_id.'?_pjax=%23content-wrapper');
        $this->asRole('super_admin')->get('/admin/users/'.$this->users['customer']->id)
            ->assertRedirect('/admin/customers/'.$this->users['customer']->context_id.'?_pjax=%23content-wrapper');
        $this->asRole('super_admin')->get('/admin/users/9999')->assertStatus(404);
    }
}
