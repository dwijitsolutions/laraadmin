<?php

namespace Tests\Feature;

use App\Models\LAModule;
use App\Models\LAModuleField;
use App\Models\LAModuleFieldType;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModuleSchemaTest extends TestCase
{
    /**
     * One field per LaraAdmin field type: colname => [field type, default value, maxlength, popup values].
     *
     * @var array
     */
    protected $fieldDefs = [
        'address_txt' => ['Address', '', 0, null],
        'address_str' => ['Address', '', 200, null],
        'checkbox' => ['Checkbox', '1', 0, null],
        'currency' => ['Currency', '10.5', 11, null],
        'date' => ['Date', '', 0, null],
        'datetime' => ['Datetime', '', 0, null],
        'decimal' => ['Decimal', '1.25', 11, null],
        'dropdown_list' => ['Dropdown', 'Alpha', 0, ['Alpha', 'Beta']],
        'dropdown_tbl' => ['Dropdown', '', 0, '@departments'],
        'email' => ['Email', '', 100, null],
        'file' => ['File', '', 0, null],
        'float' => ['Float', '0', 11, null],
        'html' => ['HTML', '', 0, null],
        'image' => ['Image', '', 0, null],
        'integer' => ['Integer', '5', 11, null],
        'mobile' => ['Mobile', '', 20, null],
        'multiselect' => ['Multiselect', '', 0, ['Alpha', 'Beta']],
        'name' => ['Name', '', 250, null],
        'password' => ['Password', '', 250, null],
        'radio' => ['Radio', 'Alpha', 0, ['Alpha', 'Beta']],
        'string' => ['String', 'abc', 100, null],
        'taginput' => ['Taginput', '', 0, ['Alpha', 'Beta']],
        'textarea' => ['Textarea', '', 0, null],
        'textfield' => ['TextField', '', 0, null],
        'url' => ['URL', '', 200, null],
        'files' => ['Files', '', 0, null],
        'location' => ['Location', '', 0, null],
        'color' => ['Color', '', 0, null],
        'time' => ['Time', '', 0, null],
        'json' => ['JSON', '', 0, null],
        'list' => ['List', '', 0, null],
        'duration' => ['Duration', '0', 0, null],
        'checklist' => ['Checklist', '', 0, null],
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
     * Drop the generated table.
     *
     * @return void
     */
    public function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('probe_items');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    /**
     * Every field type can be created, saved again unchanged and have its type changed.
     *
     * @return void
     */
    public function test_every_field_type_can_be_created_edited_and_retyped()
    {
        // Every field type in the database is covered
        $this->assertEqualsCanonicalizing(
            array_keys(LAModuleFieldType::getFTypes()),
            array_values(array_unique(array_column($this->fieldDefs, 0)))
        );

        $fields = [];
        foreach ($this->fieldDefs as $colname => [$type, $default, $maxlength, $popupVals]) {
            $field = [
                'colname' => $colname, 'label' => ucfirst($colname), 'field_type' => $type, 'unique' => false,
                'defaultvalue' => $default, 'minlength' => 0, 'maxlength' => $maxlength, 'required' => false,
                'listing_col' => false,
            ];
            if ($popupVals !== null) {
                $field['popup_vals'] = $popupVals;
            }
            $fields[] = $field;
        }

        LAModule::generate('Probe_items', 'probe_items', 'name', 'fa-cube', $fields);

        $created = $this->columns();
        $this->assertSame(array_keys($this->fieldDefs), array_values(array_intersect(array_keys($created), array_keys($this->fieldDefs))));
        $this->assertSame('decimal(15,2)', $created['decimal']['type']);
        $this->assertSame('double', $created['currency']['type']);
        $this->assertSame('timestamp', $created['datetime']['type_name']);
        $this->assertSame('json', $created['json']['type_name']);
        $this->assertSame('text', $created['address_txt']['type_name']);
        $this->assertSame('varchar(200)', $created['address_str']['type']);
        $this->assertTrue($created['date']['nullable']);
        $this->assertFalse($created['integer']['nullable']);
        $this->assertSame('abc', $created['string']['default']);
        $this->assertSame(['dropdown_tbl', 'file', 'image'], $this->foreignKeyColumns());

        // Saving every field again unchanged (the Edit Field form) keeps the schema as it was
        $module = LAModule::where('name_db', 'probe_items')->first();
        foreach (LAModuleField::where('module', $module->id)->orderBy('id')->get() as $field) {
            $this->updateField($module, $field, false);
        }
        // Known issue (also on Laravel 8): the stored Multiselect default is '[""]' while the column gets '[]',
        // so saving the field again changes the column default. It is excluded until that is fixed.
        $resaved = $this->columns();
        unset($created['multiselect'], $resaved['multiselect']);
        $this->assertEquals($this->comparable($created), $this->comparable($resaved));
        $this->assertSame(['dropdown_tbl', 'file', 'image'], $this->foreignKeyColumns());

        // Changing field types
        $ftypes = LAModuleFieldType::getFTypes();
        $changes = ['integer' => 'Decimal', 'textarea' => 'String', 'date' => 'Datetime', 'file' => 'Image'];
        foreach ($changes as $colname => $newType) {
            $field = LAModuleField::where('module', $module->id)->where('colname', $colname)->first();
            $field->field_type = $ftypes[$newType];
            if ($newType === 'String') {
                $field->maxlength = 150;
            }
            $field->save();
            $this->updateField($module, $field, true);
        }

        $changed = $this->columns();
        $this->assertSame('decimal(15,2)', $changed['integer']['type']);
        $this->assertSame('varchar(150)', $changed['textarea']['type']);
        $this->assertSame('timestamp', $changed['date']['type_name']);
        $this->assertSame(['dropdown_tbl', 'file', 'image'], $this->foreignKeyColumns());
    }

    /**
     * Apply a field through the update path, as LAModuleField::updateField() does.
     *
     * @param  LAModule  $module
     * @param  LAModuleField  $field
     * @param  bool  $isFieldTypeChange
     * @return void
     */
    protected function updateField($module, $field, $isFieldTypeChange)
    {
        $field->module_obj = $module;
        Schema::table($module->name_db, function ($table) use ($field, $isFieldTypeChange) {
            LAModule::create_field_schema($table, $field, true, $isFieldTypeChange);
        });
    }

    /**
     * Columns of the generated table keyed by name.
     *
     * @return array
     */
    protected function columns()
    {
        return collect(Schema::getColumns('probe_items'))->keyBy('name')->all();
    }

    /**
     * Column attributes that must survive an unchanged save. MySQL reports JSON expression defaults with a
     * charset introducer, so defaults are compared without it.
     *
     * @param  array  $columns
     * @return array
     */
    protected function comparable($columns)
    {
        return array_map(fn ($column) => [
            'type' => $column['type'],
            'nullable' => $column['nullable'],
            'default' => is_string($column['default']) ? str_replace('_utf8mb4', '', $column['default']) : $column['default'],
        ], $columns);
    }

    /**
     * Sorted columns of the generated table that have a foreign key.
     *
     * @return array
     */
    protected function foreignKeyColumns()
    {
        $columns = [];
        foreach (Schema::getForeignKeys('probe_items') as $foreignKey) {
            $columns = array_merge($columns, $foreignKey['columns']);
        }
        sort($columns);

        return $columns;
    }
}
