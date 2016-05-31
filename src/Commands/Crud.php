<?php

namespace Dwijitso\Sbscrud\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Dwijitso\Sbscrud\CrudGenerator;
use Illuminate\Console\AppNamespaceDetectorTrait;

class Crud extends Command
{
    use AppNamespaceDetectorTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'dwijsbs:crud {table} {--api} {--migration} {--bootstrap} {--semantic} {--schema=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is proprietory package of Dwij IT Solutions for Dwij SBS for CRUD generation from defined models.';

    /**
     * Generate a CRUD stack
     *
     * @return mixed
     */
    public function handle()
    {
        $section = false;
        $crudGenerator = new CrudGenerator();
        $filesystem = new Filesystem();

        $table = ucfirst(str_singular($this->argument('table')));

        if (stristr($table, '_')) {
            $splitTable = explode('_', $table);
            $table = $splitTable[1];
            $section = $splitTable[0];
        }

        if ($this->option('schema')) {
            foreach (explode(',', $this->option('schema')) as $column) {
                $columnDefinition = explode(':', $column);
                if (! in_array($columnDefinition[1], $this->columnTypes)) {
                    throw new Exception("$columnDefinition[1] is not in the array of valid column types: ".implode(', ', $this->columnTypes), 1);
                }
            }
        }

        $config = [];
        $config = [
            'template_source'            => '',
            'bootstrap'                  => false,
            'semantic'                   => false,
            'schema'                     => null,
            '_sectionPrefix_'            => '',
            '_sectionTablePrefix_'       => '',
            '_sectionRoutePrefix_'       => '',
            '_sectionNamespace_'         => '',
            '_path_facade_'              => app_path('Facades'),
            '_path_service_'             => app_path('Services'),
            '_path_repository_'          => app_path('Repositories/_table_'),
            '_path_model_'               => app_path('Repositories/_table_'),
            '_path_controller_'          => app_path('Http/Controllers/'),
            '_path_api_controller_'      => app_path('Http/Controllers/Api'),
            '_path_views_'               => base_path('resources/views'),
            '_path_tests_'               => base_path('tests'),
            '_path_request_'             => app_path('Http/Requests/'),
            '_path_routes_'              => app_path('Http/routes.php'),
            '_path_api_routes_'          => app_path('Http/api-routes.php'),
            'routes_prefix'              => '',
            'routes_suffix'              => '',
            '_app_namespace_'            => $this->getAppNamespace(),
            '_namespace_services_'       => $this->getAppNamespace().'Services',
            '_namespace_facade_'         => $this->getAppNamespace().'Facades',
            '_namespace_repository_'     => $this->getAppNamespace().'Repositories\_table_',
            '_namespace_model_'          => $this->getAppNamespace().'Repositories\_table_',
            '_namespace_controller_'     => $this->getAppNamespace().'Http\Controllers',
            '_namespace_api_controller_' => $this->getAppNamespace().'Http\Controllers\Api',
            '_namespace_request_'        => $this->getAppNamespace().'Http\Requests',
            '_table_name_'               => str_plural(strtolower($table)),
            '_lower_case_'               => strtolower($table),
            '_lower_casePlural_'         => str_plural(strtolower($table)),
            '_camel_case_'               => ucfirst(camel_case($table)),
            '_camel_casePlural_'         => str_plural(camel_case($table)),
            '_ucCamel_casePlural_'       => ucfirst(str_plural(camel_case($table))),
        ];

        $templateDirectory = __DIR__.'/Templates';

        if (is_dir(base_path('resources/laracogs/crud'))) {
            $templateDirectory = base_path('resources/laracogs/crud');
        }

        $config['template_source'] = Config::get('laracogs.crud.template_source', $templateDirectory);

        $config = array_merge($config, Config::get('laracogs.crud.single', []));
        $config = $this->setConfig($config, $section, $table);

        if ($section) {
            $config = [];
            $config = [
                'template_source'            => '',
                'bootstrap'                  => false,
                'semantic'                   => false,
                'schema'                     => null,
                '_sectionPrefix_'            => strtolower($section).'.',
                '_sectionTablePrefix_'       => strtolower($section).'_',
                '_sectionRoutePrefix_'       => strtolower($section).'/',
                '_sectionNamespace_'         => ucfirst($section).'\\',
                '_path_facade_'              => app_path('Facades'),
                '_path_service_'             => app_path('Services'),
                '_path_repository_'          => app_path('Repositories/'.ucfirst($section).'/'.ucfirst($table)),
                '_path_model_'               => app_path('Repositories/'.ucfirst($section).'/'.ucfirst($table)),
                '_path_controller_'          => app_path('Http/Controllers/'.ucfirst($section).'/'),
                '_path_api_controller_'      => app_path('Http/Controllers/Api/'.ucfirst($section).'/'),
                '_path_views_'               => base_path('resources/views/'.strtolower($section)),
                '_path_tests_'               => base_path('tests'),
                '_path_request_'             => app_path('Http/Requests/'.ucfirst($section)),
                '_path_routes_'              => app_path('Http/routes.php'),
                '_path_api_routes_'          => app_path('Http/api-routes.php'),
                'routes_prefix'              => "\n\nRoute::group(['namespace' => '".ucfirst($section)."', 'prefix' => '".strtolower($section)."', 'middleware' => ['web']], function () { \n",
                'routes_suffix'              => "\n});",
                '_app_namespace_'            => $this->getAppNamespace(),
                '_namespace_services_'       => $this->getAppNamespace().'Services\\'.ucfirst($section),
                '_namespace_facade_'         => $this->getAppNamespace().'Facades',
                '_namespace_repository_'     => $this->getAppNamespace().'Repositories\\'.ucfirst($section).'\\'.ucfirst($table),
                '_namespace_model_'          => $this->getAppNamespace().'Repositories\\'.ucfirst($section).'\\'.ucfirst($table),
                '_namespace_controller_'     => $this->getAppNamespace().'Http\Controllers\\'.ucfirst($section),
                '_namespace_api_controller_' => $this->getAppNamespace().'Http\Controllers\Api\\'.ucfirst($section),
                '_namespace_request_'        => $this->getAppNamespace().'Http\Requests\\'.ucfirst($section),
                '_table_name_'               => str_plural(strtolower(implode('_', $splitTable))),
                '_lower_case_'               => strtolower($table),
                '_lower_casePlural_'         => str_plural(strtolower($table)),
                '_camel_case_'               => ucfirst(camel_case($table)),
                '_camel_casePlural_'         => str_plural(camel_case($table)),
                '_ucCamel_casePlural_'       => ucfirst(str_plural(camel_case($table))),
            ];

            $templateDirectory = __DIR__.'/Templates';

            if (is_dir(base_path('resources/laracogs/crud'))) {
                $templateDirectory = base_path('resources/laracogs/crud');
            }

            $config['template_source'] = Config::get('laracogs.crud.template_source', $templateDirectory);
            $config = array_merge($config, Config::get('laracogs.crud.sectioned', []));

            $config = $this->setConfig($config, $section, $table);

            foreach ($config as $key => $value) {
                if (in_array($key, ['_path_repository_', '_path_model_', '_path_controller_', '_path_api_controller_', '_path_views_', '_path_request_',])) {
                    @mkdir($value, 0777, true);
                }
            }
        }

        if ($this->option('bootstrap')) {
            $config['bootstrap'] = true;
        }

        if ($this->option('semantic')) {
            $config['semantic'] = true;
        }

        if ($this->option('schema')) {
            $config['schema'] = $this->option('schema');
        }

        if (! isset($config['template_source'])) {
            $config['template_source'] = __DIR__.'/Templates';
        }

        try {
            $this->line('Building controller...');
            $crudGenerator->createController($config);

            $this->line('Building repository...');
            $crudGenerator->createRepository($config);

            $this->line('Building request...');
            $crudGenerator->createRequest($config);

            $this->line('Building service...');
            $crudGenerator->createService($config);

            $this->line('Building views...');
            $crudGenerator->createViews($config);

            $this->line('Building routes...');
            $crudGenerator->createRoutes($config, false);

            $this->line('Building tests...');
            $crudGenerator->createTests($config);

            $this->line('Building factory...');
            $crudGenerator->createFactory($config);

            $this->line('Building facade...');
            $crudGenerator->createFacade($config);

            if ($this->option('api')) {
                $this->line('Building Api...');
                $this->comment("\nAdd the following to your app/Providers/RouteServiceProvider.php: \n");
                $this->info("require app_path('Http/api-routes.php'); \n");
                $crudGenerator->createApi($config);
            }

        } catch (Exception $e) {
            throw new Exception("Unable to generate your CRUD: ".$e->getMessage(), 1);
        }

        try {
            if ($this->option('migration')) {
                $this->line('Building migration...');
                if ($section) {
                    $migrationName = 'create_'.str_plural(strtolower(implode('_', $splitTable))).'_table';
                    Artisan::call('make:migration', [
                        'name' => $migrationName,
                        '--table' => str_plural(strtolower(implode('_', $splitTable))),
                        '--create' => true,
                    ]);
                } else {
                    $migrationName = 'create_'.str_plural(strtolower($table)).'_table';
                    Artisan::call('make:migration', [
                        'name' => $migrationName,
                        '--table' => str_plural(strtolower($table)),
                        '--create' => true,
                    ]);
                }

                if ($this->option('schema')) {
                    $migrationFiles = $filesystem->allFiles(base_path('database/migrations'));
                    foreach ($migrationFiles as $file) {
                        if (stristr($file->getBasename(), $migrationName) ) {
                            $migrationData = file_get_contents($file->getPathname());
                            $parsedTable = "";

                            foreach (explode(',', $this->option('schema')) as $key => $column) {
                                $columnDefinition = explode(':', $column);
                                if ($key === 0) {
                                    $parsedTable .= "\$table->$columnDefinition[1]('$columnDefinition[0]');\n";
                                } else {
                                    $parsedTable .= "\t\t\t\$table->$columnDefinition[1]('$columnDefinition[0]');\n";
                                }
                            }

                            $migrationData = str_replace("\$table->increments('id');", $parsedTable, $migrationData);
                            file_put_contents($file->getPathname(), $migrationData);
                        }
                    }
                }
            } else {
                $this->info("\nYou will want to create a migration in order to get the $table tests to work correctly.\n");
            }
        } catch (Exception $e) {
            throw new Exception("Could not process the migration but your CRUD was generated", 1);
        }

        $this->info('You may wish to add this as your testing database');
        $this->comment("'testing' => [ 'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '' ],");
        $this->info('CRUD for '.$table.' is done.'."\n");
    }

    /**
     * Set the config
     *
     * @param array $config
     * @param string $section
     * @param string $table
     *
     * @return  array
     */
    public function setConfig($config, $section, $table)
    {
        if (! is_null($section)) {
            foreach ($config as $key => $value) {
                $config[$key] = str_replace('_table_', ucfirst($table), str_replace('_section_', ucfirst($section), str_replace('_sectionLowerCase_', strtolower($section), $value)));
            }
        } else {
            foreach ($config as $key => $value) {
                $config[$key] = str_replace('_table_', ucfirst($table), $value);
            }
        }

        return $config;
    }
}
