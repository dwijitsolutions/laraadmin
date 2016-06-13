<?php

namespace Dwij\Laraadmin;

use Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

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
        //echo "Laraadmin Migrations started...";
        Artisan::call('migrate', ['--path' => "vendor/dwij/laraadmin/src/Migrations/"]);
        //echo "Migrations completed !!!.";
        // Execute by php artisan vendor:publish --provider="Dwij\Laraadmin\LAProvider"
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/routes.php';
        
        /*
        |--------------------------------------------------------------------------
        | Providers
        |--------------------------------------------------------------------------
        */
        
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        
        /*
        |--------------------------------------------------------------------------
        | Register the Utilities
        |--------------------------------------------------------------------------
        */
        
        // $this->app->singleton('FormMaker', function ($app) {
        //     return new FormMaker($app);
        // });
        
        $loader = AliasLoader::getInstance();
        // $loader->alias('FormMaker', \Yab\Laracogs\Facades\FormMaker::class);
        $loader->alias('Form', \Collective\Html\FormFacade::class);
        $loader->alias('HTML', \Collective\Html\HtmlFacade::class);
        
        //$this->app->make('Dwij\Laraadmin\CrudController');
        
        /*
        |--------------------------------------------------------------------------
        | Blade Directives
        |--------------------------------------------------------------------------
        */
        
        // LAForm Maker
        Blade::directive('la_input', function($expression) {
            return "<?php echo LAFormMaker::input$expression; ?>";
        });
        
        // LAForm Maker - Display Values
        Blade::directive('la_display', function($expression) {
            return "<?php echo LAFormMaker::display$expression; ?>";
        });
        
        // Form Maker
        Blade::directive('form_maker_table', function($expression) {
            return "<?php echo FormMaker::fromTable$expression; ?>";
        });
        Blade::directive('form_maker_array', function($expression) {
            return "<?php echo FormMaker::fromArray$expression; ?>";
        });
        Blade::directive('form_maker_object', function($expression) {
            return "<?php echo FormMaker::fromObject$expression; ?>";
        });
        Blade::directive('form_maker_columns', function($expression) {
            return "<?php echo FormMaker::getTableColumns$expression; ?>";
        });
        // Label Maker
        Blade::directive('input_maker_label', function($expression) {
            return "<?php echo InputMaker::label$expression; ?>";
        });
        Blade::directive('input_maker_create', function($expression) {
            return "<?php echo InputMaker::create$expression; ?>";
        });
        
        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */
        
        $this->commands([
            \Dwij\Laraadmin\Commands\Migration::class,
            \Dwij\Laraadmin\Commands\Crud::class
        ]);
    }
}
