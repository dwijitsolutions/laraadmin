<?php

namespace Dwij\Laraadmin\Helpers;

use DB;
use Log;

use Dwij\Laraadmin\Models\Module;

class LAHelper
{
	// $names = LAHelper::generateModuleNames($module_name);
    public static function generateModuleNames($module_name) {
		$array = array();
		$module_name = trim($module_name);
		$module_name = str_replace(" ", "", $module_name);
		
		$array['module'] = ucfirst(str_plural($module_name));
		$array['label'] = ucfirst(str_plural($module_name));
		$array['table'] = strtolower(str_plural($module_name));
		$array['model'] = ucfirst(str_singular($module_name));
		$array['controller'] = $array['module']."Controller";
		$array['singular_l'] = strtolower(str_singular($module_name));
		$array['singular_c'] = ucfirst(str_singular($module_name));
		
		return (object) $array;
	}
	
	// $tables = LAHelper::getDBTables([]);
    public static function getDBTables($remove_tables = []) {
        $tables = DB::select('SHOW TABLES');
		
		$tables_out = array();
		foreach ($tables as $table) {
			$table = (Array)$table;
			$tables_out[] = array_values($table)[0];
		}
		$remove_tables2 = array(
			'migrations',
			'modules',
			'module_fields',
			'module_field_types',
			'password_resets'
		);
		$remove_tables = array_merge($remove_tables, $remove_tables2);
		$remove_tables = array_unique($remove_tables);
		$tables_out = array_diff($tables_out, $remove_tables);
		
		$tables_out2 = array();
		foreach ($tables_out as $table) {
			$tables_out2[$table] = $table;
		}
		
		return $tables_out2;
    }
	
	// $modules = LAHelper::getModuleNames([]);
    public static function getModuleNames($remove_modules = []) {
        $modules = Module::all();
		
		$modules_out = array();
		foreach ($modules as $module) {
			$modules_out[] = $module->name;
		}
		$modules_out = array_diff($modules_out, $remove_modules);
		
		$modules_out2 = array();
		foreach ($modules_out as $module) {
			$modules_out2[$module] = $module;
		}
		
		return $modules_out2;
    }
	
	// LAHelper::parseValues($field['popup_vals']);
    public static function parseValues($value) {
		// return $value;
		$valueOut = "";
		if (strpos($value, '[') !== false) {
			$arr = json_decode($value);
			foreach ($arr as $key) {
				$valueOut .= "<div class='label label-primary'>".$key."</div> ";
			}
		} else if (strpos($value, ',') !== false) {
			$arr = array_map('trim', explode(",", $value));
			foreach ($arr as $key) {
				$valueOut .= "<div class='label label-primary'>".$key."</div> ";
			}
		} else if (strpos($value, '@') !== false) {
			$valueOut .= "<b data-toggle='tooltip' data-placement='top' title='From ".str_replace("@", "", $value)." table' class='text-primary'>".$value."</b>";
		} else if ($value == "") {
			$valueOut .= "";
		} else {
			$valueOut = "<div class='label label-primary'>".$value."</div> ";
		}
		return $valueOut;
	}
	
	// LAHelper::log("info", "", $commandObject);
	public static function log($type, $text, $commandObject) {
		if($commandObject) {
			$commandObject->$type($text);
		} else {
			if($type == "line") {
				$type = "info";
			}
			Log::$type($text);
		}
	}
	
	// LAHelper::recurse_copy("", "");
	public static function recurse_copy($src,$dst) { 
		$dir = opendir($src); 
		@mkdir($dst, 0777, true);
		while(false !== ( $file = readdir($dir)) ) { 
			if (( $file != '.' ) && ( $file != '..' )) { 
				if ( is_dir($src . '/' . $file) ) { 
					LAHelper::recurse_copy($src . '/' . $file,$dst . '/' . $file); 
				} 
				else { 
					// ignore files
					if(!in_array($file, [".DS_Store"])) {
						copy($src . '/' . $file, $dst . '/' . $file);
					}
				}
			}
		}
		closedir($dir); 
	}
	
	// LAHelper::recurse_delete("");
	public static function recurse_delete($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object))
						LAHelper::recurse_delete($dir."/".$object);
					else
						unlink($dir."/".$object); 
				}
			}
			rmdir($dir); 
		}
	}
	
	// Generate Random Password
	// $password = LAHelper::gen_password();
	public static function gen_password($chars_min=6, $chars_max=8, $use_upper_case=false, $include_numbers=false, $include_special_chars=false) {
		$length = rand($chars_min, $chars_max);
		$selection = 'aeuoyibcdfghjklmnpqrstvwxz';
		if($include_numbers) {
			$selection .= "1234567890";
		}
		if($include_special_chars) {
			$selection .= "!@\"#$%&[]{}?|";
		}
		$password = "";
		for($i=0; $i<$length; $i++) {
			$current_letter = $use_upper_case ? (rand(0,1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];            
			$password .=  $current_letter;
		}
		return $password;
	}
}