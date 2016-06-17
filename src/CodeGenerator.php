<?php
namespace Dwij\Laraadmin;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\Helpers\LAHelper;

class CodeGenerator
{
	/**
	* Print input field enclosed within form-group
	* CodeGenerator::generateMigration($table, $generateFromTable);
	**/
	public static function generateMigration($table, $generate = false, $comm = null)
	{
		$filesystem = new Filesystem();
        
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
        
		LAHelper::log("info", "Model:\t   ".$modelName, $comm);
		LAHelper::log("info", "Module:\t   ".$moduleName, $comm);
		LAHelper::log("info", "Table:\t   ".$dbTableName, $comm);
		LAHelper::log("info", "Migration: ".$migrationName."\n", $comm);
		
        // Reverse migration generation from table
        $generateData = "";
        $viewColumnName = "view_column_name e.g. name";
        
        if($generate) {
            // check if table, module and module fields exists
            $module = Module::get($moduleName);
            if(isset($module)) {
				LAHelper::log("info", "Module exists :\t   ".$moduleName, $comm);
                
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
					LAHelper::log("info", "Replacing old migration file: ".$fileExistName, $comm);
                    $migrationFileName = $fileExistName;
                }
            } else {
				LAHelper::log("error", "Module ".$moduleName." doesn't exists; Cannot generate !!!", $comm);
            }
        }
        
        $templateDirectory = __DIR__.'/stubs';
        
        try {
            LAHelper::log("line", "Creating migration...", $comm);
            $migrationData = file_get_contents($templateDirectory."/migration.stub");
            
            $migrationData = str_replace("__migration_class_name__", $migrationClassName, $migrationData);
            $migrationData = str_replace("__db_table_name__", $dbTableName, $migrationData);
            $migrationData = str_replace("__module_name__", $moduleName, $migrationData);
            $migrationData = str_replace("__view_column__", $viewColumnName, $migrationData);
            $migrationData = str_replace("__generated__", $generateData, $migrationData);
            
            file_put_contents(base_path('database/migrations/'.$migrationFileName), $migrationData);
            
        } catch (Exception $e) {
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        LAHelper::log("info", "Migration done: ".$migrationFileName."\n", $comm);
	}
	
}