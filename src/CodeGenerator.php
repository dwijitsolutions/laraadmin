<?php
namespace Dwij\Laraadmin;

use Exception;
use Illuminate\Filesystem\Filesystem;
use Dwij\Laraadmin\Models\Module;
use Dwij\Laraadmin\Models\ModuleFieldTypes;
use Dwij\Laraadmin\Helpers\LAHelper;
use Dwij\Laraadmin\Models\Menu;

class CodeGenerator
{
    /**
	* Generate Controller file
    * if $generate is true then create file from module info from DB
    * $comm is command Object from Migration command
	* CodeGenerator::generateMigration($table, $generateFromTable);
	**/
	public static function createController($config, $comm = null) {

        $templateDirectory = __DIR__.'/stubs';

        LAHelper::log("info", "Creating controller...", $comm);
        $md = file_get_contents($templateDirectory."/controller.stub");

        $md = str_replace("__controller_class_name__", $config->controllerName, $md);
        $md = str_replace("__model_name__", $config->modelName, $md);
        $md = str_replace("__module_name__", $config->moduleName, $md);
        $md = str_replace("__view_column__", $config->module->view_col, $md);

        // Listing columns
        $listing_cols = "";
        foreach ($config->module->fields as $field) {
            $listing_cols .= "'".$field['colname']."', ";
        }
        $listing_cols = trim($listing_cols, ", ");

        $md = str_replace("__listing_cols__", $listing_cols, $md);
        $md = str_replace("__view_folder__", $config->dbTableName, $md);
        $md = str_replace("__route_resource__", $config->dbTableName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);
        $md = str_replace("__singular_var__", $config->singularVar, $md);

        file_put_contents(base_path('app/Http/Controllers/LA/'.$config->controllerName.".php"), $md);
    }

    public static function createModel($config, $comm = null) {

        $templateDirectory = __DIR__.'/stubs';

        LAHelper::log("info", "Creating model...", $comm);
        $md = file_get_contents($templateDirectory."/model.stub");

        $md = str_replace("__model_class_name__", $config->modelName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);

        file_put_contents(base_path('app/'.$config->modelName.".php"), $md);
    }

    public static function createViews($config, $comm = null) {

        $templateDirectory = __DIR__.'/stubs';

        LAHelper::log("info", "Creating views...", $comm);
        // Create Folder
        @mkdir(base_path("resources/views/la/".$config->dbTableName), 0777, true);

        // ============================ Listing / Index ============================
        $md = file_get_contents($templateDirectory."/views/index.blade.stub");

        $md = str_replace("__module_name__", $config->moduleName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);
        $md = str_replace("__controller_class_name__", $config->controllerName, $md);
        $md = str_replace("__singular_var__", $config->singularVar, $md);
        $md = str_replace("__singular_cap_var__", str_replace('_', ' ', $config->singularCapitalVar), $md);

        // Listing columns
        $inputFields = "";
        foreach ($config->module->fields as $field) {
            $inputFields .= "\t\t\t\t\t@la_input($"."module, '".$field['colname']."')\n";
        }
        $inputFields = trim($inputFields);
        $md = str_replace("__input_fields__", $inputFields, $md);

        file_put_contents(base_path('resources/views/la/'.$config->dbTableName.'/index.blade.php'), $md);

        // ============================ Edit ============================
        $md = file_get_contents($templateDirectory."/views/edit.blade.stub");

        $md = str_replace("__module_name__", $config->moduleName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);
        $md = str_replace("__controller_class_name__", $config->controllerName, $md);
        $md = str_replace("__singular_var__", $config->singularVar, $md);
        $md = str_replace("__singular_cap_var__", str_replace('_', ' ', $config->singularCapitalVar), $md);

        // Listing columns
        $inputFields = "";
        foreach ($config->module->fields as $field) {
            $inputFields .= "\t\t\t\t\t@la_input($"."module, '".$field['colname']."')\n";
        }
        $inputFields = trim($inputFields);
        $md = str_replace("__input_fields__", $inputFields, $md);

        file_put_contents(base_path('resources/views/la/'.$config->dbTableName.'/edit.blade.php'), $md);

        // ============================ Show ============================
        $md = file_get_contents($templateDirectory."/views/show.blade.stub");

        $md = str_replace("__module_name__", $config->moduleName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);
        $md = str_replace("__singular_var__", $config->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $config->singularCapitalVar, $md);

        // Listing columns
        $displayFields = "";
        foreach ($config->module->fields as $field) {
            $displayFields .= "\t\t\t\t\t\t@la_display($"."module, '".$field['colname']."')\n";
        }
        $displayFields = trim($displayFields);
        $md = str_replace("__display_fields__", $displayFields, $md);

        file_put_contents(base_path('resources/views/la/'.$config->dbTableName.'/show.blade.php'), $md);
    }

