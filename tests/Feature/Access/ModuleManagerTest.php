<?php

namespace Tests\Feature\Access;

use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\ActsAsRoles;
use Tests\Concerns\FillsForms;
use Tests\TestCase;

/**
 * Module Manager (Super Admin only): modules, fields, access and code generation.
 *
 * The code generators write source files; every file is restored and every new file removed after each test.
 */
class ModuleManagerTest extends TestCase
{
    use ActsAsRoles;
    use FillsForms;

    /**
     * Roles refused by the Super Admin routes, with the response they get.
     *
     * @var array<string, int>
     */
    protected const REFUSED = ['customer' => 403, 'admin' => 403, 'viewer' => 403, 'manager' => 403];

    /**
     * Source files the generators edit in place.
     *
     * @var array<int, string>
     */
    protected $editedFiles = ['routes/admin.php', 'app/Providers/LAProvider.php', 'config/laraadmin.php'];

    /**
     * Folders the generators add files to.
     *
     * @var array<int, string>
     */
    protected $generatedDirs = ['app/Http/Controllers/LA', 'app/Models', 'app/Observers', 'resources/views/la', 'resources/lang/en', 'database/migrations'];

    /**
     * Contents and permissions of $editedFiles, and the files in $generatedDirs, before the test.
     *
     * @var array
     */
    protected $snapshot = [];

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

