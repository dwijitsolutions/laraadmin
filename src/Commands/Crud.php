<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Models\Module;

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
    var $module = null;
    var $templateDirectory = "";
    
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
        $filesystem = new Filesystem();
        $this->templateDirectory = __DIR__.'/../stubs';
        
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
        $this->singularVar = str_singular($table);
        $this->singularCapitalVar = ucfirst(str_singular($table));
        
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
        
        try {
            
            $this->createController();
            $this->createModel();
            $this->createViews();
            $this->appendRoutes();
                        
        } catch (Exception $e) {
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        
        $this->info("\nCRUD successfully generated for ".$this->moduleName."\n");
    }
    
    protected function createController() {
        $this->line('Creating controller...');
        $md = file_get_contents($this->templateDirectory."/controller.stub");
        
        $md = str_replace("__controller_class_name__", $this->controllerName, $md);
        $md = str_replace("__model_name__", $this->modelName, $md);
        $md = str_replace("__module_name__", $this->moduleName, $md);
        $md = str_replace("__view_column__", $this->module->view_col, $md);
        
        // Listing columns
        $listing_cols = "";
        foreach ($this->module->fields as $field) {
            $listing_cols .= "'".$field['colname']."', ";
        }
        $listing_cols = trim($listing_cols, ", ");
        
        $md = str_replace("__listing_cols__", $listing_cols, $md);
        $md = str_replace("__view_folder__", $this->dbTableName, $md);
        $md = str_replace("__route_resource__", $this->dbTableName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        $md = str_replace("__singular_var__", $this->singularVar, $md);
        
        file_put_contents(base_path('app/Http/Controllers/'.$this->controllerName.".php"), $md);
    }
    
    protected function createModel() {
        $this->line('Creating model...');
        $md = file_get_contents($this->templateDirectory."/model.stub");
        
        $md = str_replace("__model_class_name__", $this->modelName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        
        file_put_contents(base_path('app/'.$this->modelName.".php"), $md);
    }
    
    protected function createViews() {
        $this->line('Creating views...');
        
        // Create Folder
        @mkdir("resources/views/".$this->dbTableName, 0777, true);
        
        // ============================ Listing / Index ============================
        $md = file_get_contents($this->templateDirectory."/views/index.blade.stub");
        
        $md = str_replace("__module_name__", $this->moduleName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        $md = str_replace("__controller_class_name__", $this->controllerName, $md);
        $md = str_replace("__singular_var__", $this->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $this->singularCapitalVar, $md);
        
        // Listing columns
        $inputFields = "";
        foreach ($this->module->fields as $field) {
            $inputFields .= "\t\t\t\t\t@la_input($"."module, '".$field['colname']."')\n";
        }
        $inputFields = trim($inputFields);
        $md = str_replace("__input_fields__", $inputFields, $md);
        
        file_put_contents(base_path('resources/views/'.$this->dbTableName.'/index.blade.php'), $md);
        
        // ============================ Edit ============================
        $md = file_get_contents($this->templateDirectory."/views/edit.blade.stub");
        
        $md = str_replace("__module_name__", $this->moduleName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        $md = str_replace("__controller_class_name__", $this->controllerName, $md);
        $md = str_replace("__singular_var__", $this->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $this->singularCapitalVar, $md);
        
        // Listing columns
        $inputFields = "";
        foreach ($this->module->fields as $field) {
            $inputFields .= "\t\t\t\t\t@la_input($"."module, '".$field['colname']."')\n";
        }
        $inputFields = trim($inputFields);
        $md = str_replace("__input_fields__", $inputFields, $md);
        
        file_put_contents(base_path('resources/views/'.$this->dbTableName.'/edit.blade.php'), $md);
        
        // ============================ Show ============================
        $md = file_get_contents($this->templateDirectory."/views/show.blade.stub");
        
        $md = str_replace("__module_name__", $this->moduleName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        $md = str_replace("__singular_var__", $this->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $this->singularCapitalVar, $md);
        
        // Listing columns
        $displayFields = "";
        foreach ($this->module->fields as $field) {
            $displayFields .= "\t\t\t\t\t\t@la_display($"."module, '".$field['colname']."')\n";
        }
        $displayFields = trim($displayFields);
        $md = str_replace("__display_fields__", $displayFields, $md);
        
        file_put_contents(base_path('resources/views/'.$this->dbTableName.'/show.blade.php'), $md);
    }
    
    protected function appendRoutes() {
        $this->line('Appending routes...');
        $routesFile = app_path('Http/routes.php');
        
        $md = file_get_contents($this->templateDirectory."/routes.stub");
        
        $md = str_replace("__module_name__", $this->moduleName, $md);
        $md = str_replace("__controller_class_name__", $this->controllerName, $md);
        $md = str_replace("__db_table_name__", $this->dbTableName, $md);
        $md = str_replace("__singular_var__", $this->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $this->singularCapitalVar, $md);
        
        file_put_contents($routesFile, $md, FILE_APPEND);
    }
}
