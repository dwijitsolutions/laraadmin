<?php

namespace Dwij\Laraadmin\Commands;

use Config;
use Artisan;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class Migration extends Command
{
    /**
     * The command signature.
     *
     * @var string
     */
    protected $signature = 'la:migration {table}';

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
        
        $templateDirectory = __DIR__.'/../stubs';
        
        try {
            $this->line('Creating migration...');
            $migrationData = file_get_contents($templateDirectory."/migration.stub");
            
            $migrationData = str_replace("__migration_class_name__", $migrationClassName, $migrationData);
            $migrationData = str_replace("__db_table_name__", $dbTableName, $migrationData);
            $migrationData = str_replace("__module_name__", $moduleName, $migrationData);
            $migrationData = str_replace("__view_column__", "write_view_column_name_here e.g. name", $migrationData);
            
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