        foreach ($this->editedFiles as $file) {
            $this->snapshot['edited'][$file] = [file_get_contents(base_path($file)), fileperms(base_path($file)) & 0777];
        }
        foreach ($this->generatedDirs as $dir) {
            $this->snapshot['files'][$dir] = array_map(fn ($f) => $f->getPathname(), File::allFiles(base_path($dir)));
            $this->snapshot['dirs'][$dir] = File::directories(base_path($dir));
        }
    }

    /**
     * Restore edited files and remove generated ones.
     *
     * @return void
     */
    public function tearDown(): void
    {
        foreach ($this->snapshot['edited'] as $file => [$contents, $perms]) {
            file_put_contents(base_path($file), $contents);
            chmod(base_path($file), $perms);
        }
        foreach ($this->generatedDirs as $dir) {
            foreach (File::allFiles(base_path($dir)) as $file) {
                if (! in_array($file->getPathname(), $this->snapshot['files'][$dir], true)) {
                    File::delete($file->getPathname());
                }
            }
            foreach (File::directories(base_path($dir)) as $sub) {
                if (! in_array($sub, $this->snapshot['dirs'][$dir], true)) {
                    File::deleteDirectory($sub);
                }
            }
        }
        Schema::dropIfExists('widgets');
        Schema::dropIfExists('gizmos');

        parent::tearDown();
    }

    /**
     * Create a module from the Modules page, rename it, pick its view column, list its files, delete it.
     *
     * @return void
     */
    public function test_create_update_and_delete_module()
    {
        $html = $this->asRole('super_admin')->get('/admin/la_modules')->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, 'module-add-form');
        $pairs = array_merge(array_filter($pairs, fn ($p) => ! in_array($p[0], ['name', 'icon'], true)), [['name', 'Widgets'], ['icon', 'fa-cube']]);

        $this->assertSuperAdminOnly('POST', '/admin/la_modules', $this->formJson($pairs));
        $this->assertNull(LAModule::where('name', 'Widgets')->first());

        $response = $this->asRole('super_admin')->submitForm('POST', '/admin/la_modules', $pairs);
        $module = LAModule::where('name', 'Widgets')->first();
        $this->assertNotNull($module);
        $response->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertCount(1, glob(database_path('migrations/*_create_widgets_table.php')));
        $this->asRole('super_admin')->get('/admin/la_modules/'.$module->id)->assertStatus(200)->assertSee('Widgets');
        $this->asRole('super_admin')->get('/admin/la_modules/9999')->assertStatus(404);

        // Label and icon, through both update routes
        $update = ['id' => $module->id, 'label' => 'Doohickeys', 'icon' => 'fa-gear'];
        $this->assertSuperAdminOnly('POST', '/admin/la_module_update', $update);
        $this->asRole('super_admin')->post('/admin/la_module_update', $update)->assertStatus(200);
        $this->asRole('super_admin')->put('/admin/la_modules/'.$module->id, ['id' => $module->id, 'label' => 'Widgets', 'icon' => 'fa-cube'])->assertStatus(200);
        $this->assertSame(['Widgets', 'fa-cube'], [$module->fresh()->label, $module->fresh()->fa_icon]);

        // View column
        $this->addField($module->id, 'title', 'String');
        $this->assertSuperAdminOnly('GET', '/admin/la_modules/'.$module->id.'/set_view_col/title');
        $this->asRole('super_admin')->get('/admin/la_modules/'.$module->id.'/set_view_col/title')->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertSame('title', $module->fresh()->view_col);
        $this->asRole('super_admin')->get('/admin/la_modules/'.$module->id.'/set_view_col/no_such_column')->assertStatus(404);

        // Files of the module
        $this->assertSuperAdminOnly('POST', '/admin/la_modules/get_module_files/'.$module->id);
        $this->asRole('super_admin')->post('/admin/la_modules/get_module_files/'.$module->id)->assertStatus(200)
            ->assertJsonPath('files.0.name', base_path('routes/admin.php'))->assertJsonPath('files.0.perms', '0644');

        // Delete
        $this->assertSuperAdminOnly('DELETE', '/admin/la_modules/'.$module->id);
        $this->assertNotNull(LAModule::find($module->id));
        $this->asRole('super_admin')->delete('/admin/la_modules/'.$module->id)->assertRedirect();
        $this->assertNull(LAModule::find($module->id));
        $this->assertFalse(Schema::hasTable('widgets'));
    }

    /**
     * Add, edit, sort, show in listing and delete fields of a module.
     *
     * @return void
     */
    public function test_module_fields()
    {
        $module = LAModule::get('Departments');
        $html = $this->asRole('super_admin')->get('/admin/la_modules/'.$module->id)->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, 'field-form');
        $form = array_merge($this->formJson($pairs), [
            'colname' => 'logo', 'label' => 'Logo', 'field_type' => LAModuleFieldType::where('name', 'Image')->first()->id,
            'defaultvalue' => '', 'minlength' => 0, 'maxlength' => 0,
        ]);

        // Add
        $this->assertSuperAdminOnly('POST', '/admin/la_module_fields', $form);
        $this->assertFalse(Schema::hasColumn('departments', 'logo'));
        $this->asRole('super_admin')->post('/admin/la_module_fields', $form)->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertTrue(Schema::hasColumn('departments', 'logo'));
        $field = LAModuleField::where('module', $module->id)->where('colname', 'logo')->first();

        // Edit
        $this->asRole('super_admin')->get('/admin/la_module_fields/'.$field->id.'/edit')->assertStatus(200)->assertSee('Logo');
        $edit = array_merge($form, ['module_id' => $module->id, 'label' => 'Department Logo']);
        $this->assertSuperAdminOnly('PUT', '/admin/la_module_fields/'.$field->id, $edit);
        $this->asRole('super_admin')->put('/admin/la_module_fields/'.$field->id, $edit)->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertSame('Department Logo', $field->fresh()->label);

        // Listing column
        $listing = ['listid' => $field->id, 'state' => 'true'];
        $this->assertSuperAdminOnly('POST', '/admin/la_module_fields_listing_show', $listing);
        $this->asRole('super_admin')->post('/admin/la_module_fields_listing_show', $listing)->assertJsonPath('status', 'success');
        $this->assertSame(1, (int) $field->fresh()->listing_col);

        // Sort
        $ids = LAModuleField::where('module', $module->id)->orderBy('sort')->pluck('id')->reverse()->values()->all();
        $sort = '/admin/la_modules/save_module_field_sort/'.$module->id.'?'.http_build_query(['sort_array' => $ids]);
        $this->assertSuperAdminOnly('GET', $sort);
        $this->asRole('super_admin')->get($sort)->assertJsonPath('status', 'success');
        $this->assertSame($ids, LAModuleField::where('module', $module->id)->orderBy('sort')->pluck('id')->all());

        // Delete: an Image field has a foreign key to uploads
        $this->assertSuperAdminOnly('GET', '/admin/la_module_fields/'.$field->id.'/delete');
        $this->asRole('super_admin')->get('/admin/la_module_fields/'.$field->id.'/delete')->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertFalse(Schema::hasColumn('departments', 'logo'));
        $this->assertNull(LAModuleField::find($field->id));

        // ... and through the resource route
        $this->addField($module->id, 'motto', 'String');
        $motto = LAModuleField::where('module', $module->id)->where('colname', 'motto')->first();
        $this->assertSuperAdminOnly('DELETE', '/admin/la_module_fields/'.$motto->id);
        $this->asRole('super_admin')->delete('/admin/la_module_fields/'.$motto->id)->assertRedirect('/admin/la_modules/'.$module->id);
        $this->assertFalse(Schema::hasColumn('departments', 'motto'));
    }

    /**
     * Access of every role to one module, from the module's Access tab.
     *
     * @return void
     */
    public function test_save_role_module_permissions()
    {
        $module = LAModule::get('Departments');
        $viewer = Role::where('name', 'VIEWER')->first();
        $html = $this->asRole('super_admin')->get('/admin/la_modules/'.$module->id)->assertStatus(200)->getContent();
        $pairs = $this->formControls($html, '//form[contains(@action, "save_role_module_permissions")]');
        $pairs[] = ['module_edit_'.$viewer->id, 'on'];
        $data = $this->formJson($pairs);

        $url = '/admin/la_modules/save_role_module_permissions/'.$module->id;
        $this->assertSuperAdminOnly('POST', $url, $data);
        $this->assertFalse(LAModule::hasAccess($module->id, 'edit', $this->users['viewer']->id));

        $this->asRole('super_admin')->post($url, $data)->assertRedirect('/admin/la_modules/'.$module->id.'#access');
        $this->assertTrue(LAModule::hasAccess($module->id, 'edit', $this->users['viewer']->id));
        $this->assertTrue(LAModule::hasAccess($module->id, 'delete', $this->users['super_admin']->id), 'Other roles keep their access');
    }

    /**
     * Generate the CRUD, the migration, both, or an update of a module's code.
     *
     * @return void
     */
    public function test_generate_code()
    {
        LAModule::generate('Gizmos', 'gizmos', 'name', 'fa-cube', [[
            'colname' => 'name', 'label' => 'Name', 'field_type' => 'Name', 'unique' => false, 'defaultvalue' => '',
            'minlength' => 1, 'maxlength' => 250, 'required' => true, 'listing_col' => true,
        ]]);
        $module = LAModule::where('name', 'Gizmos')->first();

        foreach (['generate_crud', 'generate_migr', 'generate_update', 'generate_migr_crud'] as $action) {
            $this->assertSuperAdminOnly('GET', "/admin/la_modules/$action/".$module->id);
        }
        $this->assertFileDoesNotExist(app_path('Http/Controllers/LA/GizmosController.php'));

        $this->asRole('super_admin')->get('/admin/la_modules/generate_crud/'.$module->id)->assertJsonPath('status', 'success');
        $this->assertFileExists(app_path('Http/Controllers/LA/GizmosController.php'));
        $this->assertFileExists(resource_path('views/la/gizmos/index.blade.php'));
        $this->assertStringContainsString("'GizmosController'", file_get_contents(base_path('routes/admin.php')));
        $this->assertTrue(LAModule::hasAccess($module->id, 'delete', $this->users['super_admin']->id));
        $this->assertSame(1, (int) $module->fresh()->is_gen);

        $this->asRole('super_admin')->get('/admin/la_modules/generate_migr/'.$module->id)->assertJsonPath('status', 'success');
        $this->assertCount(1, glob(database_path('migrations/*_create_gizmos_table.php')));

        File::delete(app_path('Http/Controllers/LA/GizmosController.php'));
        $this->asRole('super_admin')->get('/admin/la_modules/generate_update/'.$module->id)->assertJsonPath('status', 'success');
        $this->assertFileExists(app_path('Http/Controllers/LA/GizmosController.php'));

        $this->asRole('super_admin')->get('/admin/la_modules/generate_migr_crud/'.$module->id)->assertJsonPath('status', 'success');
        $this->assertCount(1, glob(database_path('migrations/*_create_gizmos_table.php')));
        $this->assertSame(1, substr_count(file_get_contents(base_path('routes/admin.php')), "'GizmosController'"));
    }

    /**
     * The last commit line, for the Super Admin.
     *
     * @return void
     */
    public function test_git_status()
    {
        $this->assertSuperAdminOnly('GET', '/admin/git-status');
        $this->asRole('super_admin')->get('/admin/git-status')->assertStatus(200)->assertSee('Last Commit');
    }

    /**
     * Assert that guests are sent to login and every role but Super Admin is refused.
     *
     * @return void
     */
    protected function assertSuperAdminOnly($method, $url, $data = [])
    {
        $this->asRole('guest')->call($method, $url, $data)->assertRedirect('/login');
        foreach (self::REFUSED as $role => $status) {
            $this->asRole($role)->call($method, $url, $data)->assertStatus($status);
        }
    }

    /**
     * Add a field to a module, as Module Manager does.
     *
     * @return void
     */
    protected function addField($moduleId, $colname, $type)
    {
        $this->asRole('super_admin')->post('/admin/la_module_fields', [
            'module_id' => $moduleId, 'colname' => $colname, 'label' => ucfirst($colname),
            'field_type' => LAModuleFieldType::where('name', $type)->first()->id, 'defaultvalue' => '',
            'minlength' => 0, 'maxlength' => 100, 'listing_col' => 'true',
        ])->assertRedirect();
        $this->assertTrue(DB::table('la_module_fields')->where('module', $moduleId)->where('colname', $colname)->exists());
    }
}
