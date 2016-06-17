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
        
        if(starts_with($module, "create_")) {
            $tname = str_replace("create_", "",$module);
            $module = str_replace("_table", "",$tname);
        }
        
        $this->modelName = ucfirst(str_singular($module));
        $tableP = str_plural(strtolower($module));
        $tableS = str_singular(strtolower($module));
        $this->dbTableName = $tableP;
        $this->moduleName = ucfirst(str_plural($module));
        $this->controllerName = ucfirst(str_plural($module))."Controller";
        $this->singularVar = str_singular($module);
        $this->singularCapitalVar = ucfirst(str_singular($module));
        
        $this->info("Model:\t    ".$this->modelName);
        $this->info("Module:\t    ".$this->moduleName);
        $this->info("Table:\t    ".$this->dbTableName);
        $this->info("Controller: ".$this->controllerName);
        
        $module = Module::get($this->moduleName);
        
        if(!isset($module->id)) {
            throw new Exception("Please run 'php artisan migrate' for 'create_".$this->dbTableName."_table' in order to create CRUD.\nOr check if any problem in Module Name '".$this->moduleName."'.", 1);
            return;
        }
        $this->module = $module;
        
        $config = array();
        $config = (object) $config;
        $config->module = $this->module;
        $config->modelName = $this->modelName;
        $config->moduleName = $this->moduleName;
        $config->dbTableName = $this->dbTableName;
        $config->controllerName = $this->controllerName;
        $config->singularVar = $this->singularVar;
        $config->singularCapitalVar = $this->singularCapitalVar;
        
        try {
            
            CodeGenerator::createController($config, $this);
            CodeGenerator::createModel($config, $this);
            CodeGenerator::createViews($config, $this);
            CodeGenerator::appendRoutes($config, $this);
            CodeGenerator::addMenu($config, $this);
            
        } catch (Exception $e) {
            $this->error("Crud::handle exception: ".$e);
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        
        $this->info("\nCRUD successfully generated for ".$this->moduleName."\n");
    }
}
