<?php

namespace Dwijitso\Sbscrud;

use Illuminate\Support\ServiceProvider;

class SbsCrudProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // @mkdir(base_path('resources/laracogs/crud'));
        // $this->publishes([
        //     __DIR__.'/Templates' => base_path('resources/laracogs/crud'),
        //     __DIR__.'/Starter/config/laracogs.php' => base_path('config/laracogs.php'),
        // ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/Http/routes.php';
        
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
        
        $this->app->make('Dwijitso\Sbscrud\CrudController');
        
        /*
        |--------------------------------------------------------------------------
        | Blade Directives
        |--------------------------------------------------------------------------
        */
        
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
            // \Yab\Laracogs\Console\Crud::class,
            // \Yab\Laracogs\Console\TableCrud::class
        ]);

        
    }
}
