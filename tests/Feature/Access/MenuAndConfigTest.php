<?php

namespace Tests\Feature\Access;

use App\Models\LAConfig;
use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ActsAsRoles;
use Tests\Concerns\FillsForms;
use Tests\TestCase;

/**
 * Menu editor and settings (Super Admin only).
 */
class MenuAndConfigTest extends TestCase
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
     * Add custom and module menus, rename, reorder, set role access, delete.
     *
     * @return void
     */
    public function test_menus()
    {
        // Custom menu, from the menu editor form
        $html = $this->asRole('super_admin')->get('/admin/la_menus')->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, 'menu-custom-form');
        $data = array_merge($this->formJson($pairs), ['name' => 'Reports', 'url' => 'reports', 'icon' => 'fa-bar-chart', 'type' => 'custom']);

        $this->assertSuperAdminOnly('POST', '/admin/la_menus', $data);
        $this->assertSame(0, LAMenu::where('name', 'Reports')->count());
        $this->asRole('super_admin')->post('/admin/la_menus', $data)->assertRedirect('/admin/la_menus');
        $menu = LAMenu::where('name', 'Reports')->first();
        $this->assertSame(Role::count(), $menu->roles()->count(), 'New menus are visible to every role');
        $this->asRole('super_admin')->get('/admin/la_menus/'.$menu->id)->assertStatus(200)->assertSee('Reports');

        // Module menu
        $this->asRole('super_admin')->post('/admin/la_menus', ['type' => 'module', 'module_id' => LAModule::get('Departments')->id])
            ->assertJsonPath('status', 'success');
        $this->asRole('super_admin')->post('/admin/la_menus', ['type' => 'module', 'module_id' => 9999])
            ->assertJsonPath('status', 'failure');

        // Rename
        $update = ['name' => 'Statistics', 'url' => 'statistics', 'icon' => 'fa-line-chart', 'type' => 'custom'];
        $this->assertSuperAdminOnly('PUT', '/admin/la_menus/'.$menu->id, $update);
        $this->asRole('super_admin')->put('/admin/la_menus/'.$menu->id, $update)->assertRedirect('/admin/la_menus');
        $this->assertSame(['Statistics', 'statistics'], [$menu->fresh()->name, $menu->fresh()->url]);
        $this->asRole('super_admin')->put('/admin/la_menus/9999', $update)->assertStatus(404);

        // Role access
        $viewer = Role::where('name', 'VIEWER')->first();
        $access = ['roles' => [$viewer->id]];
        $this->assertSuperAdminOnly('POST', '/admin/la_menus_save_role_permissions/'.$menu->id, $access);
        $this->asRole('super_admin')->post('/admin/la_menus_save_role_permissions/'.$menu->id, $access)->assertJsonPath('status', 'success');
        $this->assertSame([$viewer->id], $menu->roles()->pluck('roles.id')->all());
        // The sidebar shows the menu to the viewer role only (and to the Super Admin)
        $this->asRole('viewer')->get('/admin')->assertStatus(200)->assertSee('Statistics');
        $this->asRole('manager')->get('/admin')->assertStatus(200)->assertDontSee('Statistics');
        $this->asRole('super_admin')->get('/admin')->assertStatus(200)->assertSee('Statistics');
        $this->asRole('super_admin')->post('/admin/la_menus_save_role_permissions/'.$menu->id)->assertJsonPath('status', 'success');
        $this->assertSame(0, $menu->roles()->count());

        // Reorder: put the menu under Dashboard
        $dashboard = LAMenu::where('name', 'Dashboard')->first();
        $tree = ['jsonData' => [['id' => $dashboard->id, 'children' => [['id' => $menu->id]]]]];
        $this->assertSuperAdminOnly('POST', '/admin/la_menus/update_hierarchy', $tree);
        $this->asRole('super_admin')->post('/admin/la_menus/update_hierarchy', $tree)->assertStatus(200);
        $this->assertSame([$dashboard->id, 1], [(int) $menu->fresh()->parent, (int) $menu->fresh()->hierarchy]);

        // Delete
        $this->assertSuperAdminOnly('DELETE', '/admin/la_menus/'.$menu->id);
        $this->asRole('super_admin')->delete('/admin/la_menus/'.$menu->id)->assertRedirect('/admin/la_menus');
        $this->assertNull(LAMenu::find($menu->id));
        $this->asRole('super_admin')->delete('/admin/la_menus/'.$menu->id)->assertStatus(404);
    }

    /**
     * Add a setting, edit its definition, save a section's values, delete settings.
     *
     * @return void
     */
    public function test_configs()
    {
        // Add
        $html = $this->asRole('super_admin')->get('/admin/la_configs')->assertStatus(200)->getContent();
        $add = array_merge($this->formJson($this->formControls($html, 'config-field-form')), [
            'label' => 'Support phone', 'key' => 'support_phone', 'section' => 'general',
            'field_type' => LAModuleFieldType::where('name', 'Mobile')->first()->id, 'minlength' => '', 'maxlength' => '15',
        ]);
        $this->assertSuperAdminOnly('POST', '/admin/la_configs', $add);
        $this->asRole('super_admin')->post('/admin/la_configs', $add)->assertRedirect('/admin/la_configs');
        $config = LAConfig::where('key', 'support_phone')->first();
        $this->assertSame(['General', 15], [$config->section, (int) $config->maxlength]);
        $this->asRole('super_admin')->from('/admin/la_configs')->post('/admin/la_configs', $add)->assertSessionHasErrors('key');

        // Edit the definition
        $this->asRole('super_admin')->get('/admin/la_configs/'.$config->id.'/edit')->assertStatus(200)->assertSee('support_phone');
        $edit = array_merge($add, ['label' => 'Helpline', 'section' => 'General', 'required' => 'required']);
        $this->assertSuperAdminOnly('POST', '/admin/la_configs/edit_save/'.$config->id, $edit);
        $this->asRole('super_admin')->post('/admin/la_configs/edit_save/'.$config->id, $edit)->assertRedirect('/admin/la_configs#tab-General');
        $this->assertSame(['Helpline', 1], [$config->fresh()->label, (int) $config->fresh()->required]);
        $this->asRole('super_admin')->post('/admin/la_configs/edit_save/9999', $edit)->assertStatus(404);

        // Save the values of the General section, from its form
        $html = $this->asRole('super_admin')->get('/admin/la_configs')->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, '//form[contains(@action, "/la_configs/General")]');
        $values = array_merge($this->formJson($pairs), ['sitename' => 'Acme Admin', 'support_phone' => '9876543210']);
        $this->assertSuperAdminOnly('PUT', '/admin/la_configs/General', $values);
        $this->asRole('super_admin')->put('/admin/la_configs/General', $values)->assertRedirect('/admin/la_configs');
        $this->assertSame('Acme Admin', LAConfig::getByKey('sitename'));
        $this->assertSame('9876543210', LAConfig::getByKey('support_phone'));
        $this->assertSame('LaraAdmin', LAConfig::getByKey('sitename_part1'), 'Other values of the section are kept');

        // Delete, both ways
        $this->assertSuperAdminOnly('GET', '/admin/la_configs/ajax_destroy/support_phone');
        $this->asRole('super_admin')->get('/admin/la_configs/ajax_destroy/support_phone')->assertRedirect('/admin/la_configs');
        $this->assertNull(LAConfig::where('key', 'support_phone')->first());

        $this->assertSuperAdminOnly('DELETE', '/admin/la_configs/default_email');
        $this->asRole('super_admin')->delete('/admin/la_configs/default_email')->assertRedirect('/admin/la_configs');
        $this->assertSame(0, DB::table('la_configs')->where('key', 'default_email')->count());
    }

    /**
     * Assert that guests are sent to login and every role but Super Admin is refused.
     *
     * @return void
     */
    protected function assertSuperAdminOnly($method, $url, $data = [])
    {
        $this->asRole('guest')->call($method, $url, $data)->assertRedirect('/login');
        foreach (['customer', 'admin', 'viewer', 'manager'] as $role) {
            $this->asRole($role)->call($method, $url, $data)->assertStatus(403);
        }
    }
}
