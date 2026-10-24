<?php

namespace Tests\Feature;

use App\Models\LAMenu;
use App\Models\LAModule;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrudGeneratorTest extends TestCase
{
    /**
     * Source files the CRUD generator edits in place, restored after each test.
     *
     * @var array
     */
    protected $editedFiles = ['routes/admin.php', 'app/Providers/LAProvider.php', 'config/laraadmin.php'];

    /**
     * Files the CRUD generator creates for the Gadgets module.
     *
     * @var array
     */
    protected $generatedFiles = [
        'app/Http/Controllers/LA/GadgetsController.php',
        'app/Models/Gadget.php',
        'app/Observers/GadgetObserver.php',
        'resources/views/la/gadgets/index.blade.php',
        'resources/views/la/gadgets/edit.blade.php',
        'resources/views/la/gadgets/show.blade.php',
        'resources/lang/en/la_gadget.php',
    ];

    /**
     * Original contents and permissions of $editedFiles.
     *
     * @var array
     */
    protected $snapshots = [];

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

        foreach ($this->editedFiles as $file) {
            $this->snapshots[$file] = [file_get_contents(base_path($file)), fileperms(base_path($file)) & 0777];
        }
    }

    /**
     * Restore edited source files and remove generated ones, even when a test fails midway.
     *
     * @return void
     */
    public function tearDown(): void
    {
        // The generator also chmods the files it edits
        foreach ($this->snapshots as $file => [$contents, $perms]) {
            file_put_contents(base_path($file), $contents);
            chmod(base_path($file), $perms);
        }
        foreach ($this->generatedFiles as $file) {
            File::delete(base_path($file));
        }
        File::deleteDirectory(resource_path('views/la/gadgets'));
        Schema::dropIfExists('gadgets');

        parent::tearDown();
    }

    /**
     * Generate a module's CRUD with la:crud, use it over HTTP, then delete the module.
     *
     * @return void
     */
    public function test_generate_use_and_delete_crud()
    {
        LAModule::generate('Gadgets', 'gadgets', 'name', 'fa-cube', [
            [
                'colname' => 'name', 'label' => 'Name', 'field_type' => 'Name', 'unique' => false, 'defaultvalue' => '',
                'minlength' => 1, 'maxlength' => 250, 'required' => true, 'listing_col' => true,
            ], [
                'colname' => 'price', 'label' => 'Price', 'field_type' => 'Currency', 'unique' => false, 'defaultvalue' => '',
                'minlength' => 0, 'maxlength' => 11, 'required' => false, 'listing_col' => true,
            ], [
                'colname' => 'released', 'label' => 'Released', 'field_type' => 'Date', 'unique' => false, 'defaultvalue' => '',
                'minlength' => 0, 'maxlength' => 0, 'required' => false, 'listing_col' => true,
            ],
        ]);

        $this->artisan('la:crud', ['module' => 'Gadgets'])->assertSuccessful()->run();

        // Module Manager's "Generate CRUD" also grants Super Admin full access (LAModuleController::generate_crud)
        $module = LAModule::where('name', 'Gadgets')->first();
        LAModule::setDefaultRoleAccess($module->id, Role::where('name', 'SUPER_ADMIN')->first()->id, 'full');

        // Generated files exist and are valid PHP
        foreach ($this->generatedFiles as $file) {
            $this->assertFileExists(base_path($file));
            $this->assertValidPhp(base_path($file));
        }

        // Edited files gained their entries and stay valid PHP
        $routes = file_get_contents(base_path('routes/admin.php'));
        $this->assertStringContainsString("'GadgetsController'", $routes);
        $this->assertStringContainsString("'GadgetsController@dtajax'", $routes);
        $this->assertStringContainsString('GadgetObserver', file_get_contents(base_path('app/Providers/LAProvider.php')));
        $this->assertStringContainsString('GADGET_CREATED', file_get_contents(base_path('config/laraadmin.php')));
        foreach ($this->editedFiles as $file) {
            $this->assertValidPhp(base_path($file));
        }
        $this->assertSame(1, LAMenu::where('url', 'gadgets')->count());

        // Reboot so the generated routes, config and controller are loaded, then use the CRUD
        $this->refreshApplication();
        $user = $this->registerSuperAdmin();
        $admin = '/'.config('laraadmin.adminRoute');

        $this->actingAs($user)->get($admin.'/gadgets')->assertStatus(200);
        $this->actingAs($user)->post($admin.'/gadgets', [
            'name' => 'Widget', 'price' => '12.50', 'released' => '24/10/2026',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $gadget = DB::table('gadgets')->where('name', 'Widget')->first();
        $this->assertNotNull($gadget);
        $this->assertEquals(12.5, $gadget->price);

        $this->actingAs($user)->get($admin.'/gadgets/'.$gadget->id)->assertStatus(200);
        $this->actingAs($user)->get($admin.'/gadgets/'.$gadget->id.'/edit')->assertStatus(200);
        $dtAjax = $this->actingAs($user)->get($admin.'/gadget_dt_ajax')->assertStatus(200);
        $dtAjax->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.id', $gadget->id);
        $this->assertStringContainsString('>Widget</a>', $dtAjax->json('data.0.name'));
        $this->assertStringContainsString('<form method="POST"', $dtAjax->json('data.0.dt_action'));

        // Delete the module: generated files, routes, observer, menu and table are removed
        LAModule::deleteModule($module->id);

        foreach ($this->generatedFiles as $file) {
            if (! str_starts_with($file, 'resources/views/')) {
                $this->assertFileDoesNotExist(base_path($file));
            }
        }
        $this->assertSame([], File::files(resource_path('views/la/gadgets')));
        $this->assertStringNotContainsString('GadgetsController', file_get_contents(base_path('routes/admin.php')));
        $this->assertStringNotContainsString('GadgetObserver', file_get_contents(base_path('app/Providers/LAProvider.php')));
        $this->assertValidPhp(base_path('routes/admin.php'));
        $this->assertValidPhp(base_path('app/Providers/LAProvider.php'));
        $this->assertSame(0, LAMenu::where('url', 'gadgets')->count());
        $this->assertFalse(Schema::hasTable('gadgets'));
    }

    /**
     * Assert that a file passes "php -l".
     *
     * @param  string  $path
     * @return void
     */
    protected function assertValidPhp($path)
    {
        exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($path).' 2>&1', $output, $exitCode);
        $this->assertSame(0, $exitCode, implode("\n", $output));
    }
}
