<?php

namespace Dwij\Laraadmin;

use Illuminate\Filesystem\Filesystem;
use Exception;

/**
 * Generate the CRUD
 */
class CrudGenerator
{
    protected $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem;
    }

    /**
     * Create the controller
     * @param  array $config
     * @return bool
     */
    public function createController($config)
    {
        $request = file_get_contents($config['template_source'].'/Controller.stub');

        foreach ($config as $key => $value) {
            $request = str_replace($key, $value, $request);
        }

        $request = file_put_contents($config['_path_controller_'].'/'.$config['_camel_case_'].'Controller.php', $request);

        return $request;
    }

    /**
     * Create the routes
     * @param  array $config
     * @return bool
     */
    public function createRoutes($config, $appendRoutes = true)
    {
        if ($appendRoutes) {
            $routesMaster = app_path('Http/routes.php');
        } else {
            $routesMaster = $config['_path_routes_'];
        }

        if (! empty($config['routes_prefix'])) {
            file_put_contents($routesMaster, $config['routes_prefix'], FILE_APPEND);
        }

        $routes = file_get_contents($config['template_source'].'/Routes.stub');

        foreach ($config as $key => $value) {
            $routes = str_replace($key, $value, $routes);
        }

        file_put_contents($routesMaster, $routes, FILE_APPEND);

        if (! empty($config['routes_prefix'])) {
            file_put_contents($routesMaster, $config['routes_suffix'], FILE_APPEND);
        }

        return true;
    }

    /**
     * Create the views
     * @param  array $config
     * @return bool
     */
    public function createViews($config)
    {
        if (! is_dir($config['_path_views_'].'/'.$config['_lower_casePlural_'])) mkdir($config['_path_views_'].'/'.$config['_lower_casePlural_']);

        $viewTemplates = 'Views';

        if ($config['bootstrap']) {
            $viewTemplates = 'BootstrapViews';
        }

        if ($config['semantic']) {
            $viewTemplates = 'SemanticViews';
        }

        foreach (glob($config['template_source'].'/'.$viewTemplates.'/*') as $file) {
            $createdView = file_get_contents($file);
            $basename = str_replace('txt', 'php', basename($file));
            foreach ($config as $key => $value) {
                $createdView = str_replace($key, $value, $createdView);
            }
            $createdView = file_put_contents($config['_path_views_'].'/'.$config['_lower_casePlural_'].'/'.$basename, $createdView);
        }

        return ($createdView);
    }
}
