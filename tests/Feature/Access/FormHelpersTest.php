<?php

namespace Tests\Feature\Access;

use App\Models\BlogCategory;
use App\Models\Department;
use App\Models\LAModule;
use App\Models\LAModuleFieldType;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\ActsAsRoles;
use Tests\Concerns\FillsForms;
use Tests\TestCase;

/**
 * Helpers used inside module forms: quick add, unique value check, checklists and module search.
 */
class FormHelpersTest extends TestCase
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
     * Quick add (the "+" next to a dropdown) adds a record of the dropdown's module.
     *
     * @return void
     */
    public function test_quick_add()
    {
        $module = LAModule::get('Departments');
        $ajax = ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'];

        $html = $this->asRole('manager')->get('/admin/quick_add_form/'.$module->id.'?field_name=dept&popup_vals=@departments')
            ->assertStatus(200)->getContent();
        $pairs = $this->fillControls($module, $this->formControls($html, 'quick-add-form', $choices), $choices, 'Quick');
        $data = $this->formJson($pairs) + ['popup_vals' => '@departments'];

        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->post('/admin/quick_add_form_save/'.$module->id, $data, $ajax)->assertJsonPath('status', 'error');
        }
        $this->assertSame(0, Department::where('name', 'Quick Name')->count());

        $response = $this->asRole('manager')->post('/admin/quick_add_form_save/'.$module->id, $data, $ajax)
            ->assertStatus(200)->assertJsonPath('status', 'success');
        $department = Department::where('name', 'Quick Name')->first();
        $this->assertNotNull($department);
        $this->assertSame($department->id, $response->json('insert_id'));

        // Validation errors are reported, not thrown
        $this->asRole('manager')->post('/admin/quick_add_form_save/'.$module->id, ['name' => str_repeat('x', 300), 'popup_vals' => '@departments'], $ajax)
            ->assertStatus(200)->assertJsonPath('status', 'error');

        // Blog categories (regular form controller) support quick add too
        $categories = LAModule::get('Blog_categories');
        $this->asRole('manager')->post('/admin/quick_add_form_save/'.$categories->id, ['name' => 'Quick category', 'url' => 'quick', 'popup_vals' => '@blog_categories'], $ajax)
            ->assertStatus(200)->assertJsonPath('status', 'success');
        $this->assertSame(1, BlogCategory::where('url', 'quick')->count());
    }

    /**
     * The unique check answers whether a value is taken, for users who may fill the module's forms.
     *
     * @return void
     */
    public function test_check_unique_val()
    {
        $module = LAModule::get('Employees');
        $email = $module->fields['email_primary']['id'];
        $url = '/admin/check_unique_val/'.$email;

        $this->asRole('guest')->post($url, ['field_value' => 'admin@example.com'])->assertRedirect('/login');
        $this->asRole('customer')->post($url, ['field_value' => 'admin@example.com'])->assertStatus(403);
        foreach (['admin', 'viewer'] as $role) {
            $this->asRole($role)->post($url, ['field_value' => 'admin@example.com'])->assertStatus(403);
        }

        $this->asRole('manager')->post($url, ['field_value' => 'admin@example.com', 'row_id' => 0])->assertJsonPath('exists', true);
        $this->asRole('manager')->post($url, ['field_value' => 'free@example.com', 'row_id' => 0])->assertJsonPath('exists', false);
        // Editing a record: its own value is not a duplicate
        $this->asRole('manager')->post($url, ['field_value' => 'admin@example.com', 'row_id' => $this->users['super_admin']->context_id])
            ->assertJsonPath('exists', false);

        // Only unique fields can be checked
        $this->asRole('manager')->post('/admin/check_unique_val/'.$module->fields['name']['id'], ['field_value' => 'x'])->assertStatus(404);
    }

    /**
     * Ticking a checklist item saves it, for users who may edit the record and write the field.
     *
     * @return void
     */
    public function test_checklist_update()
    {
        $module = LAModule::get('Departments');
        $this->asRole('super_admin')->post('/admin/la_module_fields', [
            'module_id' => $module->id, 'colname' => 'todo', 'label' => 'To do',
            'field_type' => LAModuleFieldType::where('name', 'Checklist')->first()->id,
            'defaultvalue' => '', 'minlength' => 0, 'maxlength' => 0, 'listing_col' => 'false',
        ])->assertRedirect();
        $field = LAModule::get('Departments')->fields['todo'];
        DB::table('departments')->where('id', 1)->update(['todo' => '[{"title":"Hire","checked":false},{"title":"Train","checked":false}]']);

        $data = ['module_id' => $module->id, 'module_field_id' => $field['id'], 'row_id' => 1, 'list_title' => 'Train', 'list_vals' => 'true'];

        $this->asRole('guest')->post('/admin/checklist_update', $data)->assertRedirect('/login');
        $this->asRole('customer')->post('/admin/checklist_update', $data)->assertStatus(403);
        // New fields get each role's module access; make the field read-only for the manager, who may edit departments
        LAModule::setDefaultFieldRoleAccess($field['id'], Role::where('name', 'MANAGER')->first()->id, 'readonly');
        foreach (['admin', 'viewer', 'manager'] as $role) {
            $this->asRole($role)->post('/admin/checklist_update', $data)->assertStatus(403);
        }
        $this->assertStringNotContainsString('"checked":"true"', DB::table('departments')->where('id', 1)->value('todo'));

        $this->asRole('super_admin')->post('/admin/checklist_update', $data)->assertStatus(200)->assertJsonPath('status', 'success');
        $todo = json_decode(DB::table('departments')->where('id', 1)->value('todo'), true);
        // Checked states are the strings "true" / "false" sent by the page
        $this->assertSame([['checked' => false, 'title' => 'Hire'], ['checked' => 'true', 'title' => 'Train']], $todo);

        LAModule::setDefaultFieldRoleAccess($field['id'], Role::where('name', 'MANAGER')->first()->id, 'full');
        $this->asRole('manager')->post('/admin/checklist_update', ['list_title' => 'Hire'] + $data)->assertStatus(200);
        $this->assertSame('true', json_decode(DB::table('departments')->where('id', 1)->value('todo'), true)[0]['checked']);

        // A field of another module
        $this->asRole('super_admin')->post('/admin/checklist_update', ['module_id' => LAModule::get('Roles')->id] + $data)->assertStatus(404);
    }

    /**
     * Module search needs Laravel Scout on the module's model; it is refused or not found otherwise.
     *
     * @return void
     */
    public function test_search()
    {
        $this->asRole('guest')->get('/admin/find/Departments?q=adm')->assertRedirect('/login');
        $this->asRole('customer')->get('/admin/find/Departments?q=adm')->assertStatus(403);
        $this->asRole('admin')->get('/admin/find/Departments?q=adm')->assertStatus(403);
        $this->asRole('super_admin')->get('/admin/find/Departments?q=adm')->assertStatus(404);
        $this->asRole('super_admin')->get('/admin/find/NoSuchModule?q=adm')->assertStatus(404);
    }
}
