<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;

class Migration extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'la:migration {table} {--generate}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Genrate Migrations for LaraAdmin';

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
        
        $modelName = ucfirst(str_singular($table));
        $tableP = str_plural(strtolower($table));
        $tableS = str_singular(strtolower($table));
        $migrationName = 'create_'.$tableP.'_table';
        $migrationFileName = date("Y_m_d_His_").$migrationName.".php";
        $migrationClassName = ucfirst(camel_case($migrationName));
        $dbTableName = $tableP;
        $moduleName = ucfirst(str_plural($table));
        
        $this->info("Model:\t   ".$modelName);
        $this->info("Module:\t   ".$moduleName);
        $this->info("Table:\t   ".$dbTableName);
        $this->info("Migration: ".$migrationName."\n");
        
        // Reverse migration generation from table
        $generateData = "";
        $viewColumnName = "view_column_name e.g. name";
        $generate = $this->option('generate');
        if($generate) {
            // check if table, module and module fields exists
            $module = Module::get($moduleName);
            if(isset($module)) {
                $this->info("Module exists :\t   ".$moduleName);
                
                $viewColumnName = $module->view_col;
                
                $ftypes = ModuleFieldTypes::getFTypes2();
                foreach ($module->fields as $field) {
                    $ftype = $ftypes[$field['field_type']];
                    $readonly = "false";
                    if($field['readonly']) {
                        $readonly = "true";
                    }
                    $dvalue = "";
                    if($field['defaultvalue'] != "") {
                        if(starts_with($field['defaultvalue'], "[")) {
                            $dvalue = $field['defaultvalue'];
                        } else {
                            $dvalue = '"'.$field['defaultvalue'].'"';
                        }
                    } else {
                        $dvalue = '""';
                    }
                    $minlength = $field['minlength'];
                    $maxlength = $field['maxlength'];
                    $required = "false";
                    if($field['required']) {
                        $required = "true";
                    }
                    $values = "";
                    if($field['popup_vals'] != "") {
                        if(starts_with($field['popup_vals'], "[")) {
                            $values = ', '.$field['popup_vals'];
                        } else {
                            $values = ', "'.$field['popup_vals'].'"';
                        }
                    }
                    $generateData .= '["'.$field['colname'].'", "'.$field['label'].'", "'.$ftype.'", '.$readonly.', '.$dvalue.', '.$minlength.', '.$maxlength.', '.$required.''.$values.'],'."\n            ";
                }
                $generateData = trim($generateData);
                
                // Find existing migration file
                $mfiles = scandir(base_path('database/migrations/'));
                // print_r($mfiles);
                $fileExists = false;
                $fileExistName = "";
                foreach ($mfiles as $mfile) {
                    if(str_contains($mfile, $migrationName)) {
                        $fileExists = true;
                        $fileExistName = $mfile;
                    }
                }
                if($fileExists) {
                    $this->info("Replacing old migration file: ".$fileExistName);
                    $migrationFileName = $fileExistName;
                }
            } else {
                $this->error("Module ".$moduleName." doesn't exists; Cannot generate !!!");
            }
        }
        
        $templateDirectory = __DIR__.'/../stubs';
        
        try {
            $this->line('Creating migration...');
            $migrationData = file_get_contents($templateDirectory."/migration.stub");
            
            $migrationData = str_replace("__migration_class_name__", $migrationClassName, $migrationData);
            $migrationData = str_replace("__db_table_name__", $dbTableName, $migrationData);
            $migrationData = str_replace("__module_name__", $moduleName, $migrationData);
            $migrationData = str_replace("__view_column__", $viewColumnName, $migrationData);
            $migrationData = str_replace("__generated__", $generateData, $migrationData);
            
            file_put_contents(base_path('database/migrations/'.$migrationFileName), $migrationData);
            
            // Artisan::call('make:migration', [
            //     'name' => $migrationName
            // ]);
            
        } catch (Exception $e) {
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        
        $this->info("Migration done: ".$migrationFileName."\n");
    }
}
