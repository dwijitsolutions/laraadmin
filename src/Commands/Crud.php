<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Crud extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'la:crud {table}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD Methods for given Table / Module.';
    
    /* ================ Config ================ */
    var $controllerName = "";
    var $modelName = "";
    var $moduleName = "";
    var $dbTableName = "";
    

    /**
     * Generate a CRUD files inclusing Controller, Model and Routes
     *
     * @return mixed
     */
    public function handle()
    {
        $filesystem = new Filesystem();
        
        $table = $this->argument('table');
        
        if(starts_with($table, "create_")) {
            $tname = str_replace("create_", "",$table);
            $table = str_replace("_table", "",$tname);
        }
        
        $this->modelName = ucfirst(str_singular($table));
        $tableP = str_plural(strtolower($table));
        $tableS = str_singular(strtolower($table));
        $this->dbTableName = $tableP;
        $this->moduleName = ucfirst(str_plural($table));
        $this->controllerName = ucfirst(str_plural($table))."Controller";
        
        $this->info("Model:\t    ".$this->modelName);
        $this->info("Module:\t    ".$this->moduleName);
        $this->info("Table:\t    ".$this->dbTableName);
        $this->info("Controller: ".$this->controllerName);
        
        $templateDirectory = __DIR__.'/../stubs';
        
        try {
            $this->createController();
        } catch (Exception $e) {
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        
        $this->info("\nCRUD successfully generated for ".$this->moduleName."\n");
    }
    
    protected function createController() {
        $this->line('Creating controller...');
        $md = file_get_contents($templateDirectory."/controller.stub");
        
        $md = str_replace("__controller_class_name__", $this->controllerName, $md);
        $md = str_replace("__model_name__", $this->modelName, $md);
        $md = str_replace("__module_name__", $moduleName, $md);
        
        file_put_contents(base_path('app/Http/Controllers/'.$this->controllerName), $md);
    }
}