    public static function appendRoutes($config, $comm = null) {

        $templateDirectory = __DIR__.'/stubs';

        LAHelper::log("info", "Appending routes...", $comm);
        if(\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
			$routesFile = base_path('routes/admin_routes.php');
		} else {
			$routesFile = app_path('Http/admin_routes.php');
		}

		$contents = file_get_contents($routesFile);
		$contents = str_replace('});', '', $contents);
		file_put_contents($routesFile, $contents);
		
        $md = file_get_contents($templateDirectory."/routes.stub");

        $md = str_replace("__module_name__", $config->moduleName, $md);
        $md = str_replace("__controller_class_name__", $config->controllerName, $md);
        $md = str_replace("__db_table_name__", $config->dbTableName, $md);
        $md = str_replace("__singular_var__", $config->singularVar, $md);
        $md = str_replace("__singular_cap_var__", $config->singularCapitalVar, $md);

        file_put_contents($routesFile, $md, FILE_APPEND);
    }

    public static function addMenu($config, $comm = null) {

        // $templateDirectory = __DIR__.'/stubs';

        LAHelper::log("info", "Appending Menu...", $comm);
        if(Menu::where("url", $config->dbTableName)->count() == 0) {
            Menu::create([
                "name" => $config->moduleName,
                "url" => $config->dbTableName,
                "icon" => "fa ".$config->fa_icon,
                "type" => 'module',
                "parent" => 0
            ]);
        }

        // Old Method to add Menu
        // $menu = '<li><a href="{{ url(config("laraadmin.adminRoute") . '."'".'/'.$config->dbTableName."'".') }}"><i class="fa fa-cube"></i> <span>'.$config->moduleName.'</span></a></li>'."\n".'            <!-- LAMenus -->';
        // $md = file_get_contents(base_path('resources/views/la/layouts/partials/sidebar.blade.php'));
        // $md = str_replace("<!-- LAMenus -->", $menu, $md);
        // file_put_contents(base_path('resources/views/la/layouts/partials/sidebar.blade.php'), $md);
    }

	/**
	* Generate migration file
    * if $generate is true then create file from module info from DB
    * $comm is command Object from Migration command
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

		// fa_icon
		$faIcon = "fa-cube";

        if($generate) {
            // check if table, module and module fields exists
            $module = Module::get($moduleName);
            if(isset($module)) {
				LAHelper::log("info", "Module exists :\t   ".$moduleName, $comm);

                $viewColumnName = $module->view_col;
				$faIcon = $module->fa_icon;

                $ftypes = ModuleFieldTypes::getFTypes2();
                foreach ($module->fields as $field) {
                    $ftype = $ftypes[$field['field_type']];
                    $unique = "false";
                    if($field['unique']) {
                        $unique = "true";
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
                    $generateData .= '["'.$field['colname'].'", "'.$field['label'].'", "'.$ftype.'", '.$unique.', '.$dvalue.', '.$minlength.', '.$maxlength.', '.$required.''.$values.'],'."\n            ";
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
            $migrationData = str_replace("__model_name__", $modelName, $migrationData);
            $migrationData = str_replace("__view_column__", $viewColumnName, $migrationData);
			$migrationData = str_replace("__fa_icon__", $faIcon, $migrationData);
            $migrationData = str_replace("__generated__", $generateData, $migrationData);

            file_put_contents(base_path('database/migrations/'.$migrationFileName), $migrationData);

        } catch (Exception $e) {
            throw new Exception("Unable to generate migration for ".$table." : ".$e->getMessage(), 1);
        }
        LAHelper::log("info", "Migration done: ".$migrationFileName."\n", $comm);
	}

    // $config = CodeGenerator::generateConfig($module_name);
    public static function generateConfig($module, $icon)
    {
        $config = array();
        $config = (object) $config;

        if(starts_with($module, "create_")) {
            $tname = str_replace("create_", "",$module);
            $module = str_replace("_table", "",$tname);
        }

        $config->modelName = ucfirst(str_singular($module));
        $tableP = str_plural(strtolower($module));
        $tableS = str_singular(strtolower($module));
        $config->dbTableName = $tableP;
        $config->fa_icon = $icon;
        $config->moduleName = ucfirst(str_plural($module));
        $config->controllerName = ucfirst(str_plural($module))."Controller";
        $config->singularVar = strtolower(str_singular($module));
        $config->singularCapitalVar = ucfirst(str_singular($module));

        $module = Module::get($config->moduleName);
        if(!isset($module->id)) {
            throw new Exception("Please run 'php artisan migrate' for 'create_".$config->dbTableName."_table' in order to create CRUD.\nOr check if any problem in Module Name '".$config->moduleName."'.", 1);
            return;
        }
        $config->module = $module;

        return $config;
    }
}
