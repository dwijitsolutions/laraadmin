<?php

namespace Dwij\Laraadmin;

use Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

use Dwij\Laraadmin\Helpers\LAHelper;

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
        // Artisan::call('migrate', ['--path' => "vendor/dwij/laraadmin/src/Migrations/"]);
        //echo "Migrations completed !!!.";
        // Execute by php artisan vendor:publish --provider="Dwij\Laraadmin\LAProvider"
		
		/*
        |--------------------------------------------------------------------------
        | Blade Directives for Entrust not working in Laravel 5.3
        |--------------------------------------------------------------------------
        */
		if(LAHelper::laravel_ver() == 5.3) {
			
			// Call to Entrust::hasRole
			Blade::directive('role', function($expression) {
				return "<?php if (\\Entrust::hasRole({$expression})) : ?>";
			});
			
			// Call to Entrust::can
			Blade::directive('permission', function($expression) {
				return "<?php if (\\Entrust::can({$expression})) : ?>";
			});
			
			// Call to Entrust::ability
			Blade::directive('ability', function($expression) {
				return "<?php if (\\Entrust::ability({$expression})) : ?>";
			});
		}
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
        
        // Collective HTML & Form Helper
        $this->app->register(\Collective\Html\HtmlServiceProvider::class);
        // For Datatables
        $this->app->register(\Yajra\Datatables\DatatablesServiceProvider::class);
        // For Gravatar
        $this->app->register(\Creativeorange\Gravatar\GravatarServiceProvider::class);
        // For Entrust
        $this->app->register(\Zizaco\Entrust\EntrustServiceProvider::class);
        
        /*
        |--------------------------------------------------------------------------
        | Register the Alias
        |--------------------------------------------------------------------------
        */
        
        $loader = AliasLoader::getInstance();
        
        // Collective HTML & Form Helper
        $loader->alias('Form', \Collective\Html\FormFacade::class);
        $loader->alias('HTML', \Collective\Html\HtmlFacade::class);
        
        // For Gravatar User Profile Pics
        $loader->alias('Gravatar', \Creativeorange\Gravatar\Facades\Gravatar::class);
        
        // For LaraAdmin Code Generation
        $loader->alias('CodeGenerator', \Dwij\Laraadmin\CodeGenerator::class);
        
        // For LaraAdmin Form Helper
        $loader->alias('LAFormMaker', \Dwij\Laraadmin\LAFormMaker::class);
        
        // For LaraAdmin Helper
        $loader->alias('LAHelper', \Dwij\Laraadmin\Helpers\LAHelper::class);
        
        // LaraAdmin Module Model 
        $loader->alias('Module', \Dwij\Laraadmin\Models\Module::class);

		// For LaraAdmin Configuration Model
		$loader->alias('LAConfigs', \Dwij\Laraadmin\Models\LAConfigs::class);
		
        // For Entrust
		$loader->alias('Entrust', \Zizaco\Entrust\EntrustFacade::class);
        $loader->alias('role', \Zizaco\Entrust\Middleware\EntrustRole::class);
        $loader->alias('permission', \Zizaco\Entrust\Middleware\EntrustPermission::class);
        $loader->alias('ability', \Zizaco\Entrust\Middleware\EntrustAbility::class);
        
        /*
        |--------------------------------------------------------------------------
        | Register the Controllers
        |--------------------------------------------------------------------------
        */
        
        $this->app->make('Dwij\Laraadmin\Controllers\ModuleController');
        $this->app->make('Dwij\Laraadmin\Controllers\FieldController');
        $this->app->make('Dwij\Laraadmin\Controllers\CodeEditorController');
        $this->app->make('Dwij\Laraadmin\Controllers\MenuController');
		
		/*
        |--------------------------------------------------------------------------
        | Blade Directives
        |--------------------------------------------------------------------------
        */
        
        // LAForm Input Maker
        Blade::directive('la_input', function($expression) {
			if(LAHelper::laravel_ver() == 5.3) {
				$expression = "(".$expression.")";
			}
            return "<?php echo LAFormMaker::input$expression; ?>";
        });
        
        // LAForm Form Maker
        Blade::directive('la_form', function($expression) {
			if(LAHelper::laravel_ver() == 5.3) {
				$expression = "(".$expression.")";
			}
            return "<?php echo LAFormMaker::form$expression; ?>";
        });
        
        // LAForm Maker - Display Values
        Blade::directive('la_display', function($expression) {
			if(LAHelper::laravel_ver() == 5.3) {
				$expression = "(".$expression.")";
			}
            return "<?php echo LAFormMaker::display$expression; ?>";
        });
        
        // LAForm Maker - Check Whether User has Module Access
        Blade::directive('la_access', function($expression) {
			if(LAHelper::laravel_ver() == 5.3) {
				$expression = "(".$expression.")";
			}
            return "<?php if(LAFormMaker::la_access$expression) { ?>";
        });
        Blade::directive('endla_access', function($expression) {
            return "<?php } ?>";
        });
        
        // LAForm Maker - Check Whether User has Module Field Access
        Blade::directive('la_field_access', function($expression) {
			if(LAHelper::laravel_ver() == 5.3) {
				$expression = "(".$expression.")";
			}
            return "<?php if(LAFormMaker::la_field_access$expression) { ?>";
        });
        Blade::directive('endla_field_access', function($expression) {
            return "<?php } ?>";
        });
        
        /*
        |--------------------------------------------------------------------------
        | Register the Commands
        |--------------------------------------------------------------------------
        */
        
        $this->commands([
            \Dwij\Laraadmin\Commands\Migration::class,
            \Dwij\Laraadmin\Commands\Crud::class,
            \Dwij\Laraadmin\Commands\Packaging::class,
            \Dwij\Laraadmin\Commands\LAInstall::class
        ]);
    }
}
