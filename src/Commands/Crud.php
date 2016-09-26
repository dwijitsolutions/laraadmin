<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\CodeGenerator;

class Crud extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'la:crud {module}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD Methods for given Module.';
    
    /* ================ Config ================ */
    var $module = null;
    var $controllerName = "";
    var $modelName = "";
    var $moduleName = "";
    var $dbTableName = "";
    var $singularVar = "";
    var $singularCapitalVar = "";
    
    /**
     * Generate a CRUD files inclusing Controller, Model and Routes
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('module');
        
        try {
            
            $config = CodeGenerator::generateConfig($module, "fa-cube");
            
            CodeGenerator::createController($config, $this);
            CodeGenerator::createModel($config, $this);
            CodeGenerator::createViews($config, $this);
            CodeGenerator::appendRoutes($config, $this);
            CodeGenerator::addMenu($config, $this);
            
        } catch (Exception $e) {
            $this->error("Crud::handle exception: ".$e);
            throw new Exception("Unable to generate migration for ".($module)." : ".$e->getMessage(), 1);
        }
        $this->info("\nCRUD successfully generated for ".($module)."\n");
    }
}
