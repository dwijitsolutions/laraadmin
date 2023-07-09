<?php
/***
 * Code generated using LaraAdmin
 * Help: https://laraadmin.com
 * LaraAdmin is open-sourced software licensed under the MIT license.
 * Developed by: Dwij IT Solutions
 * Developer Website: https://dwijitsolutions.com
 */

namespace App\Providers;

use App\Helpers\LAHelper;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

/***
 * LaraAdmin Provider
 *
 * This is LaraAdmin Service Provider which looks after managing aliases, other required providers, blade directives
 * and Commands.
 */
class LAProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // @mkdir(base_path('resources/laraadmin'));
        // @mkdir(base_path('database/migrations/laraadmin'));
        /*
        $this->publishes([
            __DIR__.'/Templates' => base_path('resources/laraadmin'),
            __DIR__.'/config.php' => base_path('config/laraadmin.php'),
            __DIR__.'/Migrations' => base_path('database/migrations/laraadmin')
        ]);
        */
        // echo "Laraadmin Migrations started...";
        // Artisan::call('migrate', ['--path' => "vendor/dwij/laraadmin/src/Migrations/"]);
        // echo "Migrations completed !!!.";
        // Execute by php artisan vendor:publish --provider="App\Providers\LAProvider"

        /*
        |--------------------------------------------------------------------------
        | Blade Directives for Entrust not working in Laravel 5.3
        |--------------------------------------------------------------------------
        */
        if (LAHelper::laravel_ver() >= 5.3) {
            // Call to Entrust::hasRole
            Blade::directive('role', function ($expression) {
                return "<?php if (\\Entrust::hasRole({$expression})) : ?>";
            });

            // Call to Entrust::can
            Blade::directive('permission', function ($expression) {
                return "<?php if (\\Entrust::can({$expression})) : ?>";
            });

            // Call to Entrust::ability
            Blade::directive('ability', function ($expression) {
                return "<?php if (\\Entrust::ability({$expression})) : ?>";
            });
        }

        Validator::extend('mincount', function ($attribute, $value, $parameters, $validator) {
            $minVal = intval($parameters[0]);
            if (is_array($value)) {
                $arr = $value;
            } else {
                $arr = json_decode($value);
            }

            return ! (count($arr) < $minVal);
        });
        Validator::extend('maxcount', function ($attribute, $value, $parameters, $validator) {
            $maxVal = intval($parameters[0]);
            if (is_array($value)) {
                $arr = $value;
            } else {
                $arr = json_decode($value);
            }

            return ! (count($arr) > $maxVal);
        });

        /*
        |--------------------------------------------------------------------------
        | Observers loading - for deleting data in Multiselect Fields
        |--------------------------------------------------------------------------
        */

        // \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Upload::observe(\App\Observers\UploadObserver::class);
        \App\Models\Department::observe(\App\Observers\DepartmentObserver::class);
        \App\Models\Employee::observe(\App\Observers\EmployeeObserver::class);
        \App\Models\Customer::observe(\App\Observers\CustomerObserver::class);
        // \App\Models\Role::observe(\App\Observers\RoleObserver::class);
        \App\Models\BlogCategory::observe(\App\Observers\BlogCategoryObserver::class);
        \App\Models\BlogPost::observe(\App\Observers\BlogPostObserver::class);
        \App\Models\LALog::observe(\App\Observers\LALogObserver::class);
        // End of Boot - Please do not edit this line.

        // Support Enum Data Type
        // if(file_exists(base_path('.env'))) {
        //     $platform = Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
        //     $platform->registerDoctrineTypeMapping('enum', 'string');
        // }
    }

    /**
     * Register the application services including routes, Required Providers, Alias, Controllers, Blade Directives
     * and Commands.
     *
     * @return void
     */
    public function register()
    {
        // For LAEditor
        if (file_exists(base_path('/vendor/laraadmin/editor'))) {
            include base_path('/vendor/laraadmin/editor/src/routes.php');
        }

        /*
        |--------------------------------------------------------------------------
        | Providers
        |--------------------------------------------------------------------------
        */

        // Collective HTML & Form Helper
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        // For Datatables
        $this->app->register(\Yajra\Datatables\DatatablesServiceProvider::class);
        // For Entrust
        $this->app->register(\Laraadmin\Entrust\EntrustServiceProvider::class);
        // For Spatie Backup
        $this->app->register(\Spatie\Backup\BackupServiceProvider::class);

        /*
        |--------------------------------------------------------------------------
        | Register the Alias
        |--------------------------------------------------------------------------
        */

        $loader = AliasLoader::getInstance();

        // Collective HTML & Form Helper
        $loader->alias('Form', \Collective\Html\FormFacade::class);
        $loader->alias('HTML', \Collective\Html\HtmlFacade::class);

        // For LaraAdmin Code Generation
        $loader->alias('CodeGenerator', \App\Helpers\CodeGenerator::class);

        // For LaraAdmin Form Helper
        $loader->alias('LAFormMaker', \App\Helpers\LAFormMaker::class);

        // For LaraAdmin Helper
        $loader->alias('LAHelper', \App\Helpers\LAHelper::class);

        // LaraAdmin Module Model
        $loader->alias('LAModule', \App\Models\LAModule::class);

        // For LaraAdmin Configuration Model
        $loader->alias('LAConfig', \App\Models\LAConfig::class);

        // For Entrust
        $loader->alias('Entrust', \Laraadmin\Entrust\EntrustFacade::class);

        /*
        |--------------------------------------------------------------------------
        | Register the Controllers
        |--------------------------------------------------------------------------
        */

        $this->app->make('App\Http\Controllers\LA\LAModuleController');
        $this->app->make('App\Http\Controllers\LA\LAModuleFieldController');
        $this->app->make('App\Http\Controllers\LA\LAMenuController');

        // For LAEditor
        if (file_exists(base_path('/vendor/laraadmin/editor'))) {
            $this->app->make('Laraadmin\Editor\Controllers\CodeEditorController');
        }

        /*
        |--------------------------------------------------------------------------
        | Blade Directives
        |--------------------------------------------------------------------------
        */

        // LAForm Input Maker
        Blade::directive('la_input', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php echo LAFormMaker::input$expression; ?>";
        });

        // LAForm Input Maker
        Blade::directive('la_config_input', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php echo LAFormMaker::config$expression; ?>";
        });

        // LAForm Form Maker
        Blade::directive('la_form', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php echo LAFormMaker::form$expression; ?>";
        });

        // LAForm Maker - Display Values
        Blade::directive('la_display', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php echo LAFormMaker::display$expression; ?>";
        });

        // LAForm Maker - Check Whether User has Module Access
        Blade::directive('la_access', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php if(LAFormMaker::la_access$expression) { ?>";
        });
        Blade::directive('endla_access', function ($expression) {
            return '<?php } ?>';
        });

        // LAForm Maker - Check Whether User has Module Field Access
        Blade::directive('la_field_access', function ($expression) {
            if (LAHelper::laravel_ver() >= 5.3) {
                $expression = '('.$expression.')';
            }

            return "<?php if(LAFormMaker::la_field_access$expression) { ?>";
        });
        Blade::directive('endla_field_access', function ($expression) {
            return '<?php } ?>';
        });

        // Blade directive for Anchors for Ajax Page Load
        Blade::directive('ajaxload', function ($expression) {
            return config('laraadmin.ajaxload');
        });

        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */

        $commands = [
            \App\Console\Commands\Migration::class,
            \App\Console\Commands\Crud::class
        ];

        // For LAEditor
        if (file_exists(base_path('/vendor/laraadmin/editor'))) {
            $commands[] = \Laraadmin\Editor\Commands\LAEditor::class;
        }

        $this->commands($commands);
    }
}
